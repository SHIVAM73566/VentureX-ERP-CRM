<?php

namespace App\Services\Ai;

/**
 * AI Decision Engine — decides HOW a request should be answered and whether
 * Claude is justified.
 *
 * Priority levels (always use the cheapest sufficient level first):
 *   0 - local business logic / no AI needed
 *   1 - database query + local calculation
 *   2 - cache
 *   3 - simple AI provider
 *   4 - complex/advanced AI provider
 *   5 - Claude high-value reasoning (specialist, high-impact complex tasks)
 *
 * The gate never decides alone: the caller still runs local rules and the
 * cache first. This engine only decides whether Claude (or advanced AI) is
 * justified for the remaining work, using task type + business impact score.
 */
class AiDecisionEngine
{
    public function impact(string $task): int
    {
        $scores = (array) config('ai.impact_scores', []);

        return (int) ($scores[$task] ?? 30);
    }

    public function isComplex(string $task): bool
    {
        return in_array($task, (array) config('ai.complex_tasks', []), true);
    }

    public function shouldUseClaude(string $task): bool
    {
        if (! $this->isComplex($task)) {
            return false;
        }

        $threshold = (int) config('ai.claude.min_impact_score', 70);

        return $this->impact($task) >= $threshold;
    }

    public function shouldUseAdvancedAi(string $task): bool
    {
        if ($this->shouldUseClaude($task)) {
            return true;
        }

        return $this->impact($task) >= 61;
    }

    public function priorityLevel(string $task): int
    {
        if ($this->shouldUseClaude($task)) {
            return 5;
        }

        if ($this->shouldUseAdvancedAi($task)) {
            return 4;
        }

        if ($this->impact($task) >= 31) {
            return 3;
        }

        return 2;
    }
}
