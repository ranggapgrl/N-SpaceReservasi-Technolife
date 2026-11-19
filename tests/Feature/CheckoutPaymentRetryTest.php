<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Payment;
use App\Services\Payment\PaymentServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutPaymentRetryTest extends TestCase
{
    use RefreshDatabase;

    public function test_pending_payment_reopens_the_saved_midtrans_link(): void
    {
        $booking = $this->createBooking([
            'status' => 'pending',
            'payment_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/example-token',
            'midtrans_order_id' => 'NDY-20261119-ABCD-12345678',
            'expires_at' => now()->addHour(),
        ]);
        $this->mock(PaymentServiceInterface::class)->shouldNotReceive('createTransaction');

        $response = $this->post(route('checkout.pay', $booking->booking_code));

        $response->assertRedirect('https://app.sandbox.midtrans.com/snap/v2/vtweb/example-token');
    }

    public function test_failed_payment_creates_a_new_midtrans_transaction(): void
    {
        $booking = $this->createBooking(['status' => 'failed']);
        $this->mock(PaymentServiceInterface::class)
            ->shouldReceive('createTransaction')
            ->once()
            ->withArgs(fn (Booking $receivedBooking): bool => $receivedBooking->is($booking))
            ->andReturn('https://app.sandbox.midtrans.com/snap/v2/vtweb/new-token');

        $response = $this->post(route('checkout.pay', $booking->booking_code));

        $response->assertRedirect('https://app.sandbox.midtrans.com/snap/v2/vtweb/new-token');
    }

    public function test_paid_payment_cannot_start_a_new_attempt(): void
    {
        $booking = $this->createBooking([
            'status' => 'paid',
            'payment_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/old-token',
        ]);
        $this->mock(PaymentServiceInterface::class)->shouldNotReceive('createTransaction');

        $response = $this->post(route('checkout.pay', $booking->booking_code));

        $response->assertRedirect(route('booking.success', $booking->booking_code));
    }

    public function test_failed_payment_page_offers_a_midtrans_retry(): void
    {
        $booking = $this->createBooking(['status' => 'failed']);

        $response = $this->get(route('booking.success', $booking->booking_code));

        $response->assertOk()
            ->assertSee('Bayar Lagi lewat Midtrans')
            ->assertSee('24 jam');
    }

    public function test_pending_payment_invoice_shows_a_live_expiration_countdown(): void
    {
        $booking = $this->createBooking([
            'status' => 'pending',
            'payment_url' => 'https://app.sandbox.midtrans.com/snap/v2/vtweb/example-token',
            'expires_at' => now()->addHour(),
        ]);

        $html = view('booking.success', ['booking' => $booking])->render();

        $this->assertStringContainsString('data-payment-countdown', $html);
        $this->assertStringContainsString('Sisa waktu pembayaran', $html);
        $this->assertStringContainsString('setInterval', $html);
    }

    private function createBooking(array $paymentOverrides = []): Booking
    {
        $booking = Booking::create([
            'booking_code' => 'NDY-20261119-'.strtoupper(fake()->lexify('????')),
            'customer_name' => 'Test Customer',
            'customer_phone' => '081234567890',
            'customer_email' => 'customer@example.com',
            'booking_date' => '2026-12-01',
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'duration' => 1,
            'day_pass_price' => 35000,
            'product_total' => 0,
            'total_price' => 35000,
            'status' => 'pending',
            'visitor_category' => 'umum',
            'visitor_count' => 1,
        ]);

        Payment::create(array_merge([
            'booking_id' => $booking->id,
            'amount' => 35000,
            'status' => 'pending',
        ], $paymentOverrides));

        return $booking;
    }
}
