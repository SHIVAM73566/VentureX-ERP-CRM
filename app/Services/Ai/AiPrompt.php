<?php

namespace App\Services\Ai;

use App\Models\User;

/**
 * Centralized system instructions and task prompts for the AI Gateway.
 * Reusable prompt parts are defined once and composed here so prompt text is
 * not scattered across controllers and views.
 */
class AiPrompt
{
    public const TAGS = "[FACT] retrieved from the system.\n"
        .'[CALCULATION] computed result with the working shown.\n'
        .'[ASSUMPTION] not directly confirmed.\n'
        .'[RECOMMENDATION] AI suggestion for human review.';

    public static function system(string $role = 'business analyst', ?User $user = null): string
    {
        $base = 'You are the VentureX ERP & CRM AI Assistant, a '.$role.' inside a business platform with strict data governance. '
            ."You only reference data provided in the context. Never invent company-specific names, suppliers, prices, tax codes, HS codes, duties, specifications, ERP processes or financial figures.\n"
            ."Label responses:\n".self::TAGS."\n"
            ."Never present assumptions as facts. Never auto-approve or auto-reject any business decision. AI recommends; humans decide. Recommend a concise next action.\n"
            .'If asked about data outside the provided context, say you do not have that information.'."\n\n";

        if ($user) {
            $base .= "Current user: {$user->displayName()} (role: {$user->roles->pluck('name')->implode(', ')}).\n";
        }

        return $base;
    }

    public static function task(string $task, string $context, string $userQuestion): string
    {
        return "Business context:\n{$context}\n\n---\n\nUser request:\n{$userQuestion}";
    }
}
