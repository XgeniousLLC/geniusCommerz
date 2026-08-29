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

    /**
     * Map an IPQualityScore phone response onto the same vocabulary.
     *
     * Their `fraud_score` runs 0 (safe) to 100 (fraudulent) — the inverse of ours — and
     * an invalid or VOIP line is a strong signal on its own.
     */
    public static function fromIpQualityScore(array $result): array
    {
        $fraudScore = (int) ($result['fraud_score'] ?? 50);
        $score      = max(0, 100 - $fraudScore);

        $level = match (true) {
            ($result['valid'] ?? true) === false => 'high_risk',
            $fraudScore >= 85                    => 'high_risk',
            $fraudScore >= 60                    => 'mid_risk',
            $fraudScore >= 35                    => 'low_risk',
            default                              => 'safe',
        };

        return array_merge($result, [
            'provider'   => 'ipqualityscore',
            'risk_level' => $level,
            'risk_score' => $score,
            'summary'    => [
                'valid'   => $result['valid'] ?? null,
                'carrier' => $result['carrier'] ?? null,
                'line_type' => $result['line_type'] ?? null,
                'country' => $result['country'] ?? null,
            ],
            'reports'    => [],
            'couriers'   => [],
        ]);
    }

    /**
     * Normalise any provider's numeric score onto the shared vocabulary.
     *
     * Most services publish a 0-100 risk score where higher means riskier — the inverse
     * of ours. Passing $higherIsRiskier = false handles the few that score confidence
     * instead, so each driver states its direction rather than silently inverting.
     */
    public static function fromRiskScore(
        float $score,
        array $raw,
        string $provider,
        bool $higherIsRiskier = true,
        ?string $forceLevel = null,
    ): array {
        $risk  = $higherIsRiskier ? $score : (100 - $score);
        $safety = (int) round(max(0, min(100, 100 - $risk)));

        $level = $forceLevel ?? match (true) {
            $risk >= 80 => 'high_risk',
            $risk >= 55 => 'mid_risk',
            $risk >= 30 => 'low_risk',
            default     => 'safe',
        };

        return array_merge($raw, [
            'provider'   => $provider,
            'risk_level' => $level,
            'risk_score' => $safety,
            'reports'    => $raw['reports'] ?? [],
            'couriers'   => $raw['couriers'] ?? [],
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
