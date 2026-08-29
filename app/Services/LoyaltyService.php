<?php

namespace App\Services;

use App\Models\LoyaltyPoint;
use App\Models\LoyaltySetting;
use App\Models\Order;
use App\Models\User;

class LoyaltyService
{
    public function isEnabled(): bool
    {
        return (bool) LoyaltySetting::get('enabled', false);
    }

    public function getBalance(User $user): int
    {
        return (int) LoyaltyPoint::where('user_id', $user->id)->sum('points');
    }

    public function canRedeem(User $user, int $points): bool
    {
        if ($points <= 0) {
            return false;
        }

        $minRedemption = (int) LoyaltySetting::get('min_redemption', 100);

        if ($points < $minRedemption) {
            return false;
        }

        return $this->getBalance($user) >= $points;
    }

    /**
     * The settings keys are still named `taka_per_point` / `points_per_taka` — renaming
     * them would need a data migration for no functional gain. They mean "per unit of the
     * store's base currency".
     */
    public function pointsToCurrency(int $points): float
    {
        $takaPerPoint = (float) LoyaltySetting::get('taka_per_point', 0.5);

        return round($points * $takaPerPoint, 2);
    }

    public function currencyToPoints(float $taka): int
    {
        $pointsPerTaka = (float) LoyaltySetting::get('points_per_taka', 0.1);

        return (int) floor($taka * $pointsPerTaka);
    }

    public function earnPoints(Order $order): void
    {
        if (! $this->isEnabled()) {
            return;
        }

        if (! $order->user_id) {
            return;
        }

        $user = $order->user;

        $basePoints = $this->currencyToPoints((float) $order->total);

        if ($basePoints <= 0) {
            return;
        }

        $tier      = $this->getTier($user);
        $bonusPct  = (int) ($tier['bonus_pct'] ?? 0);
        $points    = $basePoints + (int) floor($basePoints * $bonusPct / 100);

        $balance = $this->getBalance($user) + $points;

        LoyaltyPoint::create([
            'user_id'       => $user->id,
            'order_id'      => $order->id,
            'type'          => 'earned',
            'points'        => $points,
            'balance_after' => $balance,
            'description'   => "Earned for order #{$order->order_number}" . ($bonusPct > 0 ? " (+{$bonusPct}% {$tier['name']} bonus)" : ''),
        ]);
    }

    public function redeemPoints(User $user, int $points, Order $order): float
    {
        if (! $this->canRedeem($user, $points)) {
            return 0.0;
        }

        $maxPct      = (int) LoyaltySetting::get('max_redemption_pct', 20);
        $maxDiscount = (float) $order->total * $maxPct / 100;
        $discount    = min($this->pointsToCurrency($points), $maxDiscount);

        $pointsToUse = $this->currencyToPoints($discount) ?: $points;
        $balance     = $this->getBalance($user) - $pointsToUse;

        LoyaltyPoint::create([
            'user_id'       => $user->id,
            'order_id'      => $order->id,
            'type'          => 'redeemed',
            'points'        => -$pointsToUse,
            'balance_after' => $balance,
            'description'   => "Redeemed for order #{$order->order_number}",
        ]);

        return round($discount, 2);
    }

    public function reverseEarnedPoints(Order $order): void
    {
        if (! $order->user_id) {
            return;
        }

        $earned = LoyaltyPoint::where('order_id', $order->id)
            ->where('type', 'earned')
            ->sum('points');

        if ($earned <= 0) {
            return;
        }

        $user    = $order->user;
        $balance = $this->getBalance($user) - $earned;

        LoyaltyPoint::create([
            'user_id'       => $user->id,
            'order_id'      => $order->id,
            'type'          => 'adjusted',
            'points'        => -$earned,
            'balance_after' => max(0, $balance),
            'description'   => "Points reversed for cancelled order #{$order->order_number}",
        ]);
    }

    public function getTier(User $user): array
    {
        $tiers = LoyaltySetting::get('tiers', [
            ['name' => 'Silver',   'min' => 0,     'max' => 4999,  'bonus_pct' => 0],
            ['name' => 'Gold',     'min' => 5000,  'max' => 19999, 'bonus_pct' => 5],
            ['name' => 'Platinum', 'min' => 20000, 'max' => null,  'bonus_pct' => 10],
        ]);

        $totalEarned = (int) LoyaltyPoint::where('user_id', $user->id)
            ->where('type', 'earned')
            ->sum('points');

        $matched = $tiers[0] ?? ['name' => 'Silver', 'min' => 0, 'max' => null, 'bonus_pct' => 0];

        foreach ($tiers as $tier) {
            $min = (int) $tier['min'];
            $max = isset($tier['max']) && $tier['max'] !== null ? (int) $tier['max'] : null;

            if ($totalEarned >= $min && ($max === null || $totalEarned <= $max)) {
                $matched = $tier;
                break;
            }
        }

        return $matched;
    }

    public function adjust(User $user, int $points, string $description, ?int $orderId = null): void
    {
        $balance = $this->getBalance($user) + $points;

        LoyaltyPoint::create([
            'user_id'       => $user->id,
            'order_id'      => $orderId,
            'type'          => 'adjusted',
            'points'        => $points,
            'balance_after' => max(0, $balance),
            'description'   => $description,
        ]);
    }
}
