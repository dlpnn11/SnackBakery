<?php

namespace Tests\E2E;

use Tests\E2E\Support\E2EEnvironment;

/**
 * Tier 4: Real-World Workloads & End-to-End User Flow Scenarios
 *
 * Simulates complete user journeys, client-side Livewire reactivity contracts,
 * health monitoring checks, atomic cloud database transaction lifecycles,
 * and complete cloud server independence from local services.
 */
class Tier4RealWorldScenarioTest extends E2ETestCase
{
    /**
     * T4.1: Scenario 1 - Unauthenticated User Landing & Authentication Presentation
     *
     * Flow: User accesses root URL / -> redirects to /login -> renders complete
     * authentication view with CSRF tokens, email/password inputs, and styling.
     */
    public function test_scenario_unauthenticated_user_landing_journey(): void
    {
        $url = E2EEnvironment::getVercelUrl();
        $this->skipIfMilestonePending('M3', !E2EEnvironment::isVercelUrlConfigured(), 'Live Vercel URL not yet configured.');

        // Step 1: Follow redirects from root URL
        $res = E2EEnvironment::httpRequest($url . '/', 'GET', [], null, true, 15);

        $this->assertHttpStatus(200, $res, "User landing route after redirection");

        $html = $res['body'];

        // Step 2: Verify CSRF token presence
        $this->assertMatchesRegularExpression(
            '/<meta\s+name="csrf-token"\s+content="[^"]+"/i',
            $html,
            "Login view must provide a valid CSRF meta token."
        );

        // Step 3: Verify email and password input elements
        $this->assertMatchesRegularExpression(
            '/<input[^>]+type="email"[^>]*>/i',
            $html,
            "Login view must render an email input field."
        );

        $this->assertMatchesRegularExpression(
            '/<input[^>]+type="password"[^>]*>/i',
            $html,
            "Login view must render a password input field."
        );

        // Step 4: Verify Livewire assets included
        $this->assertStringContainsString(
            'livewire.js',
            $html,
            "Login view should include Livewire script tag."
        );
    }

    /**
     * T4.2: Scenario 2 - Livewire Client-Side Script & Reactivity Handshake
     *
     * Flow: Frontend discovers Livewire script link in DOM and executes fetch;
     * runtime delivers complete reactive client bundle.
     */
    public function test_scenario_livewire_asset_and_runtime_handshake(): void
    {
        $url = E2EEnvironment::getVercelUrl();
        $this->skipIfMilestonePending('M3', !E2EEnvironment::isVercelUrlConfigured(), 'Live Vercel URL not yet configured.');

        // Fetch login page
        $pageRes = E2EEnvironment::httpRequest($url . '/login', 'GET', [], null, true, 15);
        $this->assertHttpStatus(200, $pageRes, "Login page for Livewire script discovery");

        // Parse <script ... src="/livewire/livewire.js?...">
        $foundScriptUrl = null;
        if (preg_match('/src="([^"]*livewire\/livewire\.js[^"]*)"/', $pageRes['body'], $matches)) {
            $foundScriptUrl = $matches[1];
            if (str_starts_with($foundScriptUrl, '/')) {
                $foundScriptUrl = $url . $foundScriptUrl;
            }
        } else {
            $foundScriptUrl = $url . '/livewire/livewire.js';
        }

        // Fetch the script
        $scriptRes = E2EEnvironment::httpRequest($foundScriptUrl, 'GET', [], null, true, 15);

        $this->assertHttpStatus(200, $scriptRes, "Livewire script fetch at $foundScriptUrl");
        $this->assertStringContainsString(
            'Livewire',
            $scriptRes['body'],
            "Livewire script bundle must contain Livewire core definition."
        );
    }

    /**
     * T4.3: Scenario 3 - Health Check & Uptime Monitoring Contract
     *
     * Flow: Automated monitoring probe issues GET to /up; expects HTTP 200 OK
     * with minimal latency and no exceptions.
     */
    public function test_scenario_health_check_endpoint(): void
    {
        $url = E2EEnvironment::getVercelUrl();
        $this->skipIfMilestonePending('M3', !E2EEnvironment::isVercelUrlConfigured(), 'Live Vercel URL not yet configured.');

        $start = microtime(true);
        $res = E2EEnvironment::httpRequest($url . '/up', 'GET', [], null, false, 10);
        $duration = microtime(true) - $start;

        $this->assertHttpStatus(200, $res, "Uptime health check /up");
        $this->assertLessThan(
            5.0,
            $duration,
            "Health check response must complete within 5 seconds. Took {$duration}s."
        );
    }

    /**
     * T4.4: Scenario 4 - Cloud Database Operational Transaction Lifecycle
     *
     * Flow: Direct test against Supabase PostgreSQL:
     * - Begin atomic transaction
     * - Insert test raw material (bahan_baku)
     * - Query row back and verify field types and precision
     * - Update test row
     * - Verify update
     * - Rollback / Delete and verify zero data contamination
     */
    public function test_scenario_database_operational_read_write_cycle(): void
    {
        $this->skipIfMilestonePending('M2', !E2EEnvironment::isSupabaseConfigured(), 'Supabase not yet configured.');

        $pdo = E2EEnvironment::getSupabasePdo(5);
        $this->assertNotNull($pdo, "Supabase connection required for lifecycle test.");

        $testTag = 'E2E_TEST_' . bin2hex(random_bytes(4));
        $testDate = date('Y-m-d', strtotime('+30 days'));

        $pdo->beginTransaction();

        try {
            // 1. Insert test record
            $insertStmt = $pdo->prepare("
                INSERT INTO bahan_baku (nama_bahan, satuan, harga_beli_persatuan, stok, minimum_stok, tanggal_expired, created_at, updated_at)
                VALUES (:nama, :satuan, :harga, :stok, :min_stok, :expired, NOW(), NOW())
                RETURNING id
            ");
            $insertStmt->execute([
                'nama'     => $testTag,
                'satuan'   => 'kg',
                'harga'    => 15000,
                'stok'     => 50.25,
                'min_stok' => 10,
                'expired'  => $testDate,
            ]);
            $insertedId = (int) $insertStmt->fetchColumn();

            $this->assertGreaterThan(0, $insertedId, "Inserted test record should yield a positive ID.");

            // 2. Query back
            $selectStmt = $pdo->prepare("SELECT * FROM bahan_baku WHERE id = :id");
            $selectStmt->execute(['id' => $insertedId]);
            $record = $selectStmt->fetch();

            $this->assertNotEmpty($record, "Inserted record must be queryable.");
            $this->assertEquals($testTag, $record['nama_bahan']);
            $this->assertEquals('50.25', (string) $record['stok']);
            $this->assertEquals(15000, (int) $record['harga_beli_persatuan']);

            // 3. Update record
            $updateStmt = $pdo->prepare("UPDATE bahan_baku SET stok = :new_stok WHERE id = :id");
            $updateStmt->execute([
                'new_stok' => 75.50,
                'id'       => $insertedId,
            ]);

            // 4. Verify update
            $verifyStmt = $pdo->prepare("SELECT stok FROM bahan_baku WHERE id = :id");
            $verifyStmt->execute(['id' => $insertedId]);
            $updatedStok = $verifyStmt->fetchColumn();

            $this->assertEquals('75.50', (string) $updatedStok, "Stok must be updated accurately.");

            // 5. Clean up via rollback to ensure zero residue
            $pdo->rollBack();

            // 6. Verify clean rollback
            $cleanCheck = $pdo->prepare("SELECT COUNT(*) FROM bahan_baku WHERE nama_bahan = :nama");
            $cleanCheck->execute(['nama' => $testTag]);
            $this->assertEquals(0, (int) $cleanCheck->fetchColumn(), "Test record must be completely cleaned up.");
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * T4.5: Scenario 5 - Complete Cloud Independence from Local Services
     *
     * Flow: Verify application operates completely without local services:
     * - DB Host resolves to external IP (not 127.0.0.1)
     * - Vercel URL resolves to public edge CDN (not localhost)
     * - Local MySQL port 3306 is not required for application operation
     */
    public function test_scenario_full_end_to_end_cloud_independence(): void
    {
        // 1. Verify DB is remote
        $cfg = E2EEnvironment::getSupabaseConfig();
        $this->skipIfMilestonePending('M2', !E2EEnvironment::isSupabaseConfigured(), 'Supabase not yet configured.');

        $this->assertNotEmpty($cfg['host']);
        $dbIp = gethostbyname($cfg['host']);
        $this->assertNotEquals('127.0.0.1', $dbIp, "Database IP must be a remote cloud host.");
        $this->assertNotEquals('::1', $dbIp, "Database IP must not be IPv6 loopback.");

        // 2. Verify Vercel URL is remote
        $url = E2EEnvironment::getVercelUrl();
        $this->skipIfMilestonePending('M3', !E2EEnvironment::isVercelUrlConfigured(), 'Live Vercel URL not yet configured.');

        $parsed = parse_url($url);
        $host = $parsed['host'] ?? '';
        $this->assertNotEmpty($host);
        $webIp = gethostbyname($host);
        $this->assertNotEquals('127.0.0.1', $webIp, "Web hosting IP must be a remote cloud CDN/edge.");
        $this->assertNotEquals('localhost', $host, "Web host must not be localhost.");
    }
}
