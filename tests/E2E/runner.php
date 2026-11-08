<?php

/**
 * SnackBakery Independent E2E Test Suite Runner
 *
 * Requirements-driven opaque-box verification across:
 * - Feature A: GitHub Public Repository
 * - Feature B: Supabase Cloud PostgreSQL Database
 * - Feature C: Vercel Serverless Deployment & Live HTTP 200
 *
 * Usage:
 *   php -d extension=pdo_pgsql tests/e2e/runner.php [options]
 *
 * Options:
 *   --all                 Run all 40 tests across all 4 tiers (default)
 *   --tier=<1|2|3|4>      Run specific tier
 *   --feature=<name>      Filter by feature: github, supabase, vercel
 *   --progressive         Progressive milestone mode (skips unreached milestones with status report)
 *   --strict              Strict requirement verification (fails on unreached milestones)
 *   --url=<url>           Override live Vercel production URL
 *   --json                Output results in JSON format
 *   --help                Display help documentation
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Tests\E2E\Support\E2EEnvironment;
use Tests\E2E\Tier1FeatureCoverageTest;
use Tests\E2E\Tier2BoundaryCornerTest;
use Tests\E2E\Tier3CrossFeatureTest;
use Tests\E2E\Tier4RealWorldScenarioTest;

// Parse CLI arguments
$options = [
    'tier'        => null,
    'feature'     => null,
    'progressive' => false,
    'strict'      => false,
    'url'         => null,
    'json'        => false,
];

foreach ($argv as $arg) {
    if (str_starts_with($arg, '--tier=')) {
        $options['tier'] = (int) substr($arg, 7);
    } elseif (str_starts_with($arg, '--feature=')) {
        $options['feature'] = strtolower(substr($arg, 10));
    } elseif ($arg === '--progressive') {
        $options['progressive'] = true;
    } elseif ($arg === '--strict') {
        $options['strict'] = true;
    } elseif (str_starts_with($arg, '--url=')) {
        $options['url'] = substr($arg, 6);
    } elseif ($arg === '--json') {
        $options['json'] = true;
    } elseif ($arg === '--help') {
        echo <<<HELP
SnackBakery E2E Test Suite Runner

Usage:
  php -d extension=pdo_pgsql tests/e2e/runner.php [options]

Options:
  --all                 Run all 40 tests (default)
  --tier=<1|2|3|4>      Run specific tier (1, 2, 3, or 4)
  --feature=<name>      Filter by feature: github | supabase | vercel
  --progressive         Progressive milestone mode (marks pending milestones)
  --strict              Strict mode (fails if milestones are pending)
  --url=<url>           Override live production URL (e.g. https://snackbakery.vercel.app)
  --json                Output machine-readable JSON
  --help                Show this help message

HELP;
        exit(0);
    }
}

// Default to progressive if not strict and env says progressive or default
if (!$options['strict']) {
    $envProg = getenv('E2E_PROGRESSIVE');
    if ($envProg === '1' || $envProg === 'true' || $options['progressive']) {
        $options['progressive'] = true;
        putenv('E2E_PROGRESSIVE=1');
    }
} else {
    putenv('E2E_PROGRESSIVE=0');
}

if ($options['url']) {
    putenv("VERCEL_URL={$options['url']}");
    putenv("APP_URL={$options['url']}");
}

// Terminal color helpers
$isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
$supportsAnsi = !$options['json'] && (function_exists('sapi_windows_vt100_support') ? sapi_windows_vt100_support(STDOUT) : true);

$GLOBALS['supportsAnsi'] = $supportsAnsi;

function color(string $text, string $colorCode): string {
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

// Inventory of all test cases
$testCatalog = [
    // TIER 1
    [
        'id'       => 'T1.1',
        'tier'     => 1,
        'feature'  => 'github',
        'class'    => Tier1FeatureCoverageTest::class,
        'method'   => 'test_github_remote_origin_configured',
        'title'    => 'GitHub remote origin configured to SnackBakery',
    ],
    [
        'id'       => 'T1.2',
        'tier'     => 1,
        'feature'  => 'github',
        'class'    => Tier1FeatureCoverageTest::class,
        'method'   => 'test_github_remote_repo_accessible',
        'title'    => 'GitHub repository exists and is accessible',
    ],
    [
        'id'       => 'T1.3',
        'tier'     => 1,
        'feature'  => 'github',
        'class'    => Tier1FeatureCoverageTest::class,
        'method'   => 'test_github_remote_repo_is_public',
        'title'    => 'GitHub repository visibility is public (R1)',
    ],
    [
        'id'       => 'T1.4',
        'tier'     => 1,
        'feature'  => 'github',
        'class'    => Tier1FeatureCoverageTest::class,
        'method'   => 'test_github_default_branch_is_main',
        'title'    => 'Active default branch is main',
    ],
    [
        'id'       => 'T1.5',
        'tier'     => 1,
        'feature'  => 'github',
        'class'    => Tier1FeatureCoverageTest::class,
        'method'   => 'test_github_local_commits_synchronized_with_remote',
        'title'    => 'Local commits on main synchronized to remote origin',
    ],
    [
        'id'       => 'T1.6',
        'tier'     => 1,
        'feature'  => 'supabase',
        'class'    => Tier1FeatureCoverageTest::class,
        'method'   => 'test_supabase_database_configuration_in_env',
        'title'    => 'Supabase pgsql credentials and cloud host in .env',
    ],
    [
        'id'       => 'T1.7',
        'tier'     => 1,
        'feature'  => 'supabase',
        'class'    => Tier1FeatureCoverageTest::class,
        'method'   => 'test_supabase_cloud_database_connectivity',
        'title'    => 'Direct PDO connection to Supabase cloud PostgreSQL',
    ],
    [
        'id'       => 'T1.8',
        'tier'     => 1,
        'feature'  => 'supabase',
        'class'    => Tier1FeatureCoverageTest::class,
        'method'   => 'test_supabase_migrations_table_exists',
        'title'    => 'Laravel migrations table exists in Supabase',
    ],
    [
        'id'       => 'T1.9',
        'tier'     => 1,
        'feature'  => 'supabase',
        'class'    => Tier1FeatureCoverageTest::class,
        'method'   => 'test_supabase_all_domain_and_system_tables_exist',
        'title'    => 'All 8 domain & system tables present in public schema',
    ],
    [
        'id'       => 'T1.10',
        'tier'     => 1,
        'feature'  => 'supabase',
        'class'    => Tier1FeatureCoverageTest::class,
        'method'   => 'test_supabase_migration_batch_records_complete',
        'title'    => 'All 8 migration file records executed and recorded',
    ],
    [
        'id'       => 'T1.11',
        'tier'     => 1,
        'feature'  => 'vercel',
        'class'    => Tier1FeatureCoverageTest::class,
        'method'   => 'test_vercel_configuration_files_valid',
        'title'    => 'Vercel deployment files (vercel.json, api/index.php) valid',
    ],
    [
        'id'       => 'T1.12',
        'tier'     => 1,
        'feature'  => 'vercel',
        'class'    => Tier1FeatureCoverageTest::class,
        'method'   => 'test_vercel_live_production_endpoint_returns_http_200',
        'title'    => 'Live Vercel production endpoint returns HTTP 200 (R3)',
    ],
    [
        'id'       => 'T1.13',
        'tier'     => 1,
        'feature'  => 'vercel',
        'class'    => Tier1FeatureCoverageTest::class,
        'method'   => 'test_vercel_root_redirects_to_login',
        'title'    => 'Root endpoint (/) redirects to /login (302/200)',
    ],
    [
        'id'       => 'T1.14',
        'tier'     => 1,
        'feature'  => 'vercel',
        'class'    => Tier1FeatureCoverageTest::class,
        'method'   => 'test_vercel_livewire_asset_returns_javascript',
        'title'    => 'Livewire client script (/livewire/livewire.js) served',
    ],
    [
        'id'       => 'T1.15',
        'tier'     => 1,
        'feature'  => 'vercel',
        'class'    => Tier1FeatureCoverageTest::class,
        'method'   => 'test_vercel_health_check_endpoint',
        'title'    => 'Built-in Laravel health check (/up) returns HTTP 200',
    ],

    // TIER 2
    [
        'id'       => 'T2.1',
        'tier'     => 2,
        'feature'  => 'github',
        'class'    => Tier2BoundaryCornerTest::class,
        'method'   => 'test_github_remote_rejects_legacy_repo_name',
        'title'    => 'Remote rejects legacy mini-erp-snack-bakery name',
    ],
    [
        'id'       => 'T2.2',
        'tier'     => 2,
        'feature'  => 'github',
        'class'    => Tier2BoundaryCornerTest::class,
        'method'   => 'test_github_gitignore_excludes_sensitive_and_large_directories',
        'title'    => '.gitignore excludes .env, /vendor, node_modules, .agents',
    ],
    [
        'id'       => 'T2.3',
        'tier'     => 2,
        'feature'  => 'github',
        'class'    => Tier2BoundaryCornerTest::class,
        'method'   => 'test_github_no_uncommitted_conflicts_or_markers',
        'title'    => 'Zero unresolved merge conflict markers in codebase',
    ],
    [
        'id'       => 'T2.4',
        'tier'     => 2,
        'feature'  => 'github',
        'class'    => Tier2BoundaryCornerTest::class,
        'method'   => 'test_github_commit_history_is_valid',
        'title'    => 'Git commit history integrity (40-char SHA and author)',
    ],
    [
        'id'       => 'T2.5',
        'tier'     => 2,
        'feature'  => 'github',
        'class'    => Tier2BoundaryCornerTest::class,
        'method'   => 'test_github_remote_branch_up_to_date',
        'title'    => 'Local main branch is up to date with origin/main',
    ],
    [
        'id'       => 'T2.6',
        'tier'     => 2,
        'feature'  => 'supabase',
        'class'    => Tier2BoundaryCornerTest::class,
        'method'   => 'test_supabase_ssl_mode_enforced',
        'title'    => 'PostgreSQL SSL encryption strictly enforced (DB_SSLMODE=require)',
    ],
    [
        'id'       => 'T2.7',
        'tier'     => 2,
        'feature'  => 'supabase',
        'class'    => Tier2BoundaryCornerTest::class,
        'method'   => 'test_supabase_table_column_types_and_precision',
        'title'    => 'Decimal precision and column datatypes match schema specs',
    ],
    [
        'id'       => 'T2.8',
        'tier'     => 2,
        'feature'  => 'supabase',
        'class'    => Tier2BoundaryCornerTest::class,
        'method'   => 'test_supabase_foreign_key_constraints_enforced',
        'title'    => 'PostgreSQL foreign key constraints active on resep table',
    ],
    [
        'id'       => 'T2.9',
        'tier'     => 2,
        'feature'  => 'supabase',
        'class'    => Tier2BoundaryCornerTest::class,
        'method'   => 'test_supabase_connection_timeout_limit',
        'title'    => 'Cloud DB query latency meets SLA (< 5000ms)',
    ],
    [
        'id'       => 'T2.10',
        'tier'     => 2,
        'feature'  => 'supabase',
        'class'    => Tier2BoundaryCornerTest::class,
        'method'   => 'test_supabase_cloud_isolation_from_localhost',
        'title'    => 'Cloud DB host does not resolve to 127.0.0.1 or localhost',
    ],
    [
        'id'       => 'T2.11',
        'tier'     => 2,
        'feature'  => 'vercel',
        'class'    => Tier2BoundaryCornerTest::class,
        'method'   => 'test_vercel_security_headers_and_no_debug_leakage',
        'title'    => 'Production responses hide debug stack traces and fatal errors',
    ],
    [
        'id'       => 'T2.12',
        'tier'     => 2,
        'feature'  => 'vercel',
        'class'    => Tier2BoundaryCornerTest::class,
        'method'   => 'test_vercel_invalid_route_returns_404',
        'title'    => 'Non-existent route returns clean HTTP 404',
    ],
    [
        'id'       => 'T2.13',
        'tier'     => 2,
        'feature'  => 'vercel',
        'class'    => Tier2BoundaryCornerTest::class,
        'method'   => 'test_vercel_built_assets_manifest_exists_and_serves',
        'title'    => 'Vite compiled CSS/JS static assets serve with HTTP 200',
    ],
    [
        'id'       => 'T2.14',
        'tier'     => 2,
        'feature'  => 'vercel',
        'class'    => Tier2BoundaryCornerTest::class,
        'method'   => 'test_vercel_temporary_storage_redirection',
        'title'    => 'VIEW_COMPILED_PATH redirects to /tmp for read-only filesystem',
    ],
    [
        'id'       => 'T2.15',
        'tier'     => 2,
        'feature'  => 'vercel',
        'class'    => Tier2BoundaryCornerTest::class,
        'method'   => 'test_vercel_cold_start_and_response_time_under_sla',
        'title'    => 'Serverless response time under SLA (< 10000ms)',
    ],

    // TIER 3
    [
        'id'       => 'T3.1',
        'tier'     => 3,
        'feature'  => 'cross',
        'class'    => Tier3CrossFeatureTest::class,
        'method'   => 'test_cross_git_commit_matches_vercel_deployment',
        'title'    => 'Cross: GitHub remote linked to Vercel deployment source',
    ],
    [
        'id'       => 'T3.2',
        'tier'     => 3,
        'feature'  => 'cross',
        'class'    => Tier3CrossFeatureTest::class,
        'method'   => 'test_cross_vercel_app_queries_supabase_without_local_services',
        'title'    => 'Cross: Live Vercel app queries Supabase without local MySQL',
    ],
    [
        'id'       => 'T3.3',
        'tier'     => 3,
        'feature'  => 'cross',
        'class'    => Tier3CrossFeatureTest::class,
        'method'   => 'test_cross_session_storage_in_cloud_db_or_cookie',
        'title'    => 'Cross: Serverless session and CSRF cookie issuance',
    ],
    [
        'id'       => 'T3.4',
        'tier'     => 3,
        'feature'  => 'cross',
        'class'    => Tier3CrossFeatureTest::class,
        'method'   => 'test_cross_migrations_parity_between_codebase_and_cloud_db',
        'title'    => 'Cross: 1-to-1 parity between local migrations and Supabase',
    ],
    [
        'id'       => 'T3.5',
        'tier'     => 3,
        'feature'  => 'cross',
        'class'    => Tier3CrossFeatureTest::class,
        'method'   => 'test_cross_env_example_matches_cloud_requirements',
        'title'    => 'Cross: .env.example defines all required cloud database keys',
    ],

    // TIER 4
    [
        'id'       => 'T4.1',
        'tier'     => 4,
        'feature'  => 'scenario',
        'class'    => Tier4RealWorldScenarioTest::class,
        'method'   => 'test_scenario_unauthenticated_user_landing_journey',
        'title'    => 'Scenario: Landing redirect -> Login form with CSRF & Livewire',
    ],
    [
        'id'       => 'T4.2',
        'tier'     => 4,
        'feature'  => 'scenario',
        'class'    => Tier4RealWorldScenarioTest::class,
        'method'   => 'test_scenario_livewire_asset_and_runtime_handshake',
        'title'    => 'Scenario: Client-side Livewire bundle fetch and handshake',
    ],
    [
        'id'       => 'T4.3',
        'tier'     => 4,
        'feature'  => 'scenario',
        'class'    => Tier4RealWorldScenarioTest::class,
        'method'   => 'test_scenario_health_check_endpoint',
        'title'    => 'Scenario: Automated health monitoring contract (/up)',
    ],
    [
        'id'       => 'T4.4',
        'tier'     => 4,
        'feature'  => 'scenario',
        'class'    => Tier4RealWorldScenarioTest::class,
        'method'   => 'test_scenario_database_operational_read_write_cycle',
        'title'    => 'Scenario: Supabase PostgreSQL atomic CRUD lifecycle',
    ],
    [
        'id'       => 'T4.5',
        'tier'     => 4,
        'feature'  => 'scenario',
        'class'    => Tier4RealWorldScenarioTest::class,
        'method'   => 'test_scenario_full_end_to_end_cloud_independence',
        'title'    => 'Scenario: 100% cloud independence (zero local services active)',
    ],
];

// Header banner
if (!$options['json']) {
    echo "\n" . color("==================================================================", 'cyan') . "\n";
    echo color("       SnackBakery Independent E2E Test Suite (Tiers 1-4)         ", 'bold') . "\n";
    echo color("==================================================================", 'cyan') . "\n";
    echo " PHP Runtime:   " . PHP_VERSION . " (" . (extension_loaded('pdo_pgsql') ? 'pdo_pgsql active' : 'pdo_pgsql inactive') . ")\n";
    echo " Git Remote:    " . (E2EEnvironment::getGitRemoteUrl() ?? 'none') . "\n";
    echo " Supabase Host: " . (E2EEnvironment::isSupabaseConfigured() ? E2EEnvironment::getSupabaseConfig()['host'] : 'not configured') . "\n";
    echo " Vercel URL:    " . (E2EEnvironment::isVercelUrlConfigured() ? E2EEnvironment::getVercelUrl() : 'not configured') . "\n";
    echo " Test Mode:     " . ($options['progressive'] ? color("PROGRESSIVE MILESTONE MODE", 'yellow') : color("STRICT E2E MODE", 'blue')) . "\n";
    echo color("------------------------------------------------------------------", 'cyan') . "\n\n";
}

$results = [
    'passed'  => 0,
    'failed'  => 0,
    'skipped' => 0,
    'total'   => 0,
    'tests'   => [],
];

$instances = [];
$startTime = microtime(true);

$currentTier = null;

foreach ($testCatalog as $item) {
    if ($options['tier'] !== null && $item['tier'] !== $options['tier']) {
        continue;
    }
    if ($options['feature'] !== null && $item['feature'] !== $options['feature']) {
        continue;
    }

    if (!$options['json'] && $currentTier !== $item['tier']) {
        $currentTier = $item['tier'];
        $tierTitles = [
            1 => 'TIER 1: FEATURE COVERAGE (Primary Acceptance Paths)',
            2 => 'TIER 2: BOUNDARY & CORNER CASES (Stress, SLA, Hygiene)',
            3 => 'TIER 3: CROSS-FEATURE INTEGRATION (GitHub, Supabase, Vercel)',
            4 => 'TIER 4: REAL-WORLD SCENARIOS (User Flows, Livewire, CRUD)',
        ];
        echo color("\n" . $tierTitles[$currentTier] . "\n", 'bold');
        echo str_repeat('-', 66) . "\n";
    }

    $results['total']++;
    $cls = $item['class'];
    $method = $item['method'];

    if (!isset($instances[$cls])) {
        $instances[$cls] = new $cls($method);
    }

    $testInstance = $instances[$cls];
    $status = 'PASS';
    $message = '';
    $duration = 0;

    $t0 = microtime(true);
    try {
        $testInstance->$method();
        $duration = (microtime(true) - $t0) * 1000;
        $results['passed']++;
        $status = 'PASS';
    } catch (\PHPUnit\Framework\SkippedTestError $e) {
        $duration = (microtime(true) - $t0) * 1000;
        $results['skipped']++;
        $status = 'PENDING';
        $message = $e->getMessage();
    } catch (\Throwable $e) {
        $duration = (microtime(true) - $t0) * 1000;
        $results['failed']++;
        $status = 'FAIL';
        $message = $e->getMessage();
    }

    $results['tests'][] = [
        'id'       => $item['id'],
        'tier'     => $item['tier'],
        'feature'  => $item['feature'],
        'title'    => $item['title'],
        'status'   => $status,
        'duration' => round($duration, 2),
        'message'  => $message,
    ];

    if (!$options['json']) {
        $badge = match ($status) {
            'PASS'    => color('[PASS]   ', 'green'),
            'FAIL'    => color('[FAIL]   ', 'red'),
            'PENDING' => color('[PENDING]', 'yellow'),
        };
        $timing = sprintf("%6.1fms", $duration);
        printf(" %s  %-5s  %-44s %s\n", $badge, $item['id'], substr($item['title'], 0, 44), color($timing, 'cyan'));
        if ($status === 'FAIL') {
            echo color("         -> Error: " . trim($message) . "\n", 'red');
        } elseif ($status === 'PENDING') {
            echo color("         -> Note:  " . trim($message) . "\n", 'yellow');
        }
    }
}

$totalTime = (microtime(true) - $startTime) * 1000;

if ($options['json']) {
    echo json_encode([
        'summary' => [
            'total'      => $results['total'],
            'passed'     => $results['passed'],
            'failed'     => $results['failed'],
            'pending'    => $results['skipped'],
            'duration_ms'=> round($totalTime, 2),
        ],
        'tests' => $results['tests'],
    ], JSON_PRETTY_PRINT);
} else {
    echo "\n" . color("==================================================================", 'cyan') . "\n";
    echo color("                       EXECUTIVE SUMMARY                          ", 'bold') . "\n";
    echo color("==================================================================", 'cyan') . "\n";
    printf(" Total Tests:   %d\n", $results['total']);
    printf(" Passed:        %s\n", color((string) $results['passed'], 'green'));
    printf(" Failed:        %s\n", $results['failed'] > 0 ? color((string) $results['failed'], 'red') : '0');
    printf(" Pending:       %s\n", $results['skipped'] > 0 ? color((string) $results['skipped'], 'yellow') : '0');
    printf(" Execution Time: %.2f ms\n", $totalTime);

    echo color("------------------------------------------------------------------", 'cyan') . "\n";
    echo color(" MILESTONE READINESS ASSESSMENT:\n", 'bold');

    $m1Pass = E2EEnvironment::getGitRemoteUrl() && str_contains(E2EEnvironment::getGitRemoteUrl(), 'SnackBakery');
    $m2Pass = E2EEnvironment::isSupabaseConfigured() && E2EEnvironment::getSupabasePdo(2) !== null;
    $m3Pass = E2EEnvironment::isVercelUrlConfigured();

    printf(" [M1] GitHub Public Repo:     %s\n", $m1Pass ? color("PASSED / READY", 'green') : color("PENDING (awaiting M1 implementer)", 'yellow'));
    printf(" [M2] Supabase Cloud DB:      %s\n", $m2Pass ? color("PASSED / READY", 'green') : color("PENDING (awaiting M2 implementer)", 'yellow'));
    printf(" [M3] Vercel Live Deployment: %s\n", $m3Pass ? color("PASSED / READY", 'green') : color("PENDING (awaiting M3 implementer)", 'yellow'));
    printf(" [M4] Full E2E Integration:   %s\n", ($m1Pass && $m2Pass && $m3Pass && $results['failed'] === 0) ? color("PASSED / READY", 'green') : color("PENDING", 'yellow'));
    echo color("==================================================================\n", 'cyan');
}

// Exit code logic:
// If strict mode and any failed/pending: exit 1
// If progressive mode and zero fails: exit 0 (even if some are pending)
if ($results['failed'] > 0) {
    exit(1);
}
if ($options['strict'] && $results['skipped'] > 0) {
    exit(1);
}

exit(0);
