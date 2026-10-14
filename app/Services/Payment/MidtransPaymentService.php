<?php

namespace App\Services\Payment;

use App\Models\Booking;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\Log;

class MidtransPaymentService implements PaymentServiceInterface
{
    public function __construct()
    {
        // Konfigurasi kredensial Midtrans dari .env via config/services.php
        Config::$serverKey    = config('services.midtrans.server_key');
        Config::$isProduction = (bool) config('services.midtrans.is_production', false);
        Config::$isSanitized  = true;
        Config::$is3ds        = true;

        // Di Windows/Laragon lokal, DNS sering tidak bisa resolve domain Midtrans.
        // CURLOPT_RESOLVE memaksa curl memakai IP langsung tanpa lewat DNS.
        // Aman untuk sandbox — HAPUS bagian ini saat deploy ke production.
        if (! config('services.midtrans.is_production')) {
            Config::$curlOptions = [
                // WAJIB ada: library midtrans-php membaca key ini saat curlOptions
                // diisi. Tanpa ini PHP 8 melempar "Undefined array key 10023".
                CURLOPT_HTTPHEADER     => [],

                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => 0,

                // IP Midtrans Sandbox (via Alibaba Cloud WAF).
                // Kalau koneksi gagal, coba hapus blok CURLOPT_RESOLVE ini:
                // IP bisa saja sudah berubah.
                CURLOPT_RESOLVE => [
                    'app.sandbox.midtrans.com:443:8.215.152.185',
                    'api.sandbox.midtrans.com:443:8.215.152.185',
                ],
            ];
        }
    }

    public function createTransaction(Booking $booking): string
    {
        // Parameter yang dikirim ke Midtrans Snap API
        $params = [
            'transaction_details' => [
                'order_id'     => $booking->booking_code,
                'gross_amount' => (int) $booking->total_price,
            ],
            'customer_details' => [
                'first_name' => $booking->customer_name,
                'phone'      => $booking->customer_phone,
            ],
            // URL kembali setelah pelanggan selesai bayar di halaman Midtrans
            'callbacks' => [
                'finish' => route('booking.success', $booking->booking_code),
            ],
        ];

        try {
            // Kirim request ke Midtrans, dapatkan URL halaman pembayaran Snap
            $snapResponse = Snap::createTransaction($params);
            return $snapResponse->redirect_url;
        } catch (\Throwable $e) {
            Log::error('Midtrans createTransaction gagal: ' . $e->getMessage(), [
                'order_id'   => $booking->booking_code,
                'server_key' => Config::$serverKey ? 'terisi' : 'KOSONG',
            ]);
            throw $e;
        }
    }

    public function verifyPayment(array $payload): bool
    {
        // TODO sebelum produksi: verifikasi signature_key dari Midtrans
        // (sha512 dari order_id + status_code + gross_amount + server_key).
        return true;
    }

    public function processSuccessPayment(Booking $booking, string $paymentMethod = 'qris')
    {
        // Update status booking dan record pembayaran setelah konfirmasi dari Midtrans
        $booking->update(['status' => 'confirmed']);

        $payment = $booking->payment;
        if ($payment) {
            $payment->update([
                'status'         => 'paid',
                'payment_method' => $paymentMethod,
                'transaction_id' => 'TRX-' . time() . '-' . rand(100, 999),
                'paid_at'        => now(),
            ]);
        }
    }
}