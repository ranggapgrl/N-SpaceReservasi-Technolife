<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\Payment\MidtransPaymentService;

class CheckoutController extends Controller
{
    /**
     * Show simulated payment gateway UI
     */
    public function simulatePayment($bookingCode)
    {
        $booking = Booking::where('booking_code', $bookingCode)->with('payment')->firstOrFail();

        abort_unless(app()->environment(['local', 'testing']), 404);

        // If already paid, redirect to success
        if ($booking->payment->status === 'paid' || $booking->status === 'confirmed') {
            return redirect()->route('booking.success', $booking->id);
        }

        return view('checkout.simulate', compact('booking'));
    }

    /**
     * Process the simulated payment
     */
    public function processPayment($bookingCode)
    {
        $booking = Booking::where('booking_code', $bookingCode)->with('payment')->firstOrFail();

        abort_unless(app()->environment(['local', 'testing']), 404);

        $paymentService = new MidtransPaymentService();
        $paymentService->processSimulatedPayment($booking);

        return redirect()->route('booking.success', $booking->id)->with('success', 'Pembayaran simulasi selesai.');
    }
}
