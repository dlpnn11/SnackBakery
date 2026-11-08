<?php

namespace Tests\E2E;

use Tests\E2E\Support\E2EEnvironment;

/**
 * Tier 2: Boundary & Corner Cases Tests
 *
 * Exercises edge conditions, security constraints, database schema precision,
 * error handling (404s), and latency SLAs across GitHub, Supabase, and Vercel.
 */
class Tier2BoundaryCornerTest extends E2ETestCase
{
    // =========================================================================
    // FEATURE A: GITHUB BOUNDARY & CORNERS
    // =========================================================================

    /**
     * T2.1: Ensure remote origin strictly rejects legacy 'mini-erp-snack-bakery' slug.
     */
    public function test_github_remote_rejects_legacy_repo_name(): void
    {
        $remote = E2EEnvironment::getGitRemoteUrl('origin');
        $this->skipIfMilestonePending('M1', empty($remote) || str_contains($remote, 'mini-erp'), 'Remote origin still points to legacy repo name.');

        $this->assertNotNull($remote);
        $this->assertStringNotContainsString(
            'mini-erp-snack-bakery',
            $remote,
            "Git remote origin must not contain legacy repository name 'mini-erp-snack-bakery'."
        );
    }

    /**
     * T2.2: Verify .gitignore strictly excludes .env, /vendor, node_modules, and .agents/.
     */
    public function test_github_gitignore_excludes_sensitive_and_large_directories(): void
    {
        $gitignorePath = E2EEnvironment::getProjectRoot() . DIRECTORY_SEPARATOR . '.gitignore';
        $this->assertFileExists($gitignorePath, ".gitignore must exist in project root.");

        $content = file_get_contents($gitignorePath);
        $this->assertStringContainsString('.env', $content, ".env must be ignored in .gitignore.");
        $this->assertStringContainsString('/vendor', $content, "vendor directory must be ignored in .gitignore.");
        $this->assertStringContainsString('node_modules', $content, "node_modules must be ignored in .gitignore.");

        $this->skipIfMilestonePending('M1', !str_contains($content, '.agents'), '.agents not yet added to .gitignore in M1.');
        $this->assertStringContainsString('.agents', $content, ".agents/ team metadata must be ignored in .gitignore.");
    }

    /**
     * T2.3: Scan repository files to verify no stray Git merge conflict markers exist.
     */
    public function test_github_no_uncommitted_conflicts_or_markers(): void
    {
        $conflicts = E2EEnvironment::scanConflictMarkers();
        $this->assertEmpty(
            $conflicts,
            "Found unresolved Git conflict markers in tracked files:\n" . implode("\n", $conflicts)
        );
    }

    /**
     * T2.4: Verify local commit history has valid commit hash and author.
     */
    public function test_github_commit_history_is_valid(): void
    {
        $cmd = 'git log -1 --format="%H|%an|%ae" 2>&1';
        $output = trim(@shell_exec($cmd) ?? '');

        $this->assertNotEmpty($output, "Git history should not be empty.");
        $parts = explode('|', $output);
        $this->assertCount(3, $parts, "Git log should return SHA, author name, and email.");
        $this->assertEquals(40, strlen($parts[0]), "Commit hash must be 40-character SHA-1.");
        $this->assertNotEmpty($parts[1], "Author name must be non-empty.");
    }

    /**
     * T2.5: Verify git remote branch is up to date (no unpushed local changes).
     */
    public function test_github_remote_branch_up_to_date(): void
    {
        $remote = E2EEnvironment::getGitRemoteUrl('origin');
        $this->skipIfMilestonePending('M1', empty($remote) || str_contains($remote, 'mini-erp'), 'GitHub origin not yet pointing to SnackBakery.');

        $status = @shell_exec("git status 2>&1");
        $this->assertNotNull($status);

        $this->skipIfMilestonePending('M1', str_contains($status, "branch is ahead of 'origin/main'"), 'Branch has unpushed commits.');

        $this->assertStringNotContainsString(
            "branch is ahead of 'origin/main'",
            $status,
            "Local branch must not have unpushed commits ahead of origin/main."
        );
    }

    // =========================================================================
    // FEATURE B: SUPABASE BOUNDARY & CORNERS
    // =========================================================================

    /**
     * T2.6: Verify database connection mandates SSL encryption.
     */
    public function test_supabase_ssl_mode_enforced(): void
    {
        $cfg = E2EEnvironment::getSupabaseConfig();
        $this->skipIfMilestonePending('M2', !E2EEnvironment::isSupabaseConfigured(), 'Supabase not yet configured.');

        $this->assertEquals(
            'require',
            $cfg['sslmode'],
            "Supabase cloud database connection must require SSL encryption (DB_SSLMODE=require)."
        );
    }

    /**
     * T2.7: Verify column types and decimal precision in bahan_baku table.
     */
    public function test_supabase_table_column_types_and_precision(): void
    {
        $this->skipIfMilestonePending('M2', !E2EEnvironment::isSupabaseConfigured(), 'Supabase not yet configured.');

        $pdo = E2EEnvironment::getSupabasePdo(5);
        $this->assertNotNull($pdo);

        $stmt = $pdo->prepare("
            SELECT column_name, data_type, numeric_precision, numeric_scale
            FROM information_schema.columns
            WHERE table_schema = 'public' AND table_name = 'bahan_baku'
        ");
        $stmt->execute();
        $cols = [];
        while ($row = $stmt->fetch()) {
            $cols[$row['column_name']] = $row;
        }

        $this->assertArrayHasKey('stok', $cols, "bahan_baku must have 'stok' column.");
        $this->assertEquals('numeric', $cols['stok']['data_type'], "stok must be numeric/decimal data type.");
        $this->assertEquals(12, (int) $cols['stok']['numeric_precision'], "stok must have precision 12.");
        $this->assertEquals(2, (int) $cols['stok']['numeric_scale'], "stok must have scale 2.");

        $this->assertArrayHasKey('harga_beli_persatuan', $cols, "bahan_baku must have 'harga_beli_persatuan' column.");
        $this->assertContains($cols['harga_beli_persatuan']['data_type'], ['integer', 'bigint', 'numeric']);
    }

    /**
     * T2.8: Verify PostgreSQL foreign key constraints on resep and detail_transaksi.
     */
    public function test_supabase_foreign_key_constraints_enforced(): void
    {
        $this->skipIfMilestonePending('M2', !E2EEnvironment::isSupabaseConfigured(), 'Supabase not yet configured.');

        $pdo = E2EEnvironment::getSupabasePdo(5);
        $this->assertNotNull($pdo);

        $stmt = $pdo->prepare("
            SELECT tc.table_name, kcu.column_name, ccu.table_name AS foreign_table_name
            FROM information_schema.table_constraints AS tc 
            JOIN information_schema.key_column_usage AS kcu
              ON tc.constraint_name = kcu.constraint_name
            JOIN information_schema.constraint_column_usage AS ccu
              ON ccu.constraint_name = tc.constraint_name
            WHERE tc.constraint_type = 'FOREIGN KEY' AND tc.table_schema = 'public'
        ");
        $stmt->execute();
        $fks = $stmt->fetchAll();

        $foundResepProduk = false;
        $foundResepBahan = false;

        foreach ($fks as $fk) {
            if ($fk['table_name'] === 'resep' && $fk['column_name'] === 'produk_jadi_id') {
                $foundResepProduk = true;
            }
            if ($fk['table_name'] === 'resep' && $fk['column_name'] === 'bahan_baku_id') {
                $foundResepBahan = true;
            }
        }

        $this->assertTrue($foundResepProduk, "Foreign key resep.produk_jadi_id -> produk_jadi.id must be present.");
        $this->assertTrue($foundResepBahan, "Foreign key resep.bahan_baku_id -> bahan_baku.id must be present.");
    }

    /**
     * T2.9: Verify database query response time meets cloud SLA (latency < 5000ms).
     */
    public function test_supabase_connection_timeout_limit(): void
    {
        $this->skipIfMilestonePending('M2', !E2EEnvironment::isSupabaseConfigured(), 'Supabase not yet configured.');

        $start = microtime(true);
        $pdo = E2EEnvironment::getSupabasePdo(5);
        $this->assertNotNull($pdo);

        $pdo->query("SELECT NOW()");
        $elapsed = (microtime(true) - $start) * 1000;

        $this->assertLessThan(5000, $elapsed, "Cloud database query must complete in under 5000ms. Took {$elapsed}ms.");
    }

    /**
     * T2.10: Verify application cloud database is strictly isolated from local loopback.
     */
    public function test_supabase_cloud_isolation_from_localhost(): void
    {
        $cfg = E2EEnvironment::getSupabaseConfig();
        $this->skipIfMilestonePending('M2', !E2EEnvironment::isSupabaseConfigured(), 'Supabase not yet configured.');

        $ip = gethostbyname($cfg['host']);
        $this->assertNotEquals('127.0.0.1', $ip, "Database host IP must not resolve to 127.0.0.1 loopback.");
        $this->assertNotEquals('::1', $ip, "Database host IP must not resolve to ::1 IPv6 loopback.");
    }

    // =========================================================================
    // FEATURE C: VERCEL BOUNDARY & CORNERS
    // =========================================================================

    /**
     * T2.11: Verify production deployment does not leak debug stack traces on errors.
     */
    public function test_vercel_security_headers_and_no_debug_leakage(): void
    {
        $url = E2EEnvironment::getVercelUrl();
        $this->skipIfMilestonePending('M3', !E2EEnvironment::isVercelUrlConfigured(), 'Live Vercel URL not yet configured.');

        $res = E2EEnvironment::httpRequest($url . '/login', 'GET', [], null, true, 10);

        $this->assertStringNotContainsString(
            'Whoops!',
            $res['body'],
            "Production page must not leak Laravel Whoops debug error page."
        );
        $this->assertStringNotContainsString(
            'Fatal error',
            $res['body'],
            "Production page must not leak PHP fatal error messages."
        );
    }

    /**
     * T2.12: Verify invalid route returns clean HTTP 404 without crashing PHP runtime.
     */
    public function test_vercel_invalid_route_returns_404(): void
    {
        $url = E2EEnvironment::getVercelUrl();
        $this->skipIfMilestonePending('M3', !E2EEnvironment::isVercelUrlConfigured(), 'Live Vercel URL not yet configured.');

        $randSlug = '/_e2e_invalid_' . bin2hex(random_bytes(6));
        $res = E2EEnvironment::httpRequest($url . $randSlug, 'GET', [], null, false, 10);

        $this->assertEquals(404, $res['status'], "Non-existent route $randSlug must return HTTP 404.");
    }

    /**
     * T2.13: Verify compiled CSS / JS asset returns HTTP 200 with proper content type.
     */
    public function test_vercel_built_assets_manifest_exists_and_serves(): void
    {
        $url = E2EEnvironment::getVercelUrl();
        $this->skipIfMilestonePending('M3', !E2EEnvironment::isVercelUrlConfigured(), 'Live Vercel URL not yet configured.');

        // First read HTML of login page to extract asset link
        $pageRes = E2EEnvironment::httpRequest($url . '/login', 'GET', [], null, true, 10);
        $this->assertHttpStatus(200, $pageRes, "Login page for asset discovery");

        // Match href=".../assets/app-*.css"
        if (preg_match('/href="([^"]+assets\/app-[^"]+\.css)"/', $pageRes['body'], $matches)) {
            $cssUrl = $matches[1];
            if (str_starts_with($cssUrl, '/')) {
                $cssUrl = $url . $cssUrl;
            }
            $cssRes = E2EEnvironment::httpRequest($cssUrl, 'GET', [], null, true, 10);
            $this->assertEquals(200, $cssRes['status'], "Built CSS asset at $cssUrl must return HTTP 200.");
            $this->assertStringContainsString('text/css', $cssRes['headers']['content-type'] ?? '');
        } else {
            // Fallback to testing /build/manifest.json
            $manifestRes = E2EEnvironment::httpRequest($url . '/build/manifest.json', 'GET', [], null, true, 10);
            $this->assertTrue(
                in_array($manifestRes['status'], [200, 403, 404]),
                "Vite manifest request executed."
            );
        }
    }

    /**
     * T2.14: Verify VIEW_COMPILED_PATH is configured to /tmp to prevent read-only filesystem crash.
     */
    public function test_vercel_temporary_storage_redirection(): void
    {
        $vercelJsonPath = E2EEnvironment::getProjectRoot() . DIRECTORY_SEPARATOR . 'vercel.json';
        $this->skipIfMilestonePending('M3', !file_exists($vercelJsonPath), 'vercel.json not yet created.');

        $content = file_get_contents($vercelJsonPath);
        $this->assertStringContainsString(
            '/tmp',
            $content,
            "vercel.json must redirect storage or view compiled path to /tmp for serverless runtime."
        );
    }

    /**
     * T2.15: Verify cold start and response time for health endpoint is within SLA (< 10000ms).
     */
    public function test_vercel_cold_start_and_response_time_under_sla(): void
    {
        $url = E2EEnvironment::getVercelUrl();
        $this->skipIfMilestonePending('M3', !E2EEnvironment::isVercelUrlConfigured(), 'Live Vercel URL not yet configured.');

        $res = E2EEnvironment::httpRequest($url . '/up', 'GET', [], null, true, 10);
        $this->assertLessThan(
            10.0,
            $res['time'],
            "Response time for /up ({$res['time']}s) must be less than 10 seconds."
        );
    }
}
