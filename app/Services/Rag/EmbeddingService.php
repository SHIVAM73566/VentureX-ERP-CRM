<?php

namespace App\Services\Rag;

/**
 * Deterministic, dependency-free text embedding service.
 *
 * Produces a normalised hashed bag-of-words + bigram vector. It runs entirely
 * locally (no API key, no external call), which means every one of the RAG
 * pipelines works offline and in air-gapped deployments. If a provider-backed
 * embedding model is added later, this class is the single swap point.
 */
class EmbeddingService
{
    public const DIMENSION = 256;

    /** @var array<string, true> */
    protected const STOPWORDS = [
        'a' => true, 'an' => true, 'and' => true, 'are' => true, 'as' => true, 'at' => true,
        'be' => true, 'by' => true, 'for' => true, 'from' => true, 'has' => true, 'have' => true,
        'in' => true, 'is' => true, 'it' => true, 'its' => true, 'of' => true, 'on' => true,
        'or' => true, 'that' => true, 'the' => true, 'this' => true, 'to' => true, 'was' => true,
        'were' => true, 'will' => true, 'with' => true, 'we' => true, 'you' => true, 'our' => true,
        'their' => true, 'they' => true, 'i' => true, 'me' => true, 'my' => true, 'us' => true,
    ];

    public function dimension(): int
    {
        return self::DIMENSION;
    }

    /**
     * @return array<int, float>
     */
    public function embed(string $text): array
    {
        $tokens = $this->tokens($text);
        $vector = array_fill(0, self::DIMENSION, 0.0);

        $count = count($tokens);
        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];
            $hash = abs(crc32($token));
            $index = $hash % self::DIMENSION;
            $sign = (($hash >> 12) & 1) === 1 ? 1.0 : -1.0;
            $vector[$index] += $sign * (1.0 + min(strlen($token), 12) / 12);

            if (isset($tokens[$i + 1])) {
                $bigram = $token.'_'.$tokens[$i + 1];
                $bHash = abs(crc32($bigram));
                $bIndex = $bHash % self::DIMENSION;
                $bSign = (($bHash >> 12) & 1) === 1 ? 1.0 : -1.0;
                $vector[$bIndex] += $bSign * 0.5;
            }
        }

        return $this->normalize($vector);
    }

    /**
     * @param  array<int, float|int>  $a
     * @param  array<int, float|int>  $b
     */
    public function similarity(array $a, array $b): float
    {
        $length = min(count($a), count($b));
        if ($length === 0) {
            return 0.0;
        }

        $dot = 0.0;
        for ($i = 0; $i < $length; $i++) {
            $dot += (float) $a[$i] * (float) $b[$i];
        }

        return max(0.0, min(1.0, $dot));
    }

    /**
     * @return array<int, string>
     */
    public function tokens(string $text): array
    {
        $text = mb_strtolower($text);
        $parts = preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $tokens = [];
        foreach ($parts as $part) {
            if (mb_strlen($part) < 2 || isset(self::STOPWORDS[$part])) {
                continue;
            }
            $tokens[] = $part;
        }

        return $tokens;
    }

    public function tokenCount(string $text): int
    {
        return count($this->tokens($text));
    }

    /**
     * @param  array<int, float>  $vector
     * @return array<int, float>
     */
    protected function normalize(array $vector): array
    {
        $norm = 0.0;
        foreach ($vector as $value) {
            $norm += $value * $value;
        }

        $norm = sqrt($norm);
        if ($norm <= 0.0) {
            return $vector;
        }

        foreach ($vector as $i => $value) {
            $vector[$i] = round($value / $norm, 6);
        }

        return $vector;
    }
}
