<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
        
        // If already paid, redirect to success
        if ($booking->payment->status === 'paid' || $booking->status === 'confirmed') {
            return redirect()->route('booking.success', $booking->id);
        }

        return view('checkout.simulate', compact('booking'));
    }

    /**
     * Process the simulated payment
     */
    public function processPayment(Request $request, $bookingCode)
    {
        $booking = Booking::where('booking_code', $bookingCode)->with('payment')->firstOrFail();
        
        $paymentService = new MidtransPaymentService();
        $paymentService->processSuccessPayment($booking, $request->payment_method ?? 'qris');

        return redirect()->route('booking.success', $booking->id)->with('success', 'Payment successful!');
    }
}
