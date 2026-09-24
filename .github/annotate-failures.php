<?php

error_reporting(E_ALL & ~E_DEPRECATED & ~E_WARNING);

const LIMIT = 40;

function wc(string $line, int $max): string
{
    $line = trim(preg_replace('/\s+/', ' ', $line));
    return (extension_loaded('mbstring') ? mb_substr($line, 0, $max) : substr($line, 0, $max));
}

$shown = 0;

try {
    $junit = '/tmp/junit.xml';
    if (is_file($junit)) {
        $xml = @file_get_contents($junit);
        if ($xml !== false && trim($xml) !== '') {
            $blocks = preg_split('/<testcase\b/', $xml);
            if (is_array($blocks)) {
                array_shift($blocks);
                foreach ($blocks as $b) {
                    if (!preg_match('/<(failure|error)\b[^>]*(?:message="([^"]*)")?(?:>(.*?)<\/(?:failure|error)>)?/s', $b, $m)) {
                        continue;
                    }
                    $type   = $m[1];
                    $msgRaw = (isset($m[2]) && $m[2] !== '') ? $m[2] : (isset($m[3]) ? $m[3] : '');
                    $msg    = html_entity_decode($msgRaw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                    $name   = preg_match('/name="([^"]*)"/', $b, $nm) ? $nm[1] : 'test';
                    $file   = preg_match('/file="([^"]*)"/', $b, $fm) ? basename($fm[1]) : 'tests';
                    $line   = preg_match('/line="(\d+)"/', $b, $lm) ? $lm[1] : '1';
                    echo "::error file={$file},line={$line}::" . wc("{$name} [{$type}] {$msg}", 900) . "\n";
                    $shown++;
                    if ($shown >= LIMIT) {
                        break;
                    }
                }
            }
        } else {
            echo "::error::JUnit XML file missing or empty at {$junit}\n";
        }
    }
} catch (Throwable $e) {
    echo "::error::annotate-failures parse exception: " . wc($e->getMessage(), 300) . "\n";
}

foreach (['/tmp/phpunit.log' => 'phpunit.log', 'storage/logs/laravel.log' => 'laravel.log'] as $path => $label) {
    if (!is_file($path)) {
        continue;
    }
    $lines = @file($path);
    if ($lines === false) {
        continue;
    }
    $tail = array_slice($lines, -60);
    echo "::error::----- {$label} tail (" . count($tail) . " lines) -----\n";
    foreach ($tail as $l) {
        $l = rtrim($l);
        if ($l === '') {
            continue;
        }
        echo "::error::" . wc("[{$label}] {$l}", 500) . "\n";
    }
}

echo "Annotated {$shown} failing test(s); log tails dumped.\n";
exit(0);