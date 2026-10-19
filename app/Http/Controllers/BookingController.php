<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Room;
use App\Models\Product;
use App\Models\Booking;
use App\Models\BookingItem;
use App\Models\Payment;
use App\Models\BlockedDate; // Untuk cek tanggal yang diblokir admin
use App\Services\BookingService;
use App\Services\Payment\PaymentServiceInterface;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
        $roomTypes = Room::where('status', 'active')
            ->get()
            ->groupBy('type')
            ->map(fn($rooms) => $rooms->first());

        $products = Product::where('is_active', true)->get();

        return view('booking.index', compact('roomTypes', 'products'));
    }

    // ═══════════════════════════════════════════════
    //  checkAvailability() — Cek Ketersediaan Ruangan (AJAX)
    // ═══════════════════════════════════════════════
    public function checkAvailability(Request $request)
    {
        $request->validate([
            'room_type' => 'required|string',
            'date'      => 'required|date|after_or_equal:today',
            'duration'  => 'required|integer|min:1|max:30',
        ]);

        // Cek apakah tanggal ini diblokir oleh admin
        $blocked = BlockedDate::where('date', $request->date)->first();
        if ($blocked) {
            $reason = $blocked->reason ? " ({$blocked->reason})" : '';
            return response()->json([
                'available' => false,
                'message'   => 'Tanggal ini ditutup oleh admin' . $reason . '. Silakan pilih tanggal lain.',
            ]);
        }

        $availableRoom = $this->bookingService->getAvailableRoom(
            $request->room_type,
            $request->date,
            '08:00:00',
            24 * $request->duration
        );

        if ($availableRoom) {
            return response()->json([
                'available' => true,
                'room_id'   => $availableRoom->id,
            ]);
        }

        return response()->json([
            'available' => false,
            'message'   => 'No rooms available for this date. Try another date.'
        ]);
    }

    // ═══════════════════════════════════════════════
    //  store() — Proses Booking Baru
    // ═══════════════════════════════════════════════
    /**
     * Alur:
     * 1. Validasi input (termasuk KTM jika mahasiswa)
     * 2. Cek tanggal diblokir & ketersediaan ruangan
     * 3. Dalam satu transaksi DB: hitung harga, upload KTM, simpan booking,
     *    items, payment, lalu buat transaksi Midtrans
     * 4. Commit hanya jika SEMUA langkah (termasuk Midtrans) berhasil
     * 5. Jika gagal: rollback, hapus file KTM, catat error asli ke log
     */
    public function store(Request $request, PaymentServiceInterface $paymentService)
    {
        // ── Validasi Input ────────────────────────────────────────────────────
        $request->validate([
            'room_type'        => 'required|string',
            'visitor_category' => 'required|in:mahasiswa,umum',
            'ktm_file'         => 'required_if:visitor_category,mahasiswa|nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
            'date'             => 'required|date',
            'duration'         => 'required|integer|min:1',
            'customer_name'    => 'required|string|max:255',
            'customer_phone'   => 'required|string|max:20',
            'products'         => 'nullable|array'
        ]);

        // ── Cek Tanggal Tidak Diblokir ────────────────────────────────────────
        if (BlockedDate::where('date', $request->date)->exists()) {
            return back()->with('error', 'Tanggal yang Anda pilih ditutup oleh admin. Silakan pilih tanggal lain.');
        }

        // ── Cek Ketersediaan Ruangan ─────────────────────────────────────────
        $availableRoom = $this->bookingService->getAvailableRoom(
            $request->room_type,
            $request->date,
            '08:00:00',
            24 * $request->duration
        );

        if (!$availableRoom) {
            return back()->with('error', 'Sorry, the room is no longer available. Please select another date.');
        }

        // Path KTM disimpan di luar try supaya bisa dihapus jika terjadi rollback
        $ktmPath = null;

        try {
            DB::beginTransaction();

            // ── Hitung Harga ──────────────────────────────────────────────────
            $pricePerDay    = $request->visitor_category === 'mahasiswa' ? 35000 : 55000;
            $roomPriceTotal = $pricePerDay * $request->duration;
            $productTotal   = 0;

            // ── Kalkulasi Item Produk Tambahan ────────────────────────────────
            $itemsToAttach = [];
            if ($request->has('products') && is_array($request->products)) {
                foreach ($request->products as $productId => $qty) {
                    if ($qty && is_numeric($qty) && $qty > 0) {
                        $product = Product::find($productId);
                        if ($product) {
                            $subtotal        = $product->price * $qty;
                            $productTotal   += $subtotal;
                            $itemsToAttach[] = [
                                'product_id' => $product->id,
                                'quantity'   => $qty,
                                'price'      => $product->price,
                                'subtotal'   => $subtotal
                            ];
                        }
                    }
                }
            }

            $totalPrice  = $roomPriceTotal + $productTotal;
            $bookingCode = 'NDY-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));

            // ── Upload KTM ke Storage Privat ──────────────────────────────────
            $ktmStatus = null;

            if ($request->visitor_category === 'mahasiswa' && $request->hasFile('ktm_file')) {
                $extension = $request->file('ktm_file')->getClientOriginalExtension();
                $fileName  = $bookingCode . '.' . $extension;

                // storage/app/private/ktm/ (tidak publik)
                $ktmPath   = $request->file('ktm_file')->storeAs('ktm', $fileName, 'local');
                $ktmStatus = 'pending';
            }

            // ── Simpan Data Booking ───────────────────────────────────────────
            $booking = Booking::create([
                'booking_code'     => $bookingCode,
                'room_id'          => $availableRoom->id,
                'customer_name'    => $request->customer_name,
                'customer_phone'   => $request->customer_phone,
                'booking_date'     => $request->date,
                'start_time'       => '08:00:00',
                'end_time'         => '22:00:00',
                'duration'         => $request->duration,
                'room_price'       => $roomPriceTotal,
                'product_total'    => $productTotal,
                'total_price'      => $totalPrice,
                'status'           => 'pending',
                'visitor_category' => $request->visitor_category,
                'ktm_path'         => $ktmPath,
                'ktm_status'       => $ktmStatus,
            ]);

            // ── Simpan Item Produk ─────────────────────────────────────────────
            foreach ($itemsToAttach as $item) {
                $booking->items()->create($item);
            }

            // ── Buat Record Payment (status awal: pending) ─────────────────────
            Payment::create([
                'booking_id' => $booking->id,
                'amount'     => $totalPrice,
                'status'     => 'pending'
            ]);

            // ── Buat Transaksi Pembayaran (Midtrans / fallback) ───────────────
            // Dipanggil SEBELUM commit: jika Midtrans gagal, seluruh booking
            // di-rollback sehingga tidak ada booking "pending" yang menggantung
            // dan menahan slot ruangan.
            $paymentUrl = $paymentService->createTransaction($booking);

            // ── Simpan Kode Booking ke Cookie Browser (30 hari) ───────────────
            $history = json_decode($request->cookie('nss_booking_history', '[]'), true);
            if (!is_array($history)) {
                $history = [];
            }
            if (!in_array($booking->booking_code, $history)) {
                $history[] = $booking->booking_code;
            }
            Cookie::queue('nss_booking_history', json_encode($history), 43200);

            // Semua langkah berhasil
            DB::commit();

            return redirect()->to($paymentUrl);

        } catch (\Throwable $e) {
            // TODO: HAPUS baris dd() ini setelah penyebab error ketemu
            dd($e->getMessage(), $e->getFile(), $e->getLine());

            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }

            // Hapus file KTM yatim jika booking dibatalkan
            if ($ktmPath && Storage::disk('local')->exists($ktmPath)) {
                Storage::disk('local')->delete($ktmPath);
            }

            // Catat error ASLI supaya bisa dilacak di storage/logs/laravel.log
            Log::error('Booking gagal: ' . $e->getMessage(), [
                'file'  => $e->getFile(),
                'line'  => $e->getLine(),
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
     * CATATAN PENTING:
     * Status diubah menjadi confirmed hanya berdasarkan redirect dari Midtrans.
     * TODO sebelum produksi: pindahkan verifikasi ke webhook Midtrans.
     */
    public function success($bookingCode)
    {
        $booking = Booking::with(['room', 'items.product', 'payment'])
                    ->where('booking_code', $bookingCode)
                    ->orWhere('id', $bookingCode)
                    ->firstOrFail();

        if ($booking->status === 'pending') {
            $booking->update(['status' => 'confirmed']);
            if ($booking->payment) {
                $booking->payment->update([
                    'status'  => 'paid',
                    'paid_at' => now(),
                ]);
            }
        }

        return view('booking.success', compact('booking'));
    }

    // ═══════════════════════════════════════════════
    //  checkStatus() — Cek Status Booking (Tanpa Login)
    // ═══════════════════════════════════════════════
    public function checkStatus(Request $request)
    {
        $bookings = collect();

        $cookieHistory = json_decode($request->cookie('nss_booking_history', '[]'), true);
        if (!is_array($cookieHistory)) {
            $cookieHistory = [];
        }

        $keyword = $request->input('keyword');

        if ($keyword) {
            $bookings = Booking::with('room')
                        ->where('booking_code', 'LIKE', "%{$keyword}%")
                        ->orWhere('customer_phone', 'LIKE', "%{$keyword}%")
                        ->orderBy('created_at', 'desc')
                        ->get();

            foreach ($bookings as $b) {
                if (!in_array($b->booking_code, $cookieHistory)) {
                    $cookieHistory[] = $b->booking_code;
                }
            }
            Cookie::queue('nss_booking_history', json_encode($cookieHistory), 43200);

        } elseif (!empty($cookieHistory)) {
            $bookings = Booking::with('room')
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
            'booking_code'   => 'required|string',
            'customer_phone' => 'required|string',
            'ktm_file'       => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
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
        $fileName  = $booking->booking_code . '.' . $extension;
        $ktmPath   = $request->file('ktm_file')->storeAs('ktm', $fileName, 'local');

        $booking->update([
            'ktm_path'          => $ktmPath,
            'ktm_status'        => 'pending',
            'ktm_reject_reason' => null,
        ]);

        return back()->with('success', 'KTM berhasil diunggah ulang. Menunggu verifikasi admin.');
    }
}