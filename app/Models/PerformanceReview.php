<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'company_id', 'employee_id', 'reviewer_id', 'period', 'review_date',
    'overall_score', 'goals_score', 'competency_score', 'status', 'strengths',
    'improvements', 'goals', 'comments',
])]
class PerformanceReview extends Model
{
    use BelongsToCompany, SoftDeletes;

    public const STATUSES = [
        'draft' => 'Draft',
        'submitted' => 'Submitted',
        'acknowledged' => 'Acknowledged',
    ];

    protected function casts(): array
    {
        return [
            'review_date' => 'date',
            'overall_score' => 'decimal:2',
            'goals_score' => 'decimal:2',
            'competency_score' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function rating(): string
    {
        $score = (float) $this->overall_score;

        return match (true) {
            $score >= 4.5 => 'Outstanding',
            $score >= 3.5 => 'Exceeds expectations',
            $score >= 2.5 => 'Meets expectations',
            $score >= 1.5 => 'Needs improvement',
            default => 'Unsatisfactory',
        };
    }
}
