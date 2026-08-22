<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'type',
        'discount_value',
        'min_order_amount',
        'max_discount',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function calculateDiscount(float $subtotal): float
    {
        if (!$this->is_active) return 0;
        if ($this->expires_at && $this->expires_at->isPast()) return 0;
        if ($subtotal < $this->min_order_amount) return 0;

        if ($this->type === 'percent') {
            $discount = ($subtotal * $this->discount_value) / 100;
            if ($this->max_discount) {
                $discount = min($discount, $this->max_discount);
            }
            return round($discount, 2);
        }

        return min((float)$this->discount_value, $subtotal);
    }
}
