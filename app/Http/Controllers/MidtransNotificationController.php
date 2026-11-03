<?php

namespace App\Http\Controllers;

use App\Services\Payment\PaymentServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransNotificationController extends Controller
{
    public function __invoke(Request $request, PaymentServiceInterface $paymentService): JsonResponse
    {
        $payload = $request->validate([
            'order_id' => ['required', 'string'],
            'transaction_id' => ['required', 'string'],
            'status_code' => ['required', 'string'],
            'gross_amount' => ['required', 'numeric'],
            'signature_key' => ['required', 'string'],
        ]);

        if (! $paymentService->processNotification($payload)) {
            return response()->json(['message' => 'Notifikasi pembayaran tidak valid.'], 403);
        }

        return response()->json(['message' => 'Notifikasi pembayaran berhasil diproses.']);
    }
}
