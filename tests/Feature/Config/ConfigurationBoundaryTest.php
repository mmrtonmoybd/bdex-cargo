<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

/**
 * Section 2.1's rule, made executable.
 *
 * Only the bootstrap Laravel needs to reach the database at all may come from the
 * environment, and that lives in config/, never app/. Every credential, base URL,
 * driver selection and operational number inside app/ must go through
 * DynamicConfig so it stays editable at runtime.
 */
function applicationPhpFiles(?string $directory = null): Finder
{
    return Finder::create()->files()->in($directory ?? app_path())->name('*.php');
}

/**
 * File contents with comments and docblocks removed.
 *
 * Prose explaining the rule is not a breach of it — these checks must look at
 * what the code does, not at what its comments talk about.
 */
function executableCode(SplFileInfo $file): string
{
    return preg_replace('#/\*.*?\*/|//[^\n]*#s', '', (string) file_get_contents($file->getPathname())) ?? '';
}

it('never reads the environment directly from application code', function (): void {
    $offenders = [];

    foreach (applicationPhpFiles() as $file) {
        if (preg_match('/(?<![\w>$])env\s*\(/', executableCode($file)) === 1) {
            $offenders[] = $file->getRelativePathname();
        }
    }

    expect($offenders)->toBe([], 'Use DynamicConfig::get() instead of env() in app/: '.implode(', ', $offenders));
});

it('never reads third-party credentials from the services config', function (): void {
    $offenders = [];

    foreach (applicationPhpFiles() as $file) {
        if (preg_match('/config\s*\(\s*[\'"]services\./', executableCode($file)) === 1) {
            $offenders[] = $file->getRelativePathname();
        }
    }

    expect($offenders)->toBe([], 'Move these to config_entries rows read via DynamicConfig: '.implode(', ', $offenders));
});

it('keeps no rate, fee or tax literal inside the pricing services', function (): void {
    // Structural constants, not prices: the percentage divisor, float comparison
    // epsilon, and number_format's decimal places.
    $structural = ['100', '0.00001'];

    $offenders = [];

    foreach (applicationPhpFiles(app_path('Services/Pricing')) as $file) {
        preg_match_all('/(?<![\w.$\'"])\d+(?:\.\d+)?(?![\w.])/', executableCode($file), $matches);

        foreach ($matches[0] as $literal) {
            // Single digits are array indices, precision arguments and quantities;
            // zero is an absent default, never a price.
            if (strlen($literal) < 2 || (float) $literal === 0.0 || in_array($literal, $structural, true)) {
                continue;
            }

            $offenders[] = $file->getRelativePathname().' ('.$literal.')';
        }
    }

    expect($offenders)->toBe([], 'Every price must come from a database row: '.implode(', ', $offenders));
});
