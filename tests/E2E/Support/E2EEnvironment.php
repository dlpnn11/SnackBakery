<?php

namespace Tests\E2E\Support;

/**
 * E2E Environment & Opaque-Box Client Helper for SnackBakery.
 *
 * Provides isolated utility methods to test GitHub, Supabase PostgreSQL,
 * and Vercel production endpoints without depending on internal Laravel application state.
 */
class E2EEnvironment
{
    private static ?array $envCache = null;
    private static ?\PDO $pdoCache = null;

    public static function getProjectRoot(): string
    {
        return realpath(__DIR__ . '/../../../') ?: dirname(__DIR__, 3);
    }

    /**
     * Parse the .env file into key-value pairs without overriding process env.
     */
    public static function loadEnv(): array
    {
        if (self::$envCache !== null) {
            return self::$envCache;
        }

        self::$envCache = [];
        $envPath = self::getProjectRoot() . DIRECTORY_SEPARATOR . '.env';

        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line) || str_starts_with($line, '#')) {
                    continue;
                }
                $parts = explode('=', $line, 2);
                if (count($parts) === 2) {
                    $key = trim($parts[0]);
                    $value = trim($parts[1]);
                    // Strip optional quotes
                    if ((str_starts_with($value, '"') && str_ends_with($value, '"')) ||
                        (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
                        $value = substr($value, 1, -1);
                    }
                    self::$envCache[$key] = $value;
                }
            }
        }

        return self::$envCache;
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $sysVal = getenv($key);
        if ($sysVal !== false && $sysVal !== '') {
            return $sysVal;
        }

        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return (string) $_ENV[$key];
        }

        $parsed = self::loadEnv();
        if (isset($parsed[$key]) && $parsed[$key] !== '') {
            return (string) $parsed[$key];
        }

        return $default;
    }

    // -------------------------------------------------------------
    // Git & GitHub Helpers
    // -------------------------------------------------------------

    public static function getGitRemoteUrl(string $remote = 'origin'): ?string
    {
        $cmd = "git remote get-url " . escapeshellarg($remote) . " 2>&1";
        $output = @shell_exec($cmd);
        if ($output === null) {
            return null;
        }
        $url = trim($output);
        return (!empty($url) && !str_contains($url, 'fatal:')) ? $url : null;
    }

    public static function getGitBranch(): string
    {
        $cmd = "git rev-parse --abbrev-ref HEAD 2>&1";
        $output = @shell_exec($cmd);
        return trim($output ?? 'unknown');
    }

    public static function getGitHeadCommit(): string
    {
        $cmd = "git rev-parse HEAD 2>&1";
        $output = @shell_exec($cmd);
        return trim($output ?? '');
    }

    public static function isGitTreeClean(): bool
    {
        $cmd = "git status --porcelain 2>&1";
        $output = @shell_exec($cmd);
        return empty(trim($output ?? ''));
    }

    public static function scanConflictMarkers(string $dir = ''): array
    {
        if (empty($dir)) {
            $dir = self::getProjectRoot();
        }

        $cmd = 'git grep -n -E "^<<<<<<< " 2>&1';
        $output = @shell_exec($cmd);
        $matches = [];
        if ($output) {
            $lines = explode("\n", trim($output));
            foreach ($lines as $line) {
                if (!empty($line) && !str_starts_with($line, 'fatal:')) {
                    $matches[] = $line;
                }
            }
        }
        return $matches;
    }

    // -------------------------------------------------------------
    // Supabase PostgreSQL Helpers
    // -------------------------------------------------------------

    public static function getSupabaseConfig(): array
    {
        return [
            'driver'   => self::get('DB_CONNECTION', 'pgsql'),
            'host'     => self::get('DB_HOST', ''),
            'port'     => (int) self::get('DB_PORT', '5432'),
            'database' => self::get('DB_DATABASE', 'postgres'),
            'username' => self::get('DB_USERNAME', 'postgres'),
            'password' => self::get('DB_PASSWORD', ''),
            'sslmode'  => self::get('DB_SSLMODE', 'require'),
            'url'      => self::get('DB_URL', null),
        ];
    }

    public static function isSupabaseConfigured(): bool
    {
        $cfg = self::getSupabaseConfig();
        return $cfg['driver'] === 'pgsql' && !empty($cfg['host']) && !in_array($cfg['host'], ['127.0.0.1', 'localhost', '::1']);
    }

    public static function getSupabasePdo(int $timeoutSeconds = 5): ?\PDO
    {
        if (self::$pdoCache !== null) {
            return self::$pdoCache;
        }

        $cfg = self::getSupabaseConfig();
        if (!self::isSupabaseConfigured()) {
            return null;
        }

        $dsn = sprintf(
            'pgsql:host=%s;port=%d;dbname=%s;sslmode=%s',
            $cfg['host'],
            $cfg['port'],
            $cfg['database'],
            $cfg['sslmode']
        );

        $options = [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_TIMEOUT            => $timeoutSeconds,
        ];

        try {
            self::$pdoCache = new \PDO($dsn, $cfg['username'], $cfg['password'], $options);
            return self::$pdoCache;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public static function resetPdoCache(): void
    {
        self::$pdoCache = null;
    }

    // -------------------------------------------------------------
    // Vercel & HTTP Helpers
    // -------------------------------------------------------------

    public static function getVercelUrl(): string
    {
        $url = self::get('VERCEL_URL');
        if (empty($url)) {
            $url = self::get('APP_URL', 'http://localhost');
        }

        if (!empty($url) && !str_starts_with($url, 'http://') && !str_starts_with($url, 'https://')) {
            $url = 'https://' . $url;
        }

        return rtrim($url, '/');
    }

    public static function isVercelUrlConfigured(): bool
    {
        $url = self::getVercelUrl();
        return !empty($url) && !str_contains($url, 'localhost') && !str_contains($url, '127.0.0.1');
    }

    /**
     * Execute an HTTP request with curl.
     */
    public static function httpRequest(
        string $url,
        string $method = 'GET',
        array $headers = [],
        ?string $body = null,
        bool $followRedirects = true,
        int $timeout = 10
    ): array {
        if (!extension_loaded('curl')) {
            throw new \RuntimeException("cURL PHP extension is required for E2E tests.");
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, min(3, $timeout));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_USERAGENT, 'SnackBakery-E2E-TestHarness/1.0');

        if ($followRedirects) {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 5);
        } else {
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        }

        $headerList = [];
        foreach ($headers as $k => $v) {
            $headerList[] = is_numeric($k) ? $v : "$k: $v";
        }
        if (!empty($headerList)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerList);
        }

        if (strtoupper($method) === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }
        } elseif (strtoupper($method) === 'HEAD') {
            curl_setopt($ch, CURLOPT_NOBODY, true);
        } elseif (strtoupper($method) !== 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
            if ($body !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            }
        }

        $rawResponse = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $totalTime = curl_getinfo($ch, CURLINFO_TOTAL_TIME);
        $primaryIp = curl_getinfo($ch, CURLINFO_PRIMARY_IP) ?: '';
        $effectiveUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL) ?: $url;
        curl_close($ch);

        $responseHeaders = [];
        $responseBody = '';

        if ($rawResponse !== false && $rawResponse !== null) {
            $rawHeaders = substr($rawResponse, 0, $headerSize);
            $responseBody = substr($rawResponse, $headerSize);

            // Parse headers
            $headerLines = explode("\r\n", $rawHeaders);
            foreach ($headerLines as $line) {
                if (str_contains($line, ':')) {
                    [$hk, $hv] = explode(':', $line, 2);
                    $responseHeaders[strtolower(trim($hk))] = trim($hv);
                }
            }
        }

        return [
            'status'        => (int) $statusCode,
            'body'          => (string) $responseBody,
            'headers'       => $responseHeaders,
            'time'          => (float) $totalTime,
            'ip'            => (string) $primaryIp,
            'effective_url' => (string) $effectiveUrl,
            'errno'         => $errno,
            'error'         => $error,
        ];
    }
}
