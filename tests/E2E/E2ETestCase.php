<?php

namespace Tests\E2E;

use PHPUnit\Framework\TestCase;
use Tests\E2E\Support\E2EEnvironment;

/**
 * Base TestCase for SnackBakery E2E Tests.
 *
 * Provides specialized assertion methods and opaque-box validation logic.
 */
abstract class E2ETestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        E2EEnvironment::loadEnv();
    }

    /**
     * Skip test if a milestone is pending and progressive mode is enabled.
     */
    protected function skipIfMilestonePending(string $milestoneName, bool $condition, string $reason): void
    {
        if ($condition && (getenv('E2E_PROGRESSIVE') === '1' || getenv('E2E_PROGRESSIVE') === 'true')) {
            $this->markTestSkipped("[$milestoneName PENDING] $reason");
        }
    }

    /**
     * Assert that git remote origin is configured to expected GitHub URL.
     */
    protected function assertGitRemoteConfigured(string $expectedSubstr = 'dlpnn11/SnackBakery'): void
    {
        $remoteUrl = E2EEnvironment::getGitRemoteUrl();
        $this->assertNotNull($remoteUrl, "Git remote 'origin' must be configured.");
        $this->assertStringContainsString(
            $expectedSubstr,
            $remoteUrl,
            "Git remote 'origin' URL ($remoteUrl) must point to repository containing '$expectedSubstr'."
        );
    }

    /**
     * Assert that HTTP request returned expected status code.
     */
    protected function assertHttpStatus(int $expectedStatus, array $response, string $context = ''): void
    {
        $this->assertEquals(
            $expectedStatus,
            $response['status'],
            "Expected HTTP status $expectedStatus from $context (Effective URL: {$response['effective_url']}), got {$response['status']}. Error: {$response['error']}"
        );
    }
}
