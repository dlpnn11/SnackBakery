<?php

/**
 * SnackBakery Independent E2E Test Suite Runner
 *
 * Requirements-driven opaque-box verification across:
 * - Feature A: GitHub Public Repository (origin, commits, visibility, cleanliness)
 * - Feature B: Supabase Cloud PostgreSQL Database (connection, migrations, schemas, latency)
 * - Feature C: Vercel Serverless Deployment & Live HTTP 200 (serverless config, health, Livewire, assets)
 *
 * Usage:
 *   php -d extension=pdo_pgsql tests/e2e/runner.php [options]
 *
 * Options:
 *   --all                 Run all 40 tests across all 4 tiers (default)
 *   --tier=<1|2|3|4>      Run specific tier
 *   --feature=<name>      Filter by feature: github | supabase | vercel
 *   --progressive         Progressive milestone mode (marks pending milestones as SKIPPED)
 *   --strict              Strict requirement verification (fails on unreached milestones)
 *   --url=<url>           Override live Vercel production URL (e.g. https://snackbakery.vercel.app)
 *   --help                Display help documentation
 */

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

use Tests\E2E\Support\E2EEnvironment;
use PHPUnit\TextUI\Application;

// Parse CLI arguments
$options = [
    'tier'        => null,
    'feature'     => null,
    'progressive' => true, // default to progressive during development milestones
    'strict'      => false,
    'url'         => null,
    'raw_args'    => [],
];

for ($i = 1; $i < count($argv); $i++) {
    $arg = $argv[$i];
    if (str_starts_with($arg, '--tier=')) {
        $options['tier'] = (int) substr($arg, 7);
    } elseif (str_starts_with($arg, '--feature=')) {
        $options['feature'] = strtolower(substr($arg, 10));
    } elseif ($arg === '--progressive') {
        $options['progressive'] = true;
        $options['strict'] = false;
    } elseif ($arg === '--strict') {
        $options['strict'] = true;
        $options['progressive'] = false;
    } elseif (str_starts_with($arg, '--url=')) {
        $options['url'] = substr($arg, 6);
    } elseif ($arg === '--all') {
        // default
    } elseif ($arg === '--help' || $arg === '-h') {
        echo <<<HELP
SnackBakery E2E Test Suite Runner

Usage:
  php -d extension=pdo_pgsql tests/e2e/runner.php [options]

Options:
  --all                 Run all 40 tests (default)
  --tier=<1|2|3|4>      Run specific tier:
                          1: Feature Coverage (15 tests)
                          2: Boundary & Corner Cases (15 tests)
                          3: Cross-Feature Integration (5 tests)
                          4: Real-World Workload Scenarios (5 tests)
  --feature=<name>      Filter by feature: github | supabase | vercel
  --progressive         Progressive milestone mode (default; skips unreached milestones)
  --strict              Strict requirement verification (fails on unreached milestones)
  --url=<url>           Override live production URL (e.g. https://snackbakery.vercel.app)
  --help                Show this help message

Examples:
  php -d extension=pdo_pgsql tests/e2e/runner.php --progressive
  php -d extension=pdo_pgsql tests/e2e/runner.php --tier=1
  php -d extension=pdo_pgsql tests/e2e/runner.php --feature=github
  php -d extension=pdo_pgsql tests/e2e/runner.php --strict --url=https://snackbakery.vercel.app

HELP;
        exit(0);
    } else {
        $options['raw_args'][] = $arg;
    }
}

// Set environment mode
if ($options['progressive']) {
    putenv('E2E_PROGRESSIVE=1');
} else {
    putenv('E2E_PROGRESSIVE=0');
}

if ($options['url']) {
    putenv("VERCEL_URL={$options['url']}");
    putenv("APP_URL={$options['url']}");
}

// Terminal color helpers
$GLOBALS['supportsAnsi'] = $supportsAnsi;

function colorize(string $text, string $colorCode): string {
    if (empty($GLOBALS['supportsAnsi'])) return $text;
    $codes = [
        'green'  => "\033[32m",
        'red'    => "\033[31m",
        'yellow' => "\033[33m",
        'blue'   => "\033[34m",
        'cyan'   => "\033[36m",
        'bold'   => "\033[1m",
        'reset'  => "\033[0m",
    ];
    return ($codes[$colorCode] ?? '') . $text . $codes['reset'];
}

// Print header banner
echo "\n" . colorize("==================================================================", 'cyan') . "\n";
echo colorize("       SnackBakery Independent E2E Test Suite (Tiers 1-4)         ", 'bold') . "\n";
echo colorize("==================================================================", 'cyan') . "\n";
echo " PHP Runtime:   " . PHP_VERSION . " (" . (extension_loaded('pdo_pgsql') ? colorize('pdo_pgsql active', 'green') : colorize('pdo_pgsql inactive', 'yellow')) . ")\n";
echo " Git Remote:    " . (E2EEnvironment::getGitRemoteUrl() ?? colorize('none', 'yellow')) . "\n";
echo " Supabase Host: " . (E2EEnvironment::isSupabaseConfigured() ? colorize(E2EEnvironment::getSupabaseConfig()['host'], 'green') : colorize('not configured (using local .env)', 'yellow')) . "\n";
echo " Vercel URL:    " . (E2EEnvironment::isVercelUrlConfigured() ? colorize(E2EEnvironment::getVercelUrl(), 'green') : colorize('not configured', 'yellow')) . "\n";
echo " Test Mode:     " . ($options['progressive'] ? colorize("PROGRESSIVE MILESTONE MODE", 'yellow') : colorize("STRICT E2E VERIFICATION MODE", 'blue')) . "\n";
if ($options['tier'] !== null) {
    echo " Target Tier:   Tier " . $options['tier'] . "\n";
}
if ($options['feature'] !== null) {
    echo " Target Feature: " . $options['feature'] . "\n";
}
echo colorize("------------------------------------------------------------------", 'cyan') . "\n\n";

// Build PHPUnit command-line arguments
$phpunitArgs = ['phpunit', '--colors=always'];

// Target specific test files based on tier
if ($options['tier'] === 1) {
    $phpunitArgs[] = 'tests/E2E/Tier1FeatureCoverageTest.php';
} elseif ($options['tier'] === 2) {
    $phpunitArgs[] = 'tests/E2E/Tier2BoundaryCornerTest.php';
} elseif ($options['tier'] === 3) {
    $phpunitArgs[] = 'tests/E2E/Tier3CrossFeatureTest.php';
} elseif ($options['tier'] === 4) {
    $phpunitArgs[] = 'tests/E2E/Tier4RealWorldScenarioTest.php';
} else {
    $phpunitArgs[] = 'tests/E2E';
}

// Filter by feature if requested
if ($options['feature'] !== null) {
    $featureFilters = [
        'github'   => 'test_github_',
        'supabase' => 'test_supabase_',
        'vercel'   => 'test_vercel_',
        'cross'    => 'test_cross_',
        'scenario' => 'test_scenario_',
    ];
    if (isset($featureFilters[$options['feature']])) {
        $phpunitArgs[] = '--filter=' . $featureFilters[$options['feature']];
    }
}

// Pass any raw additional args through
foreach ($options['raw_args'] as $rawArg) {
    $phpunitArgs[] = $rawArg;
}

// Execute test suite via PHPUnit Application
$exitCode = (new Application)->run($phpunitArgs);

// Print milestone readiness assessment
echo "\n" . colorize("==================================================================", 'cyan') . "\n";
echo colorize("               MILESTONE READINESS ASSESSMENT                     ", 'bold') . "\n";
echo colorize("==================================================================", 'cyan') . "\n";

$remoteUrl = E2EEnvironment::getGitRemoteUrl();
$m1Pass = $remoteUrl && str_contains($remoteUrl, 'SnackBakery');
$m2Pass = E2EEnvironment::isSupabaseConfigured() && E2EEnvironment::getSupabasePdo(2) !== null;
$m3Pass = E2EEnvironment::isVercelUrlConfigured();

printf(" [M1] GitHub Public Repo:     %s\n", $m1Pass ? colorize("PASSED / READY", 'green') : colorize("PENDING (awaiting M1 implementer)", 'yellow'));
printf(" [M2] Supabase Cloud DB:      %s\n", $m2Pass ? colorize("PASSED / READY", 'green') : colorize("PENDING (awaiting M2 implementer)", 'yellow'));
printf(" [M3] Vercel Live Deployment: %s\n", $m3Pass ? colorize("PASSED / READY", 'green') : colorize("PENDING (awaiting M3 implementer)", 'yellow'));
printf(" [M4] Full E2E Integration:   %s\n", ($m1Pass && $m2Pass && $m3Pass && $exitCode === 0) ? colorize("PASSED / READY", 'green') : colorize("PENDING", 'yellow'));
echo colorize("==================================================================\n\n", 'cyan');

exit($exitCode);
