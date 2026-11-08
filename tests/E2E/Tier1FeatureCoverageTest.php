<?php

namespace Tests\E2E;

use Tests\E2E\Support\E2EEnvironment;

/**
 * Tier 1: Feature Coverage Tests (Primary Acceptance Paths)
 *
 * Verifies core functionality for GitHub public repository connection,
 * Supabase PostgreSQL cloud database, and Vercel serverless live deployment.
 */
class Tier1FeatureCoverageTest extends E2ETestCase
{
    // =========================================================================
    // FEATURE A: GITHUB PUBLIC REPOSITORY CONNECTION
    // =========================================================================

    /**
     * T1.1: Verify remote origin is configured to https://github.com/dlpnn11/SnackBakery(.git)
     */
    public function test_github_remote_origin_configured(): void
    {
        $remote = E2EEnvironment::getGitRemoteUrl('origin');

        $this->skipIfMilestonePending('M1', empty($remote) || str_contains($remote, 'mini-erp'), 'GitHub remote origin not yet updated to SnackBakery.');

        $this->assertNotNull($remote, "Git remote 'origin' must be configured.");
        $this->assertMatchesRegularExpression(
            '#github\.com[/:](dlpnn11/)?SnackBakery(\.git)?$#i',
            $remote,
            "Remote origin must point to SnackBakery on user's GitHub account. Got: $remote"
        );
    }

    /**
     * T1.2: Verify the remote GitHub repository exists and is accessible.
     */
    public function test_github_remote_repo_accessible(): void
    {
        $url = 'https://api.github.com/repos/dlpnn11/SnackBakery';
        $headers = ['Accept: application/vnd.github.v3+json'];
        $token = E2EEnvironment::get('GITHUB_TOKEN') ?: E2EEnvironment::get('GITHUB_PERSONAL_ACCESS_TOKEN');
        if ($token) {
            $headers[] = "Authorization: Bearer $token";
        }

        $res = E2EEnvironment::httpRequest($url, 'GET', $headers);

        $this->skipIfMilestonePending('M1', $res['status'] === 404, 'Remote repository dlpnn11/SnackBakery not yet created on GitHub.');

        $this->assertTrue(
            in_array($res['status'], [200, 301, 302]),
            "GitHub repository 'dlpnn11/SnackBakery' should be accessible via GitHub API. Received HTTP {$res['status']}."
        );
    }

    /**
     * T1.3: Verify the GitHub repository is public (Acceptance Criteria R1).
     */
    public function test_github_remote_repo_is_public(): void
    {
        $url = 'https://api.github.com/repos/dlpnn11/SnackBakery';
        $headers = ['Accept: application/vnd.github.v3+json'];
        $token = E2EEnvironment::get('GITHUB_TOKEN') ?: E2EEnvironment::get('GITHUB_PERSONAL_ACCESS_TOKEN');
        if ($token) {
            $headers[] = "Authorization: Bearer $token";
        }

        $res = E2EEnvironment::httpRequest($url, 'GET', $headers);

        $this->skipIfMilestonePending('M1', $res['status'] === 404, 'Remote repository dlpnn11/SnackBakery not yet created.');

        if ($res['status'] === 200) {
            $data = json_decode($res['body'], true);
            $this->assertIsArray($data, "GitHub API response must be valid JSON.");
            $this->assertFalse($data['private'] ?? true, "Repository must be PUBLIC as required by R1.");
            $this->assertEquals('public', $data['visibility'] ?? 'public', "Repository visibility must be public.");
        } else {
            // Unauthenticated check fallback: Public repos return HTML 200 on browser URL
            $webRes = E2EEnvironment::httpRequest('https://github.com/dlpnn11/SnackBakery', 'GET');
            $this->assertEquals(200, $webRes['status'], "Public repository should be viewable at https://github.com/dlpnn11/SnackBakery");
        }
    }

    /**
     * T1.4: Verify the active branch is 'main'.
     */
    public function test_github_default_branch_is_main(): void
    {
        $branch = E2EEnvironment::getGitBranch();
        $this->assertEquals('main', $branch, "Local branch must be 'main' for deployment.");
    }

    /**
     * T1.5: Verify local commits on main are pushed to remote origin/main.
     */
    public function test_github_local_commits_synchronized_with_remote(): void
    {
        $remote = E2EEnvironment::getGitRemoteUrl('origin');
        $this->skipIfMilestonePending('M1', empty($remote) || str_contains($remote, 'mini-erp'), 'GitHub remote origin not yet updated.');

        $cmd = "git rev-parse HEAD 2>&1";
        $localHead = trim(@shell_exec($cmd) ?? '');

        $lsCmd = "git ls-remote origin refs/heads/main 2>&1";
        $lsOutput = trim(@shell_exec($lsCmd) ?? '');

        $this->skipIfMilestonePending('M1', empty($lsOutput) || str_contains($lsOutput, 'fatal:'), 'Remote branch main not yet pushed.');

        $this->assertNotEmpty($lsOutput, "Remote origin should have 'main' branch.");
        $this->assertStringContainsString($localHead, $lsOutput, "Local HEAD commit ($localHead) must be present on remote origin/main.");
    }

    // =========================================================================
    // FEATURE B: SUPABASE POSTGRESQL CLOUD DATABASE INTEGRATION
    // =========================================================================

    /**
     * T1.6: Verify .env specifies pgsql cloud database credentials.
     */
    public function test_supabase_database_configuration_in_env(): void
    {
        $cfg = E2EEnvironment::getSupabaseConfig();

        $this->skipIfMilestonePending('M2', $cfg['driver'] !== 'pgsql' || empty($cfg['host']) || $cfg['host'] === '127.0.0.1', 'Supabase credentials not yet configured in .env.');

        $this->assertEquals('pgsql', $cfg['driver'], "DB_CONNECTION must be set to 'pgsql'.");
        $this->assertNotEmpty($cfg['host'], "DB_HOST must not be empty.");
        $this->assertNotContains($cfg['host'], ['127.0.0.1', 'localhost', '::1'], "DB_HOST must be a cloud host, not local server.");
        $this->assertContains($cfg['port'], [5432, 6543], "DB_PORT must be PostgreSQL standard 5432 or pooler 6543.");
        $this->assertNotEmpty($cfg['database'], "DB_DATABASE must not be empty.");
    }

    /**
     * T1.7: Verify direct PDO connection to Supabase cloud PostgreSQL database.
     */
    public function test_supabase_cloud_database_connectivity(): void
    {
        $this->skipIfMilestonePending('M2', !E2EEnvironment::isSupabaseConfigured(), 'Supabase not yet configured.');

        $pdo = E2EEnvironment::getSupabasePdo(5);
        $this->assertNotNull($pdo, "Failed to connect to Supabase PostgreSQL cloud database via PDO.");

        $stmt = $pdo->query('SELECT 1 as test, version() as version');
        $row = $stmt->fetch();

        $this->assertEquals(1, $row['test'], "SELECT 1 check should return 1.");
        $this->assertStringContainsString('PostgreSQL', $row['version'], "Database must be PostgreSQL.");
    }

    /**
     * T1.8: Verify Laravel migrations table exists in Supabase.
     */
    public function test_supabase_migrations_table_exists(): void
    {
        $this->skipIfMilestonePending('M2', !E2EEnvironment::isSupabaseConfigured(), 'Supabase not yet configured.');

        $pdo = E2EEnvironment::getSupabasePdo(5);
        $this->assertNotNull($pdo, "Supabase connection required.");

        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM information_schema.tables 
            WHERE table_schema = 'public' AND table_name = 'migrations'
        ");
        $stmt->execute();
        $count = (int) $stmt->fetchColumn();

        $this->assertEquals(1, $count, "The 'migrations' table must exist in Supabase database.");
    }

    /**
     * T1.9: Verify all 8 domain and framework tables exist in Supabase.
     */
    public function test_supabase_all_domain_and_system_tables_exist(): void
    {
        $this->skipIfMilestonePending('M2', !E2EEnvironment::isSupabaseConfigured(), 'Supabase not yet configured.');

        $pdo = E2EEnvironment::getSupabasePdo(5);
        $this->assertNotNull($pdo, "Supabase connection required.");

        $expectedTables = [
            'users',
            'cache',
            'jobs',
            'bahan_baku',
            'produk_jadi',
            'resep',
            'transaksi',
            'detail_transaksi',
        ];

        $stmt = $pdo->query("
            SELECT table_name 
            FROM information_schema.tables 
            WHERE table_schema = 'public'
        ");
        $actualTables = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($expectedTables as $table) {
            $this->assertContains(
                $table,
                $actualTables,
                "Expected table '$table' was not found in Supabase public schema."
            );
        }
    }

    /**
     * T1.10: Verify all 8 migrations have been recorded in the migrations table.
     */
    public function test_supabase_migration_batch_records_complete(): void
    {
        $this->skipIfMilestonePending('M2', !E2EEnvironment::isSupabaseConfigured(), 'Supabase not yet configured.');

        $pdo = E2EEnvironment::getSupabasePdo(5);
        $this->assertNotNull($pdo, "Supabase connection required.");

        $stmt = $pdo->query("SELECT migration FROM migrations ORDER BY id");
        $recorded = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        $expectedMigrations = [
            '0001_01_01_000000_create_users_table',
            '0001_01_01_000001_create_cache_table',
            '0001_01_01_000002_create_jobs_table',
            '2026_05_04_135705_create_bahan_bakus_table',
            '2026_05_04_135714_create_produk_jadis_table',
            '2026_05_04_135722_create_reseps_table',
            '2026_05_04_135731_create_transaksis_table',
            '2026_05_04_135740_create_detail_transaksis_table',
        ];

        foreach ($expectedMigrations as $mig) {
            $this->assertContains(
                $mig,
                $recorded,
                "Migration '$mig' should be recorded in Supabase migrations table."
            );
        }
    }

    // =========================================================================
    // FEATURE C: VERCEL SERVERLESS HOSTING & LIVE DEPLOYMENT
    // =========================================================================

    /**
     * T1.11: Verify Vercel deployment files (vercel.json and api/index.php) exist.
     */
    public function test_vercel_configuration_files_valid(): void
    {
        $root = E2EEnvironment::getProjectRoot();
        $vercelJsonPath = $root . DIRECTORY_SEPARATOR . 'vercel.json';
        $apiIndexPath = $root . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'index.php';

        $this->skipIfMilestonePending('M3', !file_exists($vercelJsonPath) || !file_exists($apiIndexPath), 'Vercel configuration files not yet created.');

        $this->assertFileExists($vercelJsonPath, "vercel.json must exist in project root.");
        $this->assertFileExists($apiIndexPath, "api/index.php serverless entrypoint must exist.");

        $jsonContent = file_get_contents($vercelJsonPath);
        $parsed = json_decode($jsonContent, true);
        $this->assertIsArray($parsed, "vercel.json must contain valid JSON.");

        // Check api/index.php bootstrap
        $entryContent = file_get_contents($apiIndexPath);
        $this->assertStringContainsString('public/index.php', $entryContent, "api/index.php must delegate to public/index.php");
    }

    /**
     * T1.12: Verify automated HTTP request to live Vercel production URL returns HTTP 200 (Acceptance Criteria R3).
     */
    public function test_vercel_live_production_endpoint_returns_http_200(): void
    {
        $url = E2EEnvironment::getVercelUrl();
        $this->skipIfMilestonePending('M3', !E2EEnvironment::isVercelUrlConfigured(), 'Live Vercel URL not yet configured.');

        // Follow redirect from root or query /login directly
        $res = E2EEnvironment::httpRequest($url . '/login', 'GET', [], null, true, 15);

        $this->assertHttpStatus(200, $res, "Live Vercel /login endpoint");
        $this->assertStringContainsString('SnackBakery', $res['body'], "Response body should contain SnackBakery branding or title.");
    }

    /**
     * T1.13: Verify root endpoint (/) issues a 302 redirect to /login.
     */
    public function test_vercel_root_redirects_to_login(): void
    {
        $url = E2EEnvironment::getVercelUrl();
        $this->skipIfMilestonePending('M3', !E2EEnvironment::isVercelUrlConfigured(), 'Live Vercel URL not yet configured.');

        $res = E2EEnvironment::httpRequest($url . '/', 'GET', [], null, false, 10);

        // Root should redirect to login (302) or return 200 if already at destination
        $this->assertTrue(
            in_array($res['status'], [200, 301, 302]),
            "Root URL must redirect to /login (302) or return 200. Got: {$res['status']}"
        );

        if (in_array($res['status'], [301, 302])) {
            $location = $res['headers']['location'] ?? '';
            $this->assertStringContainsString('login', $location, "Redirect target must be /login.");
        }
    }

    /**
     * T1.14: Verify Livewire JavaScript asset is routed and returns valid JS on live Vercel deployment.
     */
    public function test_vercel_livewire_asset_returns_javascript(): void
    {
        $url = E2EEnvironment::getVercelUrl();
        $this->skipIfMilestonePending('M3', !E2EEnvironment::isVercelUrlConfigured(), 'Live Vercel URL not yet configured.');

        $res = E2EEnvironment::httpRequest($url . '/livewire/livewire.js', 'GET', [], null, true, 10);

        $this->assertHttpStatus(200, $res, "Livewire script endpoint");
        $contentType = $res['headers']['content-type'] ?? '';
        $this->assertStringContainsString('javascript', strtolower($contentType), "Content-Type must be JavaScript.");
        $this->assertStringContainsString('Livewire', $res['body'], "Script body should contain Livewire initialization.");
    }

    /**
     * T1.15: Verify Laravel 11/12/13 built-in health check route (/up) returns HTTP 200.
     */
    public function test_vercel_health_check_endpoint(): void
    {
        $url = E2EEnvironment::getVercelUrl();
        $this->skipIfMilestonePending('M3', !E2EEnvironment::isVercelUrlConfigured(), 'Live Vercel URL not yet configured.');

        $res = E2EEnvironment::httpRequest($url . '/up', 'GET', [], null, false, 10);

        $this->assertHttpStatus(200, $res, "Application health endpoint /up");
    }
}
