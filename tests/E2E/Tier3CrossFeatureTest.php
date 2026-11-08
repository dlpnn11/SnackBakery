<?php

namespace Tests\E2E;

use Tests\E2E\Support\E2EEnvironment;

/**
 * Tier 3: Cross-Feature Integration Tests
 *
 * Validates cross-cutting behavior across GitHub, Supabase PostgreSQL,
 * and Vercel serverless deployment.
 */
class Tier3CrossFeatureTest extends E2ETestCase
{
    /**
     * T3.1: Verify GitHub repository integration contract with Vercel deployment.
     */
    public function test_cross_git_commit_matches_vercel_deployment(): void
    {
        $remote = E2EEnvironment::getGitRemoteUrl('origin');
        $this->skipIfMilestonePending('M1', empty($remote) || str_contains($remote, 'mini-erp'), 'GitHub origin not yet pointing to SnackBakery.');

        $this->assertStringContainsString('SnackBakery', $remote, "Origin remote must be SnackBakery.");

        // If local vercel configuration exists, verify link
        $vercelConfigPath = E2EEnvironment::getProjectRoot() . '/.vercel/project.json';
        if (file_exists($vercelConfigPath)) {
            $data = json_decode(file_get_contents($vercelConfigPath), true);
            $this->assertNotEmpty($data['projectId'] ?? '', "Vercel projectId should be present when linked.");
        }
    }

    /**
     * T3.2: Verify live Vercel application communicates with Supabase cloud database.
     */
    public function test_cross_vercel_app_queries_supabase_without_local_services(): void
    {
        $url = E2EEnvironment::getVercelUrl();
        $this->skipIfMilestonePending('M3', !E2EEnvironment::isVercelUrlConfigured(), 'Live Vercel URL not yet configured.');

        $res = E2EEnvironment::httpRequest($url . '/login', 'GET', [], null, true, 15);

        $this->assertHttpStatus(200, $res, "Live Vercel /login route");
        $this->assertStringNotContainsString(
            'SQLSTATE',
            $res['body'],
            "Response should not contain SQL connection error."
        );
        $this->assertStringNotContainsString(
            'Connection refused',
            $res['body'],
            "Response should not indicate database connection refused."
        );
    }

    /**
     * T3.3: Verify session persistence mechanism operates in the cloud.
     */
    public function test_cross_session_storage_in_cloud_db_or_cookie(): void
    {
        $url = E2EEnvironment::getVercelUrl();
        $this->skipIfMilestonePending('M3', !E2EEnvironment::isVercelUrlConfigured(), 'Live Vercel URL not yet configured.');

        $res = E2EEnvironment::httpRequest($url . '/login', 'GET', [], null, false, 10);

        $this->assertArrayHasKey(
            'set-cookie',
            $res['headers'],
            "Live serverless response must return a session cookie (Set-Cookie header)."
        );
        $this->assertMatchesRegularExpression(
            '/(_session|laravel_session|XSRF-TOKEN)/i',
            $res['headers']['set-cookie'],
            "Session or CSRF cookie must be issued."
        );
    }

    /**
     * T3.4: Verify parity between local migration files and Supabase migrations table.
     */
    public function test_cross_migrations_parity_between_codebase_and_cloud_db(): void
    {
        $this->skipIfMilestonePending('M2', !E2EEnvironment::isSupabaseConfigured(), 'Supabase not yet configured.');

        $pdo = E2EEnvironment::getSupabasePdo(5);
        $this->assertNotNull($pdo);

        // Read local migration directory
        $migDir = E2EEnvironment::getProjectRoot() . '/database/migrations';
        $files = glob($migDir . '/*.php');
        $localMigrations = [];
        foreach ($files as $file) {
            $localMigrations[] = basename($file, '.php');
        }
        sort($localMigrations);

        // Read remote migrations table
        $stmt = $pdo->query("SELECT migration FROM migrations ORDER BY migration");
        $remoteMigrations = $stmt->fetchAll(\PDO::FETCH_COLUMN);
        sort($remoteMigrations);

        $this->assertEquals(
            $localMigrations,
            $remoteMigrations,
            "Local migration files must match recorded migrations in Supabase cloud database 1:1."
        );
    }

    /**
     * T3.5: Verify .env.example contains all required cloud configuration keys.
     */
    public function test_cross_env_example_matches_cloud_requirements(): void
    {
        $examplePath = E2EEnvironment::getProjectRoot() . '/.env.example';
        $this->assertFileExists($examplePath);

        $content = file_get_contents($examplePath);
        $requiredKeys = [
            'APP_KEY',
            'APP_URL',
            'DB_CONNECTION',
            'DB_HOST',
            'DB_PORT',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_PASSWORD',
        ];

        foreach ($requiredKeys as $key) {
            $this->assertStringContainsString(
                $key . '=',
                $content,
                ".env.example must document the '$key' configuration variable."
            );
        }
    }
}
