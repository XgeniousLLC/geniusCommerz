<?php

namespace App\Services;

class FraudScorer
{
    /**
     * Score a BDCourier result and return enriched array with risk_level and risk_score.
     */
    public static function scoreBdCourier(array $result): array
    {
        $summary     = $result['summary']  ?? [];
        $reports     = $result['reports']  ?? [];
        $reportCount = count($reports);
        $total       = (int) ($summary['total_parcel'] ?? 0);
        $ratio       = (float) ($summary['success_ratio'] ?? 0);

        if ($total === 0 && $reportCount === 0) {
            return array_merge($result, [
                'risk_level' => 'unknown',
                'risk_score' => 50,
            ]);
        }

        // Penalty: 20 points per fraud report, capped at 60
        $penalty = min(60, $reportCount * 20);
        $score   = (int) max(0, $ratio - $penalty);

        $level = match(true) {
            $reportCount >= 3            => 'high_risk',
            $score >= 80 && $reportCount === 0 => 'safe',
            $score >= 65                 => 'low_risk',
            $score >= 45                 => 'mid_risk',
            default                      => 'high_risk',
        };

        return array_merge($result, [
            'risk_level' => $level,
            'risk_score' => $score,
        ]);
    }

    /**
     * Map FraudBD risk_level string to our standard levels.
     */
    public static function fromFraudBd(array $result): array
    {
        // FraudBD sets risk_level per courier; use the worst one as overall
        $levels  = array_column($result['couriers'] ?? [], 'risk_level');
        $worst   = self::worstLevel($levels);
        $score   = self::scoreFromLevel($worst);

        return array_merge($result, [
            'risk_level' => $worst ?? 'unknown',
            'risk_score' => $score,
        ]);
    }

    public static function riskColor(string $level): string
    {
        return match($level) {
            'safe'      => 'success',
            'low_risk'  => 'info',
            'mid_risk'  => 'warning',
            'high_risk' => 'danger',
            default     => 'muted',
        };
    }

    public static function riskLabel(string $level): string
    {
        return match($level) {
            'safe'      => 'Safe',
            'low_risk'  => 'Low Risk',
            'mid_risk'  => 'Mid Risk',
            'high_risk' => 'High Risk',
            default     => 'Unknown',
        };
    }

    private static function worstLevel(array $levels): string
    {
        $order = ['safe' => 0, 'new_customer' => 0, 'low' => 1, 'medium' => 2, 'high' => 3, 'very_high' => 4];
        $worst = null;
        $worstVal = -1;

        foreach ($levels as $l) {
            $val = $order[$l] ?? -1;
            if ($val > $worstVal) {
                $worstVal = $val;
                $worst    = $l;
            }
        }

        return match($worst) {
            'low'       => 'low_risk',
            'medium'    => 'mid_risk',
            'high',
            'very_high' => 'high_risk',
            'safe',
            'new_customer' => 'safe',
            default     => 'unknown',
        };
    }

    private static function scoreFromLevel(string $level): int
    {
        return match($level) {
            'safe'      => 90,
            'low_risk'  => 70,
            'mid_risk'  => 45,
            'high_risk' => 20,
            default     => 50,
        };
    }
}
