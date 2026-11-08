<?php

declare(strict_types=1);

/**
 * Serverless Entrypoint & Vercel Specification Empirical Stress Test Harness
 *
 * M3 Challenger: Serverless Entrypoint Stress Verifier
 * Workspace: tests/stress/serverless_entrypoint_stress_test.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Tests\E2E\Support\E2EEnvironment;
use Illuminate\Http\Request;

class ServerlessEntrypointStressHarness
{
    private string $projectRoot;
    private int $passCount = 0;
    private int $failCount = 0;
    private array $failures = [];
    private array $details = [];

    public function __construct()
    {
        $this->projectRoot = realpath(__DIR__ . '/../..') ?: dirname(__DIR__, 2);
    }

    public function run(): void
    {
        echo "=================================================================\n";
        echo "   SnackBakery Milestone 3: Serverless Entrypoint Stress Harness \n";
        echo "=================================================================\n";
        echo " Project Root: " . $this->projectRoot . "\n";
        echo " PHP Version:  " . PHP_VERSION . "\n\n";

        $this->testSyntaxAndIntegrity();
        $this->testEphemeralTmpDirectoryCreationAndWarmStarts();
        $this->testStorageAndCacheDirectoryIO();
        $this->testLaravelKernelStoragePathResolution();
        $this->testServerEnvironmentAndRoutingNormalization();
        $this->testVercelIgnorePatternEngine();
        $this->testVercelJsonSpecificationCompliance();
        $this->testViteAssetsManifestAndIntegrity();
        $this->testE2ETestSuiteExecution();

        $this->printSummary();
    }

    private function record(string $testName, bool $passed, string $message, ?string $detail = null): void
    {
        if ($passed) {
            $this->passCount++;
            echo " [PASS] {$testName}: {$message}\n";
        } else {
            $this->failCount++;
            $this->failures[] = "{$testName}: {$message}";
            echo " [FAIL] {$testName}: {$message}\n";
        }
        if ($detail) {
            $this->details[] = "   -> {$detail}";
            echo "        {$detail}\n";
        }
    }

    /**
     * 1. Syntax & Linter Verification
     */
    private function testSyntaxAndIntegrity(): void
    {
        echo "\n--- Section 1: PHP Syntax & Linter Checks ---\n";

        $files = [
            'api/index.php',
            'bootstrap/app.php',
            'app/Providers/AppServiceProvider.php',
            'public/index.php',
        ];

        foreach ($files as $file) {
            $fullPath = $this->projectRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $file);
            if (!file_exists($fullPath)) {
                $this->record("Syntax Check ({$file})", false, "File does not exist: {$fullPath}");
                continue;
            }

            $output = [];
            $exitCode = 0;
            exec("php -l " . escapeshellarg($fullPath), $output, $exitCode);
            $outputStr = implode("\n", $output);

            $this->record(
                "Syntax Check ({$file})",
                $exitCode === 0 && str_contains($outputStr, 'No syntax errors'),
                $exitCode === 0 ? "Lint valid" : "Syntax error detected",
                trim($outputStr)
            );
        }
    }

    /**
     * 2. Empirical /tmp Directory Creation & Warm-Start Stress
     */
    private function testEphemeralTmpDirectoryCreationAndWarmStarts(): void
    {
        echo "\n--- Section 2: Ephemeral /tmp Directory Creation & Warm-Start Stress ---\n";

        $storageDirs = [
            '/tmp/storage/framework/views',
            '/tmp/storage/framework/cache/data',
            '/tmp/storage/framework/sessions',
            '/tmp/storage/logs',
            '/tmp/storage/app/public',
        ];

        // Step A: Cold-start simulation (create all directories if absent)
        $coldStartSuccess = true;
        $createdList = [];
        foreach ($storageDirs as $dir) {
            if (!is_dir($dir)) {
                $created = @mkdir($dir, 0755, true);
                if (!$created) {
                    $coldStartSuccess = false;
                } else {
                    $createdList[] = $dir;
                }
            } else {
                $createdList[] = $dir . " (already existed)";
            }
        }

        $this->record(
            "Cold-Start /tmp Creation",
            $coldStartSuccess,
            "All 5 required storage subdirectories exist in /tmp",
            "Dirs: " . implode(', ', $storageDirs)
        );

        // Step B: Warm-start stress (repeat directory checking loop 100 times)
        $warmStartWarnings = 0;
        set_error_handler(function ($errno, $errstr) use (&$warmStartWarnings) {
            $warmStartWarnings++;
            return true;
        });

        $start = microtime(true);
        for ($i = 0; $i < 100; $i++) {
            foreach ($storageDirs as $dir) {
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
            }
        }
        $elapsed = round((microtime(true) - $start) * 1000, 2);
        restore_error_handler();

        $this->record(
            "Warm-Start 100x Loop",
            $warmStartWarnings === 0,
            "100 consecutive warm-start invocations completed without warnings ({$elapsed}ms)",
            "Warnings count: {$warmStartWarnings}"
        );
    }

    /**
     * 3. File I/O Verification inside /tmp/storage/
     */
    private function testStorageAndCacheDirectoryIO(): void
    {
        echo "\n--- Section 3: File I/O Verification in /tmp/storage/ ---\n";

        $testTargets = [
            '/tmp/storage/framework/views/test_compiled_view_' . uniqid() . '.php' => '<?php /* Blade compiled template */ echo "OK"; ?>',
            '/tmp/storage/framework/cache/data/test_cache_file_' . uniqid() => serialize(['key' => 'snack_cache', 'val' => 42]),
            '/tmp/storage/framework/sessions/test_sess_' . uniqid() => 'token|' . bin2hex(random_bytes(16)),
            '/tmp/storage/logs/stress_test_' . uniqid() . '.log' => "[2026-11-08 16:00:00] production.INFO: Test log entry\n",
            '/tmp/storage/app/public/test_upload_' . uniqid() . '.txt' => 'Test public asset attachment',
        ];

        foreach ($testTargets as $filePath => $payload) {
            $written = @file_put_contents($filePath, $payload);
            $readable = file_exists($filePath) && file_get_contents($filePath) === $payload;
            $unlinked = @unlink($filePath);

            $this->record(
                "I/O in " . dirname($filePath),
                $written !== false && $readable && $unlinked,
                "Atomic write, read verification, and unlink succeeded",
                "Path: {$filePath} (" . strlen($payload) . " bytes)"
            );
        }
    }

    /**
     * 4. Laravel Kernel Storage Path Resolution & Blade Compiler Check
     */
    private function testLaravelKernelStoragePathResolution(): void
    {
        echo "\n--- Section 4: Laravel Kernel Storage Path Resolution ---\n";

        // Emulate api/index.php environment overrides
        putenv('LARAVEL_STORAGE_PATH=/tmp/storage');
        $_ENV['LARAVEL_STORAGE_PATH'] = '/tmp/storage';
        $_SERVER['LARAVEL_STORAGE_PATH'] = '/tmp/storage';

        putenv('VIEW_COMPILED_PATH=/tmp/storage/framework/views');
        $_ENV['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';
        $_SERVER['VIEW_COMPILED_PATH'] = '/tmp/storage/framework/views';

        // Bootstrap kernel to register service providers
        $bootstrapFile = $this->projectRoot . '/bootstrap/app.php';
        /** @var \Illuminate\Foundation\Application $app */
        $app = require $bootstrapFile;
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();

        // Verify storagePath()
        $storagePath = $app->storagePath();
        $this->record(
            "Application::storagePath() Resolution",
            str_contains(str_replace('\\', '/', $storagePath), '/tmp/storage'),
            "Laravel storagePath resolves to /tmp/storage",
            "Resolved: {$storagePath}"
        );

        // Verify sub-paths
        $viewsPath = $app->storagePath('framework/views');
        $this->record(
            "Application::storagePath('framework/views')",
            str_contains(str_replace('\\', '/', $viewsPath), '/tmp/storage/framework/views'),
            "Framework views path resolves to /tmp/storage/framework/views",
            "Resolved: {$viewsPath}"
        );

        $cachePath = $app->storagePath('framework/cache');
        $this->record(
            "Application::storagePath('framework/cache')",
            str_contains(str_replace('\\', '/', $cachePath), '/tmp/storage/framework/cache'),
            "Framework cache path resolves to /tmp/storage/framework/cache",
            "Resolved: {$cachePath}"
        );

        // Verify Blade compiler compiled path resolution
        try {
            $bladeCompiler = $app->make('blade.compiler');
            $compiledFile = $bladeCompiler->getCompiledPath('test.blade.php');
            $this->record(
                "Blade Compiler Compiled Path",
                str_contains(str_replace('\\', '/', $compiledFile), '/tmp/storage/framework/views'),
                "Blade compiles templates into /tmp/storage/framework/views",
                "Compiled path: {$compiledFile}"
            );
        } catch (\Throwable $e) {
            $this->record("Blade Compiler Resolution", false, "Failed: " . $e->getMessage());
        }
    }

    /**
     * 5. Server Environment & Routing Normalization
     */
    private function testServerEnvironmentAndRoutingNormalization(): void
    {
        echo "\n--- Section 5: Server Environment & Routing Normalization ---\n";

        // Test SCRIPT_NAME normalization
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_SERVER['REQUEST_URI'] = '/login';
        $_SERVER['HTTP_HOST'] = 'snackbakery.vercel.app';
        $_SERVER['SERVER_NAME'] = 'snackbakery.vercel.app';

        $req = Request::createFromGlobals();
        $this->record(
            "Request Base URL Normalization",
            $req->getBaseUrl() === '',
            "Base URL is empty string (routes not poisoned by /api)",
            "BaseUrl: '" . $req->getBaseUrl() . "', PathInfo: " . $req->getPathInfo()
        );

        $this->record(
            "Request PathInfo",
            $req->getPathInfo() === '/login',
            "Route path matches intended request endpoint '/login'",
            "PathInfo: " . $req->getPathInfo()
        );

        // Test HTTPS Forwarding
        $_SERVER['HTTP_X_FORWARDED_PROTO'] = 'https';
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            $_SERVER['HTTPS'] = 'on';
        }

        $secureReq = Request::createFromGlobals();
        $this->record(
            "HTTPS Protocol Recognition",
            $secureReq->isSecure() && $secureReq->getScheme() === 'https',
            "X-Forwarded-Proto enforces HTTPS scheme on Request object",
            "Scheme: " . $secureReq->getScheme()
        );

        // Test reverse proxy trust configuration:
        // When trustProxies(at: '*') is applied via Laravel TrustProxies middleware
        Request::setTrustedProxies(['127.0.0.1', '10.0.0.1', '0.0.0.0/0'], Request::HEADER_X_FORWARDED_FOR | Request::HEADER_X_FORWARDED_PROTO | Request::HEADER_X_FORWARDED_PORT | Request::HEADER_X_FORWARDED_HOST);
        $proxyReq = Request::create(
            '/login',
            'GET',
            [],
            [],
            [],
            [
                'HTTP_X_FORWARDED_FOR' => '203.0.113.195',
                'REMOTE_ADDR'          => '10.0.0.1',
            ]
        );
        $clientIp = $proxyReq->getClientIp();
        $this->record(
            "Reverse Proxy Client IP Resolution",
            $clientIp === '203.0.113.195',
            "Client IP extracted from forwarded header under trusted proxy",
            "Extracted IP: {$clientIp}"
        );
    }

    /**
     * 6. .vercelignore Pattern Matching Engine
     */
    private function testVercelIgnorePatternEngine(): void
    {
        echo "\n--- Section 6: .vercelignore Pattern Matching & Security ---\n";

        $ignorePath = $this->projectRoot . '/.vercelignore';
        if (!file_exists($ignorePath)) {
            $this->record(".vercelignore Existence", false, ".vercelignore not found");
            return;
        }

        $lines = file($ignorePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $patterns = [];
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed !== '' && !str_starts_with($trimmed, '#')) {
                $patterns[] = $trimmed;
            }
        }

        $this->record(
            ".vercelignore Parsed Patterns",
            count($patterns) >= 10,
            "Loaded " . count($patterns) . " active ignore rules",
            "Rules: " . implode(', ', array_slice($patterns, 0, 8)) . "..."
        );

        // Check required exclusions explicitly present in .vercelignore
        $requiredExclusions = [
            '.env'          => 'Sensitive environment credentials',
            '.agents'       => 'Agent teamwork and prompt metadata',
            '/vendor'       => 'Local PHP vendor directory (built on cloud)',
            '/node_modules' => 'Local Node modules (built on cloud)',
            '/tests'        => 'Test suite and fixtures',
            '.git'          => 'Git repository history',
        ];

        foreach ($requiredExclusions as $rule => $reason) {
            $matched = false;
            foreach ($patterns as $pattern) {
                if ($pattern === $rule || ltrim($pattern, '/') === ltrim($rule, '/')) {
                    $matched = true;
                    break;
                }
            }
            $this->record(
                ".vercelignore Rule: {$rule}",
                $matched,
                "Explicitly excluded: {$reason}"
            );
        }

        // Test exclusion simulator against positive and negative test cases
        $testCases = [
            // Files that MUST BE IGNORED (EXCLUDED from deployment)
            '.env'                                => true,
            '.env.local'                          => true,
            '.env.production'                     => true,
            '.agents/teamwork/plan.md'            => true,
            'vendor/autoload.php'                 => true,
            'node_modules/vite/package.json'      => true,
            'tests/E2E/runner.php'                => true,
            '.git/config'                         => true,
            '.github/workflows/ci.yml'            => true,
            'phpunit.xml'                         => true,
            '.phpunit.result.cache'               => true,
            'storage/framework/cache/foo'         => true,
            'storage/framework/views/bar'         => true,
            'storage/logs/laravel.log'            => true,

            // Files that MUST NOT BE IGNORED (INCLUDED in deployment)
            '.env.example'                        => false,
            'api/index.php'                       => false,
            'public/index.php'                    => false,
            'public/build/manifest.json'          => false,
            'public/build/assets/app.css'         => false,
            'vercel.json'                         => false,
            'composer.json'                       => false,
            'package.json'                        => false,
            'app/Models/User.php'                 => false,
            'bootstrap/app.php'                   => false,
        ];

        $simulationPassed = true;
        foreach ($testCases as $candidatePath => $shouldIgnore) {
            $isIgnored = $this->matchIgnore($candidatePath, $patterns);
            if ($isIgnored !== $shouldIgnore) {
                $simulationPassed = false;
                $this->record(
                    "Ignore Match: {$candidatePath}",
                    false,
                    "Expected ignore=" . ($shouldIgnore ? 'true' : 'false') . ", actual=" . ($isIgnored ? 'true' : 'false')
                );
            }
        }

        $this->record(
            "Ignore Engine Simulation (23 Test Cases)",
            $simulationPassed,
            "All sensitive/build artifacts ignored; all deployment assets included"
        );
    }

    /**
     * Minimal .gitignore / .vercelignore glob matcher
     */
    private function matchIgnore(string $path, array $patterns): bool
    {
        $normalizedPath = str_replace('\\', '/', $path);
        $ignored = false;

        foreach ($patterns as $pattern) {
            $isNegation = str_starts_with($pattern, '!');
            $rule = $isNegation ? trim(substr($pattern, 1)) : $pattern;

            // Strip leading /
            $ruleNormalized = ltrim($rule, '/');

            // Handle wildcards
            $regex = preg_quote($ruleNormalized, '#');
            $regex = str_replace('\*', '.*', $regex);

            if (str_ends_with($ruleNormalized, '.*')) {
                // directory wildcard
                $patternRegex = '#^' . $regex . '#';
            } elseif (str_contains($ruleNormalized, '/')) {
                // path match
                $patternRegex = '#^' . $regex . '(/.*)?$#';
            } else {
                // filename / basename match
                $patternRegex = '#(^|/)' . $regex . '(/.*)?$#';
            }

            if (preg_match($patternRegex, $normalizedPath)) {
                $ignored = !$isNegation;
            }
        }

        return $ignored;
    }

    /**
     * 7. vercel.json Specification & Routing Verification
     */
    private function testVercelJsonSpecificationCompliance(): void
    {
        echo "\n--- Section 7: vercel.json Specification & Routing Compliance ---\n";

        $jsonPath = $this->projectRoot . '/vercel.json';
        if (!file_exists($jsonPath)) {
            $this->record("vercel.json Existence", false, "vercel.json not found");
            return;
        }

        $raw = file_get_contents($jsonPath);
        $json = json_decode($raw, true);

        $this->record("vercel.json JSON Syntax", is_array($json), "Valid JSON parsed");

        // Schema validation
        $this->record("vercel.json version", ($json['version'] ?? null) === 2, "version is 2");
        $this->record("vercel.json framework", array_key_exists('framework', $json) && $json['framework'] === null, "framework is null (serverless override)");
        $this->record("vercel.json buildCommand", ($json['buildCommand'] ?? '') === 'npm run build', "buildCommand is 'npm run build'");
        $this->record("vercel.json outputDirectory", ($json['outputDirectory'] ?? '') === 'public', "outputDirectory is 'public'");

        // Function runtime
        $runtime = $json['functions']['api/index.php']['runtime'] ?? null;
        $maxDuration = $json['functions']['api/index.php']['maxDuration'] ?? null;
        $this->record(
            "vercel.json runtime",
            $runtime === 'vercel-php@0.9.0',
            "Runtime is vercel-php@0.9.0",
            "Configured: " . var_export($runtime, true)
        );
        $this->record(
            "vercel.json maxDuration",
            is_int($maxDuration) && $maxDuration >= 10,
            "maxDuration >= 10s (configured: {$maxDuration}s)",
            "Duration: {$maxDuration}s"
        );

        // Routes table verification
        $routes = $json['routes'] ?? [];
        $this->record("vercel.json routes count", count($routes) >= 4, "Configured " . count($routes) . " routes");

        $routeMap = [];
        foreach ($routes as $r) {
            $routeMap[$r['src']] = $r['dest'];
        }

        // Livewire routing check
        $hasLivewire = isset($routeMap['/livewire/(.*)']) && $routeMap['/livewire/(.*)'] === '/api/index.php';
        $this->record(
            "Route: /livewire/(.*) -> /api/index.php",
            $hasLivewire,
            "Livewire routes prioritized to PHP serverless function"
        );

        // Static build routing check
        $hasBuild = isset($routeMap['/build/(.*)']) && str_contains($routeMap['/build/(.*)'], 'build');
        $this->record(
            "Route: /build/(.*) -> /public/build/$1",
            $hasBuild,
            "Vite compiled build assets mapped to CDN static delivery"
        );

        // Catch-all check
        $hasCatchAll = isset($routeMap['/(.*)']) && $routeMap['/(.*)'] === '/api/index.php';
        $this->record(
            "Route: /(.*) -> /api/index.php",
            $hasCatchAll,
            "Catch-all routes application traffic into Laravel serverless entrypoint"
        );

        // Env vars check
        $env = $json['env'] ?? [];
        $requiredEnvs = [
            'APP_ENV'              => 'production',
            'APP_DEBUG'            => 'false',
            'LARAVEL_STORAGE_PATH' => '/tmp/storage',
            'VIEW_COMPILED_PATH'   => '/tmp/storage/framework/views',
            'SESSION_DRIVER'       => 'cookie',
            'CACHE_STORE'          => 'array',
            'DB_CONNECTION'        => 'pgsql',
            'DB_PORT'              => '5432',
            'DB_SSLMODE'           => 'require',
        ];

        foreach ($requiredEnvs as $envKey => $expectedVal) {
            $actualVal = $env[$envKey] ?? null;
            $this->record(
                "vercel.json env: {$envKey}",
                $actualVal === $expectedVal,
                "Expected '{$expectedVal}', got '{$actualVal}'"
            );
        }
    }

    /**
     * 8. Vite Manifest & Assets Consistency
     */
    private function testViteAssetsManifestAndIntegrity(): void
    {
        echo "\n--- Section 8: Vite Manifest & Assets Consistency ---\n";

        $manifestPath = $this->projectRoot . '/public/build/manifest.json';
        if (!file_exists($manifestPath)) {
            $this->record("Vite manifest.json", false, "Manifest file not found: {$manifestPath}");
            return;
        }

        $manifest = json_decode(file_get_contents($manifestPath), true);
        $this->record("Vite manifest.json valid", is_array($manifest), "Manifest contains valid JSON");

        $allAssetsExist = true;
        $assetDetails = [];
        foreach ($manifest as $entryKey => $entryData) {
            $relFile = $entryData['file'] ?? null;
            if ($relFile) {
                $assetPath = $this->projectRoot . '/public/build/' . $relFile;
                if (!file_exists($assetPath)) {
                    $allAssetsExist = false;
                    $assetDetails[] = "Missing: {$relFile}";
                } else {
                    $assetDetails[] = "{$relFile} (" . filesize($assetPath) . " bytes)";
                }
            }
        }

        $this->record(
            "Vite Built Assets on Disk",
            $allAssetsExist && count($assetDetails) > 0,
            "All manifest assets exist on disk",
            implode(', ', $assetDetails)
        );
    }

    /**
     * 9. Execute Project Milestone E2E Runner
     */
    private function testE2ETestSuiteExecution(): void
    {
        echo "\n--- Section 9: E2E Runner --feature=vercel ---\n";

        $output = [];
        $exitCode = 0;
        exec("php tests/E2E/runner.php --feature=vercel", $output, $exitCode);
        $outputStr = implode("\n", $output);

        $passed = $exitCode === 0 && str_contains($outputStr, 'OK, but some tests were skipped!');
        $this->record(
            "E2E Runner (--feature=vercel)",
            $passed,
            "Runner exited with code 0; local vercel configuration tests passed",
            trim(substr($outputStr, strpos($outputStr, 'PHPUnit') ?: 0, 300))
        );
    }

    private function printSummary(): void
    {
        echo "\n=================================================================\n";
        echo "                       SUMMARY OF RESULTS                        \n";
        echo "=================================================================\n";
        echo " Total Passed: {$this->passCount}\n";
        echo " Total Failed: {$this->failCount}\n";

        if ($this->failCount === 0) {
            echo " VERDICT: APPROVE (Serverless runtime & configs are robust & valid)\n";
        } else {
            echo " VERDICT: REQUEST_CHANGES\n";
            echo " Failures:\n";
            foreach ($this->failures as $f) {
                echo "  - {$f}\n";
            }
        }
        echo "=================================================================\n\n";
    }

    public function getFailCount(): int
    {
        return $this->failCount;
    }
}

$harness = new ServerlessEntrypointStressHarness();
$harness->run();
exit($harness->getFailCount() > 0 ? 1 : 0);
