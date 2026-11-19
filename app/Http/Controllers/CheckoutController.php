<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\Payment\MidtransPaymentService;
use App\Services\Payment\PaymentServiceInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Midtrans\Transaction;

class CheckoutController extends Controller
{
    /**
     * Menampilkan halaman pembayaran palsu untuk pengujian tanpa transaksi Midtrans sungguhan.
     *
     * @param  string  $bookingCode  Kode booking yang akan dibayar.
     */
    public function simulatePayment($bookingCode)
    {
        // Ambil booking dan payment sekaligus agar status payment tersedia di halaman simulasi.
        $booking = Booking::where('booking_code', $bookingCode)->with('payment')->firstOrFail();

        // Halaman simulasi hanya boleh diakses pada komputer pengembangan atau saat tes.
        abort_unless(app()->environment(['local', 'testing']), 404);

        // Booking yang sudah lunas tidak boleh dibayar lagi.
        if ($booking->payment->status === 'paid' || $booking->status === 'confirmed') {
            return redirect()->route('booking.success', $booking->id);
        }

        // Kirim data booking ke halaman simulasi untuk ditampilkan.
        return view('checkout.simulate', compact('booking'));
    }

    /**
     * Menandai pembayaran sebagai simulasi untuk menguji alur booking.
     *
     * @param  string  $bookingCode  Kode booking yang pembayarannya akan disimulasikan.
     */
    public function processPayment($bookingCode)
    {
        // Pastikan booking tersedia beserta data payment-nya.
        $booking = Booking::where('booking_code', $bookingCode)->with('payment')->firstOrFail();

        // Jangan izinkan endpoint simulasi digunakan di production.
        abort_unless(app()->environment(['local', 'testing']), 404);

        // Layanan simulasi mengubah status booking tanpa menghubungi Midtrans.
        $paymentService = new MidtransPaymentService;
        $paymentService->processSimulatedPayment($booking);

        // Setelah simulasi, tampilkan invoice/status booking seperti biasa.
        return redirect()->route('booking.success', $booking->id)->with('success', 'Pembayaran simulasi selesai.');
    }

    /**
     * Membuka Snap yang masih aktif atau membuat transaksi baru setelah transaksi lama gagal/kedaluwarsa.
     *
     * Jika status Midtrans masih pending, link lama dipakai kembali atau proses retry ditunda
     * supaya pelanggan tidak mendapat dua transaksi aktif untuk booking yang sama.
     *
     * @param  string  $bookingCode  Kode booking yang akan dilanjutkan pembayarannya.
     * @param  PaymentServiceInterface  $paymentService  Layanan untuk membuat transaksi Midtrans baru.
     * @return RedirectResponse Redirect ke invoice, pesan error, atau halaman pembayaran Midtrans.
     */
    public function pay(string $bookingCode, PaymentServiceInterface $paymentService): RedirectResponse
    {
        try {
            // Bungkus pengecekan dan pembuatan transaksi dalam satu transaksi DB agar retry bersamaan tidak duplikat.
            $result = DB::transaction(function () use ($bookingCode, $paymentService): array {
                // Kunci booking dan pembayaran agar klik ulang bersamaan tidak membuat transaksi ganda.
                $booking = Booking::where('booking_code', $bookingCode)
                    ->lockForUpdate()
                    ->firstOrFail();
                $payment = $booking->payment()->lockForUpdate()->firstOrFail();

                // Pasang relasi yang sudah dikunci agar layanan pembayaran memakai record yang sama.
                $booking->setRelation('payment', $payment);

                // Pembayaran lunas/simulasi hanya boleh melihat invoice, bukan memulai pembayaran baru.
                if (in_array($payment->status, ['paid', 'simulated'], true) || $booking->status === 'confirmed') {
                    return ['type' => 'success'];
                }

                // Booking berstatus lain (misalnya dibatalkan) tidak lagi dapat dibayar.
                if ($booking->status !== 'pending') {
                    return ['type' => 'error', 'message' => 'Booking ini sudah tidak dapat dibayar.'];
                }

                // Kalau Snap masih aktif, arahkan pengguna ke link yang sama agar transaksi tidak terduplikasi.
                if (
                    $payment->status === 'pending'
                    && $payment->payment_url
                    && $payment->expires_at?->isFuture()
                ) {
                    return ['type' => 'url', 'url' => $payment->payment_url];
                }

                if ($payment->status === 'pending') {
                    // Pastikan transaksi lama benar-benar gagal/kedaluwarsa sebelum menerbitkan link baru.
                    $orderId = $payment->midtrans_order_id ?? $booking->booking_code;
                    $transaction = Transaction::status($orderId);

                    // Cocokkan identitas dan nominal dari Midtrans sebelum mempercayai status transaksinya.
                    if (
                        ! isset($transaction->order_id, $transaction->transaction_status, $transaction->gross_amount)
                        || (string) $transaction->order_id !== $orderId
                        || (int) round((float) $transaction->gross_amount * 100)
                            !== (int) round((float) $payment->amount * 100)
                    ) {
                        return [
                            'type' => 'error',
                            'message' => 'Status pembayaran belum dapat dipastikan. Silakan coba beberapa saat lagi.',
                        ];
                    }

                    $transactionStatus = (string) $transaction->transaction_status;
                    // Midtrans menganggap settlement atau capture yang diterima sebagai pembayaran lunas.
                    $isPaid = $transactionStatus === 'settlement'
                        || ($transactionStatus === 'capture' && ($transaction->fraud_status ?? null) === 'accept');

                    if ($isPaid) {
                        // Sinkronkan status lokal jika notifikasi Midtrans belum sempat masuk.
                        $paidAt = $transaction->settlement_time
                            ?? $transaction->capture_time
                            ?? now()->toDateTimeString();

                        $payment->update([
                            'status' => 'paid',
                            'payment_method' => $transaction->payment_type ?? $payment->payment_method,
                            'transaction_id' => $transaction->transaction_id ?? null,
                            'paid_at' => Carbon::parse($paidAt),
                            'verified_at' => now(),
                        ]);
                        $booking->update(['status' => 'confirmed']);

                        return ['type' => 'success'];
                    }

                    // Status selain gagal/expired masih mungkin dibayar; jangan buat transaksi kedua.
                    if (! in_array($transactionStatus, ['cancel', 'deny', 'expire'], true)) {
                        return [
                            'type' => 'error',
                            'message' => 'Pembayaran masih diproses Midtrans. Silakan tunggu konfirmasi sebelum mencoba lagi.',
                        ];
                    }

                    // Setelah Midtrans mengonfirmasi status terminal, payment boleh dicoba ulang.
                    $payment->update(['status' => 'failed']);
                }

                // Tolak status yang tidak dikenali; hanya payment gagal yang bisa membuat transaksi baru.
                if ($payment->status !== 'failed') {
                    return ['type' => 'error', 'message' => 'Pembayaran ini belum dapat dicoba kembali.'];
                }

                // Layanan Midtrans menyimpan ID, URL, dan batas waktu untuk percobaan baru.
                return ['type' => 'url', 'url' => $paymentService->createTransaction($booking)];
            });
        } catch (ModelNotFoundException $e) {
            // Pertahankan respons 404 Laravel jika kode booking/payment memang tidak ditemukan.
            throw $e;
        } catch (\Throwable $e) {
            // Simpan rincian teknis di log, tetapi tampilkan pesan yang aman dan mudah dimengerti pelanggan.
            Log::error('Gagal membuat ulang pembayaran Midtrans: '.$e->getMessage(), [
                'booking_code' => $bookingCode,
            ]);

            return redirect()
                ->route('booking.success', $bookingCode)
                ->with('error', 'Pembayaran belum dapat dibuka. Silakan coba lagi beberapa saat.');
        }

        // Jika transaksi sudah lunas saat dicek, kembali ke invoice.
        if ($result['type'] === 'success') {
            return redirect()->route('booking.success', $bookingCode);
        }

        // Tampilkan pesan yang sesuai jika status Midtrans belum aman untuk di-retry.
        if ($result['type'] === 'error') {
            return redirect()
                ->route('booking.success', $bookingCode)
                ->with('error', $result['message']);
        }

        // URL hanya berasal dari payment link yang tersimpan atau respons layanan Midtrans.
        return redirect()->away($result['url']);
    }
}
