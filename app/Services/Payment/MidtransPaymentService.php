<?php

namespace App\Services\Payment;

use App\Models\Booking;
use Carbon\Carbon;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

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
        $serverKey = (string) config('services.midtrans.server_key');
        $signature = $payload['signature_key'] ?? null;

        if (
            $serverKey === ''
            || ! is_string($signature)
            || ! isset($payload['order_id'], $payload['status_code'], $payload['gross_amount'])
        ) {
            return false;
        }

        // Midtrans menandatangani identitas pesanan, kode status, dan nominal notifikasi.
        $expectedSignature = hash(
            'sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].$serverKey
        );

        return hash_equals($expectedSignature, $signature);
    }

    public function processNotification(array $payload): bool
    {
        if (! $this->verifyPayment($payload) || empty($payload['transaction_id'])) {
            return false;
        }

        // Gunakan status dari API Midtrans, bukan status transaksi dari browser.
        $transaction = $this->fetchTransactionStatus($payload['transaction_id']);

        if (
            ! isset(
                $transaction->order_id,
                $transaction->gross_amount,
                $transaction->transaction_status,
                $transaction->transaction_id
            )
            || (string) $transaction->order_id !== (string) $payload['order_id']
            || (string) $transaction->transaction_id !== (string) $payload['transaction_id']
            || ! $this->amountsMatch($transaction->gross_amount, $payload['gross_amount'])
        ) {
            return false;
        }

        $booking = Booking::with('payment')
            ->where('booking_code', $transaction->order_id)
            ->first();

        if (! $booking?->payment || ! $this->amountsMatch($booking->payment->amount, $transaction->gross_amount)) {
            return false;
        }

        $status = (string) $transaction->transaction_status;
        $isPaid = $status === 'settlement'
            || ($status === 'capture' && ($transaction->fraud_status ?? null) === 'accept');

        if ($isPaid) {
            DB::transaction(function () use ($booking, $transaction): void {
                $paidAt = $transaction->settlement_time
                    ?? $transaction->capture_time
                    ?? now()->toDateTimeString();

                $booking->payment->update([
                    'status' => 'paid',
                    'payment_method' => $transaction->payment_type ?? $booking->payment->payment_method,
                    'transaction_id' => $transaction->transaction_id ?? null,
                    'paid_at' => Carbon::parse($paidAt),
                    'verified_at' => now(),
                ]);
                $booking->update(['status' => 'confirmed']);
            });

            return true;
        }

        if (in_array($status, ['cancel', 'deny', 'expire'], true)) {
            if ($booking->payment->status !== 'paid') {
                $booking->payment->update(['status' => 'failed']);
            }

            return true;
        }

        if (in_array($status, ['refund', 'partial_refund'], true)) {
            $booking->payment->update(['status' => $status === 'refund' ? 'refunded' : 'partially_refunded']);

            return true;
        }

        // Status pending atau capture yang masih ditinjau tidak dihitung sebagai pendapatan.
        return $status === 'pending' || $status === 'capture';
    }

    public function processSimulatedPayment(Booking $booking): void
    {
        // Pembayaran simulasi boleh menguji alur booking, tetapi tidak boleh masuk laporan pendapatan.
        $booking->update(['status' => 'confirmed']);

        $booking->payment?->update([
            'status' => 'simulated',
            'payment_method' => 'simulation',
            'transaction_id' => null,
            'paid_at' => null,
        ]);
    }

    protected function fetchTransactionStatus(string $transactionId): object
    {
        return Transaction::status($transactionId);
    }

    private function amountsMatch(mixed $first, mixed $second): bool
    {
        $normalize = static function (mixed $amount): ?string {
            if (! is_string($amount) && ! is_int($amount) && ! is_float($amount)) {
                return null;
            }

            $amount = (string) $amount;

            if (! preg_match('/^\d+(?:\.\d{1,2})?$/', $amount)) {
                return null;
            }

            [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

            return (ltrim($whole, '0') ?: '0').'.'.str_pad($fraction, 2, '0');
        };

        $normalizedFirst = $normalize($first);
        $normalizedSecond = $normalize($second);

        return $normalizedFirst !== null
            && $normalizedSecond !== null
            && hash_equals($normalizedFirst, $normalizedSecond);
    }
}