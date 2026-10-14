<?php

namespace App\Services\Payment;

use App\Models\Booking;

interface PaymentServiceInterface
{
    /**
     * Create a payment transaction and return a payment URL or Token.
     */
    public function createTransaction(Booking $booking): string;

    /**
     * Verify a payment status from gateway callback.
     */
    public function verifyPayment(array $payload): bool;
}
