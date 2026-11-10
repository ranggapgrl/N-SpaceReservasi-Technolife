<?php

namespace Tests\Feature\Admin;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Services\Payment\MidtransPaymentService;
use App\Services\Payment\PaymentServiceInterface;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevenueReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_revenue_report_counts_paid_payment_amounts_only(): void
    {
        $this->actingAs(User::factory()->create());

        $paidThisMonth = $this->createBooking('PAID-NOV', '2026-11-02 10:00:00');
        Payment::create([
            'booking_id' => $paidThisMonth->id,
            'amount' => 123000,
            'status' => 'paid',
            'verified_at' => '2026-11-02 10:05:00',
        ]);

        $pendingThisMonth = $this->createBooking('PENDING-NOV', '2026-11-03 10:00:00');
        Payment::create([
            'booking_id' => $pendingThisMonth->id,
            'amount' => 45000,
            'status' => 'pending',
        ]);

        $paidLastMonth = $this->createBooking('PAID-OCT', '2026-10-15 10:00:00');
        Payment::create([
            'booking_id' => $paidLastMonth->id,
            'amount' => 77000,
            'status' => 'paid',
            'verified_at' => '2026-10-15 10:05:00',
        ]);

        $legacyPaid = $this->createBooking('LEGACY-UNVERIFIED', '2026-11-01 10:00:00');
        Payment::create([
            'booking_id' => $legacyPaid->id,
            'amount' => 90000,
            'status' => 'paid',
        ]);

        $response = $this->get(route('admin.revenue.index', [
            'month' => '11',
            'year' => '2026',
        ]));

        $response->assertOk()
            ->assertSee('123.000')
            ->assertSee('200.000')
            ->assertSee('Pembayaran terverifikasi')
            ->assertSee('Pembayaran dikonfirmasi')
            ->assertSee('md:hidden', false)
            ->assertSee('PAID-NOV')
            ->assertDontSee('PENDING-NOV')
            ->assertDontSee('PAID-OCT')
            ->assertDontSee('LEGACY-UNVERIFIED');
    }

    public function test_success_page_does_not_mark_a_pending_payment_as_paid(): void
    {
        $booking = $this->createBooking('PENDING-SUCCESS', '2026-11-03 10:00:00');
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => 50000,
            'status' => 'pending',
        ]);

        $response = $this->get(route('booking.success', $booking->booking_code));

        $response->assertOk()
            ->assertSee('Menunggu Konfirmasi Pembayaran')
            ->assertSee('Total Tagihan');
        $this->assertSame('pending', $booking->fresh()->status);
        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertNull($payment->fresh()->paid_at);
    }

    public function test_midtrans_notification_marks_payment_paid_only_after_server_verification(): void
    {
        config(['services.midtrans.server_key' => 'test-server-key']);

        $booking = $this->createBooking('MIDTRANS-PAID', '2026-11-03 10:00:00');
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => 85000,
            'status' => 'pending',
        ]);

        $service = new class extends MidtransPaymentService
        {
            protected function fetchTransactionStatus(string $transactionId): object
            {
                return (object) [
                    'order_id' => 'MIDTRANS-PAID',
                    'gross_amount' => '85000.00',
                    'transaction_status' => 'settlement',
                    'transaction_id' => $transactionId,
                    'payment_type' => 'qris',
                    'settlement_time' => '2026-11-03 10:05:00',
                ];
            }
        };
        $this->app->instance(PaymentServiceInterface::class, $service);

        $payload = [
            'order_id' => 'MIDTRANS-PAID',
            'transaction_id' => 'midtrans-transaction-123',
            'status_code' => '200',
            'gross_amount' => '85000.00',
        ];
        $payload['signature_key'] = hash(
            'sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].'test-server-key'
        );

        $response = $this->postJson(route('midtrans.notification'), $payload);

        $response->assertOk();
        $this->assertSame('confirmed', $booking->fresh()->status);
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('qris', $payment->fresh()->payment_method);
        $this->assertSame('2026-11-03 10:05:00', $payment->fresh()->paid_at);
        $this->assertNotNull($payment->fresh()->verified_at);
    }

    public function test_invalid_midtrans_signature_cannot_mark_a_payment_paid(): void
    {
        config(['services.midtrans.server_key' => 'test-server-key']);

        $booking = $this->createBooking('MIDTRANS-INVALID', '2026-11-03 10:00:00');
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => 85000,
            'status' => 'pending',
        ]);

        $service = new class extends MidtransPaymentService
        {
            protected function fetchTransactionStatus(string $transactionId): object
            {
                throw new \LogicException('Invalid signatures must not trigger a Midtrans API request.');
            }
        };
        $this->app->instance(PaymentServiceInterface::class, $service);

        $response = $this->postJson(route('midtrans.notification'), [
            'order_id' => 'MIDTRANS-INVALID',
            'transaction_id' => 'midtrans-transaction-invalid',
            'status_code' => '200',
            'gross_amount' => '85000.00',
            'signature_key' => 'not-a-valid-signature',
        ]);

        $response->assertForbidden();
        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertNull($payment->fresh()->verified_at);
    }

    public function test_simulated_checkout_is_marked_as_test_data_not_real_revenue(): void
    {
        $booking = $this->createBooking('SIMULATED-BOOKING', '2026-11-03 10:00:00');
        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => 50000,
            'status' => 'pending',
        ]);

        $response = $this->post(route('checkout.process', $booking->booking_code));

        $response->assertRedirect(route('booking.success', $booking->id));
        $this->assertSame('simulated', $payment->fresh()->status);
        $this->assertNull($payment->fresh()->paid_at);
    }

    private function createBooking(string $bookingCode, string $createdAt): Booking
    {
        return Booking::create([
            'booking_code' => $bookingCode,
            'customer_name' => 'Test User',
            'customer_phone' => '08123456789',
            'booking_date' => Carbon::parse($createdAt)->toDateString(),
            'start_time' => '08:00:00',
            'end_time' => '22:00:00',
            'duration' => 1,
            'day_pass_price' => 50000,
            'product_total' => 0,
            'total_price' => 50000,
            'status' => 'pending',
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}
