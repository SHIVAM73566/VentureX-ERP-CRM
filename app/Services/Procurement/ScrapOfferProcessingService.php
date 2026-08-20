<?php

namespace App\Services\Procurement;

use App\Models\SupplierOffer;

class ScrapOfferProcessingService
{
    /**
     * Expected composition ranges keyed by grade. Values are [min, max] percentages.
     *
     * @var array<string, array<string, array{0: float, 1: float}>>
     */
    public const GRADE_CHEMISTRY = [
        'copper' => ['cu' => [99.0, 100.0], 'fe' => [0.0, 0.5], 'pb' => [0.0, 0.3]],
        'ss_304' => ['ni' => [8.0, 10.5], 'cr' => [18.0, 20.0], 'fe' => [65.0, 74.0]],
        'ss_316' => ['ni' => [10.0, 14.0], 'cr' => [16.0, 18.0], 'mo' => [2.0, 3.0], 'fe' => [62.0, 72.0]],
        'brass_60_40' => ['cu' => [59.0, 62.0], 'zn' => [37.0, 40.0]],
        'aluminium' => ['al' => [95.0, 100.0]],
        'hms' => ['fe' => [90.0, 100.0]],
    ];

    public function gradeKey(?string $grade, ?string $material): string
    {
        $text = strtolower(trim(($grade ?? '').' '.($material ?? '')));

        if (str_contains($text, '316') || str_contains($text, 'ss316')) {
            return 'ss_316';
        }
        if (str_contains($text, '304') || str_contains($text, 'ss304')) {
            return 'ss_304';
        }
        if (str_contains($text, 'copper') || str_contains($text, 'barley')) {
            return 'copper';
        }
        if (str_contains($text, 'brass') || str_contains($text, 'drinkery')) {
            return 'brass_60_40';
        }
        if (str_contains($text, 'aluminium') || str_contains($text, 'aluminum') || str_contains($text, 'tense')) {
            return 'aluminium';
        }
        if (str_contains($text, 'hms') || str_contains($text, 'heavy melting')) {
            return 'hms';
        }

        return '';
    }

    public function chemistryMatches(array $data, string $gradeKey): bool
    {
        if (! $gradeKey || ! isset(self::GRADE_CHEMISTRY[$gradeKey])) {
            return false;
        }

        $expected = self::GRADE_CHEMISTRY[$gradeKey];
        $reported = 0;

        foreach ($expected as $element => [$min, $max]) {
            $value = $data[$element.'_percent'] ?? $data[$element] ?? null;
            if ($value === null || $value === '') {
                continue;
            }
            $reported++;
            $value = (float) $value;
            if ($value < $min || $value > $max) {
                return false;
            }
        }

        return $reported > 0;
    }

    public function reportedElements(array $data): array
    {
        $elements = ['cu', 'fe', 'ni', 'cr', 'pb', 'zn', 'al', 'mn', 'mo'];

        $found = [];
        foreach ($elements as $element) {
            $value = $data[$element.'_percent'] ?? null;
            if ($value !== null && $value !== '') {
                $found[$element] = (float) $value;
            }
        }

        return $found;
    }

    /**
     * Assign quality status per the business rules.
     * GREEN  – complete, COA available, chemistry matches grade.
     * YELLOW – missing information or verification required.
     * RED    – significant chemistry/grade mismatch or high risk.
     *
     * The system never approves or rejects a supplier — this is analysis only.
     */
    public function assessQuality(array $data): string
    {
        $required = ['supplier_id', 'material_description', 'quantity_mt', 'price_per_mt'];
        foreach ($required as $field) {
            $value = $data[$field] ?? null;
            if ($value === null || $value === '') {
                return 'YELLOW';
            }
        }

        $gradeKey = $this->gradeKey($data['grade'] ?? null, $data['material_category'] ?? '');

        if ($gradeKey) {
            $reported = $this->reportedElements($data);
            if (empty($reported)) {
                return 'YELLOW'; // chemistry not reported — cannot verify grade
            }
            if (! $this->chemistryMatches($data, $gradeKey)) {
                return 'RED';
            }
        }

        if (($data['coa_available'] ?? false) === false) {
            return 'YELLOW'; // acceptable but requires COA verification
        }

        return 'GREEN';
    }

    public function riskLevel(string $quality): string
    {
        return match ($quality) {
            'GREEN' => 'low',
            'YELLOW' => 'medium',
            default => 'high',
        };
    }

    public function gradeMatch(string $quality, ?string $gradeKey = null): string
    {
        if (! $gradeKey) {
            return 'unknown';
        }

        return match ($quality) {
            'RED' => 'mismatch',
            'GREEN' => 'match',
            default => 'partial',
        };
    }

    public function estimatedValue(?float $quantity, ?float $price): ?float
    {
        if ($quantity === null || $price === null) {
            return null;
        }

        return round($quantity * $price, 2);
    }

    /**
     * Produce the analysis payload persisted in ai_analysis.
     */
    public function analyse(array $data): array
    {
        $gradeKey = $this->gradeKey($data['grade'] ?? null, $data['material_category'] ?? '');
        $quality = $this->assessQuality($data);
        $reported = $this->reportedElements($data);

        $issues = [];
        if (($data['coa_available'] ?? false) === false) {
            $issues[] = 'COA not provided';
        }
        foreach (['supplier_id', 'material_description', 'quantity_mt', 'price_per_mt'] as $field) {
            if (empty($data[$field] ?? null)) {
                $issues[] = "Missing field: {$field}";
            }
        }
        if ($quality === 'RED') {
            $issues[] = 'Chemistry/grade mismatch detected';
        }

        return [
            'grade_key' => $gradeKey,
            'quality_status' => $quality,
            'risk_level' => $this->riskLevel($quality),
            'grade_match' => $this->gradeMatch($quality, $gradeKey),
            'reported_elements' => $reported,
            'expected_elements' => $gradeKey ? (self::GRADE_CHEMISTRY[$gradeKey] ?? null) : null,
            'issues' => $issues,
            'human_decision_required' => true,
            'processing' => 'ScrapOfferProcessingService',
        ];
    }

    public function apply(SupplierOffer $offer): SupplierOffer
    {
        $analysis = $this->analyse([
            ...$offer->only([
                'supplier_id', 'material_category', 'material_description', 'grade', 'isri_grade',
                'quantity_mt', 'price_per_mt', 'cu_percent', 'fe_percent', 'ni_percent', 'cr_percent',
                'pb_percent', 'zn_percent', 'al_percent', 'mn_percent', 'mo_percent', 'coa_available',
            ]),
        ]);

        $offer->forceFill([
            'quality_status' => $analysis['quality_status'],
            'risk_level' => $analysis['risk_level'],
            'grade_match' => $analysis['grade_match'],
            'estimated_metal_value' => $this->estimatedValue((float) $offer->quantity_mt, (float) $offer->price_per_mt),
            'ai_analysis' => $analysis,
        ])->save();

        return $offer;
    }
}
