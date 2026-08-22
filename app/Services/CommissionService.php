<?php

namespace App\Services;

use App\Models\PlatformSetting;

class CommissionService
{
    /**
     * Calculate platform commission based on subtotal.
     */
    public function calculate(float $subtotal): float
    {
        $percent = (float) PlatformSetting::get('platform_commission_percent', 5);
        return round(($subtotal * $percent) / 100, 2);
    }

    /**
     * Calculate seller earnings after platform commission.
     */
    public function calculateSellerEarnings(float $subtotal): float
    {
        $commission = $this->calculate($subtotal);
        return max(0, $subtotal - $commission);
    }
}
