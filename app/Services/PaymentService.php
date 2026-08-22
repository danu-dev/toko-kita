<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Str;

class PaymentService
{
    /**
     * Create payment record for order.
     * Supported Modes:
     * - online_gateway (QRIS, GoPay, OVO, DANA, Virtual Account)
     * - cash_on_delivery (Cash / Tunai di Tempat)
     */
    public function createPayment(Order $order, string $method): Payment
    {
        $prefix = match ($method) {
            'qris' => 'QRIS',
            'gopay' => 'GOPAY',
            'ovo' => 'OVO',
            'dana' => 'DANA',
            'bca_va' => 'BCAVA',
            'mandiri_va' => 'MDVA',
            'cod' => 'CASH',
            default => 'PAY',
        };

        $isCash = ($method === 'cod');

        return Payment::create([
            'order_id' => $order->id,
            'payment_code' => $prefix . '-' . strtoupper(Str::random(10)),
            'method' => $method,
            'amount' => $order->total,
            'status' => $isCash ? 'pending' : 'paid',
            'transaction_reference' => $isCash ? 'TUNAI-COD-' . rand(100000, 999999) : 'PG-TRX-' . strtoupper(Str::random(8)),
            'paid_at' => $isCash ? null : now(),
        ]);
    }
}
