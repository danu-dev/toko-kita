<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\User;
use App\Models\Wallet;
use Exception;
use Illuminate\Support\Facades\DB;

class OrderService
{
    /**
     * Allowed State Transitions:
     * MENUNGGU_KONFIRMASI -> DIPROSES | DIBATALKAN
     * DIPROSES -> SIAP_DIAMBIL_DIKIRIM | DIBATALKAN
     * SIAP_DIAMBIL_DIKIRIM -> SELESAI
     * SELESAI -> RETUR_REFUND (via dispute)
     */
    const STATUS_MENUNGGU = 'menunggu_konfirmasi';
    const STATUS_DIPROSES = 'diproses';
    const STATUS_SIAP = 'siap_diambil_dikirim';
    const STATUS_SELESAI = 'selesai';
    const STATUS_DIBATALKAN = 'dibatalkan';
    const STATUS_RETUR = 'retur_refund';

    /**
     * Transition order status and update audit trail & wallet if completed
     */
    public function transition(Order $order, string $newStatus, ?User $actor = null, ?string $notes = null): Order
    {
        return DB::transaction(function () use ($order, $newStatus, $actor, $notes) {
            $fromStatus = $order->status;

            if ($fromStatus === $newStatus) {
                return $order;
            }

            // Validation rule matrix
            if (!$this->canTransition($fromStatus, $newStatus, $actor, $order)) {
                throw new Exception("Perpindahan status dari [{$fromStatus}] ke [{$newStatus}] tidak diizinkan.");
            }

            $order->status = $newStatus;

            if ($newStatus === self::STATUS_DIPROSES && !$order->confirmed_at) {
                $order->confirmed_at = now();
            } elseif ($newStatus === self::STATUS_SIAP && !$order->ready_at) {
                $order->ready_at = now();
            } elseif ($newStatus === self::STATUS_SELESAI) {
                $order->completed_at = now();

                // Credit seller wallet held_balance (anti-fraud hold 24h)
                $wallet = Wallet::firstOrCreate(['store_id' => $order->store_id]);
                $wallet->increment('balance', $order->seller_earnings);

                // Add loyalty points to buyer (1 point per Rp 1.000)
                $earnedPoints = (int) floor($order->subtotal / 1000);
                if ($order->buyer && $earnedPoints > 0) {
                    $order->buyer->increment('loyalty_points', $earnedPoints);
                }

                // Increment product sales
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->increment('total_sales', $item->quantity);
                    }
                }
            } elseif ($newStatus === self::STATUS_DIBATALKAN) {
                $order->cancelled_at = now();
                $order->cancelled_by = $actor ? $actor->id : null;
                $order->cancellation_reason = $notes;

                // Restock products
                foreach ($order->items as $item) {
                    if ($item->product) {
                        $item->product->increment('stock', $item->quantity);
                    }
                }
            }

            $order->save();

            // Record audit trail
            OrderStatusHistory::create([
                'order_id' => $order->id,
                'from_status' => $fromStatus,
                'to_status' => $newStatus,
                'changed_by' => $actor ? $actor->id : null,
                'notes' => $notes ?: $this->getDefaultHistoryNote($newStatus),
                'created_at' => now(),
            ]);

            return $order;
        });
    }

    public function canTransition(string $from, string $to, ?User $actor, Order $order): bool
    {
        // Cancel logic
        if ($to === self::STATUS_DIBATALKAN) {
            // Buyer can cancel only before 'diproses'
            if ($actor && $actor->id === $order->buyer_id) {
                return $from === self::STATUS_MENUNGGU;
            }
            // Seller/Admin can cancel before 'selesai'
            return in_array($from, [self::STATUS_MENUNGGU, self::STATUS_DIPROSES]);
        }

        return match ($from) {
            self::STATUS_MENUNGGU => in_array($to, [self::STATUS_DIPROSES, self::STATUS_DIBATALKAN]),
            self::STATUS_DIPROSES => in_array($to, [self::STATUS_SIAP, self::STATUS_DIBATALKAN]),
            self::STATUS_SIAP => in_array($to, [self::STATUS_SELESAI]),
            self::STATUS_SELESAI => in_array($to, [self::STATUS_RETUR]),
            default => false,
        };
    }

    private function getDefaultHistoryNote(string $status): string
    {
        return match ($status) {
            self::STATUS_MENUNGGU => 'Pesanan berhasil dibuat.',
            self::STATUS_DIPROSES => 'Penjual menerima dan memproses pesanan.',
            self::STATUS_SIAP => 'Pesanan telah siap untuk diambil atau dikirim.',
            self::STATUS_SELESAI => 'Pesanan telah selesai diterima pembeli.',
            self::STATUS_DIBATALKAN => 'Pesanan dibatalkan.',
            self::STATUS_RETUR => 'Pengajuan komplain/retur disetujui.',
            default => 'Status pesanan diperbarui.',
        };
    }
}
