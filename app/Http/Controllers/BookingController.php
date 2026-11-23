<?php

namespace App\Http\Controllers;

use App\Models\BlockedDate;
use App\Models\Booking;
use App\Models\Product;
use App\Models\Room;
use App\Services\BookingService;
use App\Services\Payment\PaymentServiceInterface;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Midtrans\Transaction;

class BookingController extends Controller
{
    // ─── Dependency Injection ─────────────────────────────────────────────────
    protected $bookingService;

    public function __construct(BookingService $bookingService)
    {
        $this->bookingService = $bookingService;
    }

    // ═══════════════════════════════════════════════
    //  index() — Halaman Form Booking
    // ═══════════════════════════════════════════════
    public function index()
    {
        // Ambil data produk/snack tambahan yang aktif
        $products = Product::where('is_active', true)->get();
        $rooms = Room::where('status', 'active')->orderBy('name')->get();
        $venuePackages = config('booking.venue_packages');
        $venueServiceChargePercent = config('booking.venue_service_charge_percent');

        // Ambil daftar tanggal yang diblokir oleh admin
        $blockedDates = BlockedDate::pluck('date')->map(fn ($d) => $d->format('Y-m-d'))->toArray();

        // Tampilkan halaman booking tanpa mengirimkan data $roomTypes
        return view('booking.index', compact('products', 'rooms', 'blockedDates', 'venuePackages', 'venueServiceChargePercent'));
    }

    //  checkAvailability() —
    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'venue_package' => ['required_with:room_id', 'nullable', 'in:per_jam,halfday,fullday'],
            'dates' => ['required_without:date', 'array', 'min:1', 'max:30'],
            'dates.*' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'distinct'],
            'date' => ['required_without:dates', 'nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'arrival_times' => ['required_without:arrival_time', 'array'],
            'arrival_times.*' => ['required', 'date_format:H:i'],
            'departure_times' => ['required_without_all:departure_time,room_id', 'array'],
            'departure_times.*' => ['required_without:room_id', 'date_format:H:i'],
            'arrival_time' => ['required_without:arrival_times', 'date_format:H:i'],
            'departure_time' => ['required_without_all:departure_times,room_id', 'date_format:H:i'],
            'duration' => ['required_without:dates', 'integer', 'min:1', 'max:30'],
            'visitor_count' => ['required', 'integer', 'min:1'],
        ]);

        $dates = $this->resolveBookingDates($validated);
        $blocked = $this->findBlockedDate($dates);

        if ($blocked) {
            $reason = $blocked->reason ? " ({$blocked->reason})" : '';

            return response()->json([
                'available' => false,
                'message' => "Tanggal {$blocked->date->format('Y-m-d')} ditutup oleh admin{$reason}. Silakan pilih tanggal lain.",
            ]);
        }

        $room = ! empty($validated['room_id'])
            ? Room::where('status', 'active')->find($validated['room_id'])
            : null;

        if (! empty($validated['room_id']) && ! $room) {
            return response()->json([
                'available' => false,
                'message' => 'Venue ini sedang tidak tersedia untuk pemesanan.',
            ]);
        }

        $venuePackage = $room ? config('booking.venue_packages.'.$validated['venue_package']) : null;
        $schedules = $this->resolveBookingSchedules(
            $validated,
            $venuePackage ? (int) $venuePackage['duration_hours'] : null
        );

        if ($room && (int) $request->visitor_count < $room->minimumAttendees()) {
            return response()->json([
                'available' => false,
                'message' => "Minimum pemesanan untuk {$room->type} adalah {$room->minimumAttendees()} orang.",
            ]);
        }

        $capacity = $room?->capacity ?? (int) config('booking.open_space_capacity');
        if ((int) $request->visitor_count > $capacity) {
            return response()->json([
                'available' => false,
                'message' => "Kapasitas tempat yang dipilih maksimal {$capacity} orang.",
            ]);
        }

        $hasCapacity = $room
            ? $this->bookingService->roomIsAvailable($room->id, $schedules)
            : $this->bookingService->hasCapacity($schedules, (int) $request->visitor_count);

        if ($hasCapacity) {
            return response()->json([
                'available' => true,
            ]);
        }

        return response()->json([
            'available' => false,
            'message' => $room
                ? 'Venue sudah dipesan pada salah satu jadwal pilihan.'
                : 'Kapasitas open space tidak mencukupi pada jadwal yang dipilih.',
        ]);
    }

    //  store() — Proses Booking Baru
    public function store(Request $request, PaymentServiceInterface $paymentService)
    {
        // ── Validasi Input ────────────────────────────────────────────────────
        $validated = $request->validate([
            'room_id' => ['nullable', 'integer', 'exists:rooms,id'],
            'venue_package' => ['required_with:room_id', 'nullable', 'in:per_jam,halfday,fullday'],
            'visitor_category' => ['required_without:room_id', 'nullable', 'in:mahasiswa,umum'],
            'ktm_file' => 'required_if:visitor_category,mahasiswa|nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'dates' => ['required_without:date', 'array', 'min:1', 'max:30'],
            'dates.*' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'distinct'],
            'date' => ['required_without:dates', 'nullable', 'date_format:Y-m-d', 'after_or_equal:today'],
            'arrival_times' => ['required_without:arrival_time', 'array'],
            'arrival_times.*' => ['required', 'date_format:H:i'],
            'departure_times' => ['required_without_all:departure_time,room_id', 'array'],
            'departure_times.*' => ['required_without:room_id', 'date_format:H:i'],
            'arrival_time' => ['required_without:arrival_times', 'date_format:H:i'],
            'departure_time' => ['required_without_all:departure_times,room_id', 'date_format:H:i'],
            'duration' => ['required_without:dates', 'integer', 'min:1', 'max:30'],
            'visitor_count' => ['required', 'integer', 'min:1'],
            'customer_name' => 'required|string|max:255',
            'institution' => ['required_with:room_id', 'nullable', 'string', 'max:255'],
            'customer_phone' => 'required|string|max:20',
            'products' => 'nullable|array',
            'customer_email' => 'required|email:rfc|max:255',
            'facility_notes' => 'nullable|string|max:1000',
        ]);

        $dates = $this->resolveBookingDates($validated);
        $firstDate = $dates[0];
        $duration = count($dates);
        $room = ! empty($validated['room_id'])
            ? Room::where('status', 'active')->find($validated['room_id'])
            : null;

        if (! empty($validated['room_id']) && ! $room) {
            return back()->withInput()->with('error', 'Venue ini sedang tidak tersedia untuk pemesanan.');
        }

        $venuePackage = $room ? config('booking.venue_packages.'.$validated['venue_package']) : null;
        $schedules = $this->resolveBookingSchedules(
            $validated,
            $venuePackage ? (int) $venuePackage['duration_hours'] : null
        );

        $capacity = $room?->capacity ?? (int) config('booking.open_space_capacity');
        if ((int) $validated['visitor_count'] > $capacity) {
            throw ValidationException::withMessages([
                'visitor_count' => "Kapasitas tempat yang dipilih maksimal {$capacity} orang.",
            ]);
        }

        if ($room && (int) $validated['visitor_count'] < $room->minimumAttendees()) {
            throw ValidationException::withMessages([
                'visitor_count' => "Minimum pemesanan untuk {$room->type} adalah {$room->minimumAttendees()} orang.",
            ]);
        }

        $blocked = $this->findBlockedDate($dates);

        if ($blocked) {
            return back()->withInput()->with('error', "Tanggal {$blocked->date->format('Y-m-d')} ditutup oleh admin. Silakan pilih tanggal lain.");
        }

        // Path KTM disimpan di luar try supaya bisa dihapus jika terjadi rollback
        $ktmPath = null;

        try {
            DB::beginTransaction();

            $this->bookingService->lockCapacityDates($dates);

            $blocked = $this->findBlockedDate($dates);

            if ($blocked) {
                DB::rollBack();

                return back()->withInput()->with('error', "Tanggal {$blocked->date->format('Y-m-d')} ditutup oleh admin. Silakan pilih tanggal lain.");
            }

            $hasCapacity = $room
                ? $this->bookingService->roomIsAvailable($room->id, $schedules)
                : $this->bookingService->hasCapacity($schedules, (int) $request->visitor_count);

            if (! $hasCapacity) {
                DB::rollBack();

                $message = $room
                    ? 'Venue sudah dipesan pada salah satu jadwal pilihan. Silakan pilih jam atau tanggal lain.'
                    : 'Kapasitas open space tidak mencukupi pada jadwal yang dipilih. Kurangi jumlah pengunjung atau pilih jadwal lain.';

                return back()->withInput()->with('error', $message);
            }

            // ── Hitung Harga ──────────────────────────────────────────────────
            $visitorCategory = $room ? 'venue' : $validated['visitor_category'];
            $venuePricePerPerson = $room ? (int) $venuePackage['price_per_person'] : null;
            $dayPassPrice = $room
                ? $venuePricePerPerson * (int) $validated['visitor_count'] * $duration
                : ($visitorCategory === 'mahasiswa' ? 35000 : 55000) * (int) $validated['visitor_count'] * $duration;
            $serviceChargeRate = $room ? (float) config('booking.venue_service_charge_percent') : 0;
            $serviceCharge = $room
                ? (int) round($dayPassPrice * $serviceChargeRate / 100)
                : 0;
            $productTotal = 0;

            // ── Kalkulasi Item Produk Tambahan ────────────────────────────────
            $itemsToAttach = [];
            if ($request->has('products') && is_array($request->products)) {
                foreach ($request->products as $productId => $qty) {
                    if ($qty && is_numeric($qty) && $qty > 0) {
                        $product = Product::find($productId);
                        if ($product) {
                            $subtotal = $product->price * $qty;
                            $productTotal += $subtotal;
                            $itemsToAttach[] = [
                                'product_id' => $product->id,
                                'quantity' => $qty,
                                'price' => $product->price,
                                'subtotal' => $subtotal,
                            ];
                        }
                    }
                }
            }

            $totalPrice = $dayPassPrice + $serviceCharge + $productTotal;
            $bookingCode = 'NDY-'.now()->format('Ymd').'-'.strtoupper(Str::random(4));

            // ── Upload KTM ke Storage Privat ──────────────────────────────────
            $ktmStatus = null;

            if ($request->visitor_category === 'mahasiswa' && $request->hasFile('ktm_file')) {
                $extension = $request->file('ktm_file')->getClientOriginalExtension();
                $fileName = $bookingCode.'.'.$extension;

                // storage/app/private/ktm/ (tidak publik)
                $ktmPath = $request->file('ktm_file')->storeAs('ktm', $fileName, 'local');
                $ktmStatus = 'pending';
            }

            // ── Simpan Data Booking ───────────────────────────────────────────
            $booking = Booking::create([
                'booking_code' => $bookingCode,
                'room_id' => $room?->id,
                'room_name' => $room?->name,
                'venue_package' => $room ? $validated['venue_package'] : null,
                'venue_duration_hours' => $venuePackage['duration_hours'] ?? null,
                'venue_price_per_person' => $venuePricePerPerson,
                'service_charge_rate' => $serviceChargeRate,
                'service_charge' => $serviceCharge,
                'customer_name' => $request->customer_name,
                'institution' => $room ? $validated['institution'] : null,
                'customer_phone' => $request->customer_phone,
                'booking_date' => $firstDate,
                'start_time' => $schedules[$firstDate]['start_time'],
                'end_time' => $schedules[$firstDate]['end_time'],
                'duration' => $duration,
                'visitor_count' => $request->visitor_count,
                'facility_notes' => $request->input('facility_notes'),
                'day_pass_price' => $dayPassPrice,
                'product_total' => $productTotal,
                'total_price' => $totalPrice,
                'status' => 'pending',
                'visitor_category' => $visitorCategory,
                'ktm_path' => $ktmPath,
                'ktm_status' => $ktmStatus,
                'customer_email' => $request->customer_email,
            ]);

            // Simpan tanggal pilihan satu per satu, bukan sebagai rentang berurutan.
            $booking->bookingDates()->createMany(
                array_map(fn (array $schedule): array => $schedule, array_values($schedules))
            );

            // ── Simpan Item Produk ─────────────────────────────────────────────
            foreach ($itemsToAttach as $item) {
                $booking->items()->create($item);
            }

            // ── Buat Record Payment (status awal: pending) ─────────────────────
            $payment = $booking->payment()->create([
                'booking_id' => $booking->id,
                'amount' => $totalPrice,
                'status' => 'pending',
            ]);
            $booking->setRelation('payment', $payment);

            // ── Buat Transaksi Pembayaran (Midtrans / fallback) ───────────────
            // Dipanggil SEBELUM commit: jika Midtrans gagal, seluruh booking
            // di-rollback sehingga tidak ada booking "pending" yang menggantung
            // dan menahan slot ruangan.
            $paymentUrl = $paymentService->createTransaction($booking);

            // ── Simpan Kode Booking ke Cookie Browser (sesuai dengan settingan) ───────────────
            $history = json_decode($request->cookie('nss_booking_history', '[]'), true);
            if (! is_array($history)) {
                $history = [];
            }
            if (! in_array($booking->booking_code, $history)) {
                $history[] = $booking->booking_code;
            }
            Cookie::queue('nss_booking_history', json_encode($history), 30);

            // Semua langkah berhasil
            DB::commit();

            return redirect()->to($paymentUrl);

        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            // Hapus file KTM yatim jika booking dibatalkan
            if ($ktmPath && Storage::disk('local')->exists($ktmPath)) {
                Storage::disk('local')->delete($ktmPath);
            }

            // Catat error ASLI supaya bisa dilacak di storage/logs/laravel.log
            Log::error('Booking gagal: '.$e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()
                ->withInput()
                ->with('error', 'An error occurred while processing your booking. Please try again.');
        }
    }

    // ═══════════════════════════════════════════════
    //  success() — Halaman Sukses Booking
    // ═══════════════════════════════════════════════
    /**
     * Halaman ini hanya menampilkan status booking; konfirmasi lunas dilakukan lewat notifikasi Midtrans.
     */
    public function success($bookingCode, PaymentServiceInterface $paymentService)
    {
        $booking = Booking::with(['items.product', 'payment', 'bookingDates', 'room'])
            ->where('booking_code', $bookingCode)
            ->orWhere('id', $bookingCode)
            ->firstOrFail();

        // Jika pembayaran masih pending, coba sinkronkan statusnya langsung dari Midtrans
        if ($booking->payment && $booking->payment->status === 'pending') {
            try {
                // Memanggil status transaksi dari API Midtrans berdasarkan booking_code (order_id)
                $statusResponse = Transaction::status(
                    $booking->payment->midtrans_order_id ?? $booking->booking_code
                );

                if (isset($statusResponse->transaction_status)) {
                    $status = $statusResponse->transaction_status;
                    $isPaid = $status === 'settlement' || ($status === 'capture' && ($statusResponse->fraud_status ?? null) === 'accept');

                    if ($isPaid) {
                        DB::transaction(function () use ($booking, $statusResponse) {
                            $paidAt = $statusResponse->settlement_time ?? $statusResponse->capture_time ?? now();
                            $booking->payment->update([
                                'status' => 'paid',
                                'payment_method' => $statusResponse->payment_type ?? 'midtrans',
                                'transaction_id' => $statusResponse->transaction_id ?? null,
                                'paid_at' => Carbon::parse($paidAt),
                                'verified_at' => now(),
                            ]);
                            $booking->update(['status' => 'confirmed']);
                        });

                        // Refresh relasi payment setelah di-update
                        $booking->load('payment');
                    }
                }
            } catch (\Throwable $e) {
                // Abaikan error jika transaksi belum terdaftar di API Midtrans / simulasi lokal
                Log::info('Gagal sinkronisasi status otomatis di halaman success: '.$e->getMessage());
            }
        }

        return view('booking.success', compact('booking'));
    }

    /**
     * Mendukung daftar tanggal baru sekaligus format date/duration booking lama.
     */
    private function resolveBookingDates(array $validated): array
    {
        if (! empty($validated['dates'])) {
            return collect($validated['dates'])
                ->map(fn (string $date): string => Carbon::parse($date)->toDateString())
                ->unique()
                ->sort()
                ->values()
                ->all();
        }

        $startDate = Carbon::parse($validated['date']);

        return collect(range(0, (int) $validated['duration'] - 1))
            ->map(fn (int $offset): string => $startDate->copy()->addDays($offset)->toDateString())
            ->all();
    }

    /**
     * Resolve a time range for each date, while accepting the previous shared-time form payload.
     */
    private function resolveBookingSchedules(array $validated, ?int $venueDurationHours = null): array
    {
        $schedules = [];

        foreach ($this->resolveBookingDates($validated) as $date) {
            $startTime = $validated['arrival_times'][$date] ?? $validated['arrival_time'] ?? null;
            $endTime = $venueDurationHours !== null && $startTime
                ? Carbon::createFromFormat('!H:i', $startTime)->addHours($venueDurationHours)->format('H:i')
                : ($validated['departure_times'][$date] ?? $validated['departure_time'] ?? null);

            if (! $startTime || ! $endTime || $endTime <= $startTime) {
                $errorKey = $venueDurationHours !== null
                    ? (isset($validated['arrival_times']) ? 'arrival_times.'.$date : 'arrival_time')
                    : (isset($validated['arrival_times']) ? 'departure_times.'.$date : 'departure_time');

                throw ValidationException::withMessages([
                    $errorKey => $venueDurationHours !== null
                        ? 'Jam mulai tidak sesuai dengan durasi paket venue.'
                        : 'Pilih jam mulai dan jam selesai yang valid untuk setiap tanggal.',
                ]);
            }

            if ($venueDurationHours !== null && $endTime > '22:00') {
                throw ValidationException::withMessages([
                    (isset($validated['arrival_times']) ? 'arrival_times.'.$date : 'arrival_time') => 'Jadwal paket harus selesai paling lambat pukul 22.00.',
                ]);
            }

            $schedules[$date] = [
                'date' => $date,
                'start_time' => $startTime.':00',
                'end_time' => $endTime.':00',
            ];
        }

        return $schedules;
    }

    private function findBlockedDate(array $dates): ?BlockedDate
    {
        return BlockedDate::where(function ($query) use ($dates): void {
            foreach ($dates as $date) {
                $query->orWhereDate('date', $date);
            }
        })->first();
    }

    // ═══════════════════════════════════════════════
    //  checkStatus() — Cek Status Booking (Tanpa Login)
    // ═══════════════════════════════════════════════
    public function checkStatus(Request $request)
    {
        $bookings = collect();

        $cookieHistory = json_decode($request->cookie('nss_booking_history', '[]'), true);
        if (! is_array($cookieHistory)) {
            $cookieHistory = [];
        }

        $keyword = $request->input('keyword');

        if ($keyword) {
            $bookings = Booking::with(['payment', 'bookingDates', 'room'])
                ->where('booking_code', 'LIKE', "%{$keyword}%")
                ->orWhere('customer_phone', 'LIKE', "%{$keyword}%")
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($bookings as $b) {
                if (! in_array($b->booking_code, $cookieHistory)) {
                    $cookieHistory[] = $b->booking_code;
                }
            }
            Cookie::queue('nss_booking_history', json_encode($cookieHistory), 3);

        } elseif (! empty($cookieHistory)) {
            $bookings = Booking::with(['payment', 'bookingDates', 'room'])
                ->whereIn('booking_code', $cookieHistory)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('booking.check', compact('bookings'));
    }

    // ═══════════════════════════════════════════════
    //  reuploadKtm() — Upload Ulang KTM yang Ditolak
    // ═══════════════════════════════════════════════
    public function reuploadKtm(Request $request)
    {
        $request->validate([
            'booking_code' => 'required|string',
            'customer_phone' => 'required|string',
            'ktm_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Harus cocok booking_code DAN customer_phone
        $booking = Booking::where('booking_code', $request->booking_code)
            ->where('customer_phone', $request->customer_phone)
            ->firstOrFail();

        if ($booking->ktm_status !== 'rejected') {
            return back()->with('error', 'Upload ulang hanya diizinkan untuk KTM yang ditolak.');
        }

        if ($booking->ktm_path && Storage::disk('local')->exists($booking->ktm_path)) {
            Storage::disk('local')->delete($booking->ktm_path);
        }

        $extension = $request->file('ktm_file')->getClientOriginalExtension();
        $fileName = $booking->booking_code.'.'.$extension;
        $ktmPath = $request->file('ktm_file')->storeAs('ktm', $fileName, 'local');

        $booking->update([
            'ktm_path' => $ktmPath,
            'ktm_status' => 'pending',
            'ktm_reject_reason' => null,
        ]);

        return back()->with('success', 'KTM berhasil diunggah ulang. Menunggu verifikasi admin.');
    }
}
