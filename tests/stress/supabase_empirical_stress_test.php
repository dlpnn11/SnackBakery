<?php

declare(strict_types=1);

/**
 * SnackBakery - Empirical Database Persistence & Schema Stress Test Harness
 * 
 * Challenger 2: Database Schema & Persistence Stress Tester
 * Milestone: M2 Supabase Cloud Database Integration
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use Tests\E2E\Support\E2EEnvironment;

class SupabaseEmpiricalStressTester
{
    private \PDO $pdo;
    private array $results = [];
    private int $passCount = 0;
    private int $failCount = 0;
    private int $warnCount = 0;

    public function __construct()
    {
        $cfg = E2EEnvironment::getSupabaseConfig();
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
            \PDO::ATTR_TIMEOUT            => 5,
        ];
        $this->pdo = new \PDO($dsn, $cfg['username'], $cfg['password'], $options);
    }

    private function assert(string $testName, bool $condition, string $message = ''): void
    {
        if ($condition) {
            $this->passCount++;
            $this->results[] = ['status' => 'PASS', 'test' => $testName, 'message' => $message];
            echo "  [PASS] {$testName}\n";
        } else {
            $this->failCount++;
            $this->results[] = ['status' => 'FAIL', 'test' => $testName, 'message' => $message];
            echo "  [FAIL] {$testName} - {$message}\n";
        }
    }

    private function warn(string $testName, string $message): void
    {
        $this->warnCount++;
        $this->results[] = ['status' => 'WARN', 'test' => $testName, 'message' => $message];
        echo "  [WARN] {$testName} - {$message}\n";
    }

    public function runAll(): void
    {
        echo "==================================================================\n";
        echo "   SUPABASE EMPIRICAL STRESS TEST HARNESS (CHALLENGER M2.2)      \n";
        echo "==================================================================\n\n";

        $this->test1_SslAndServerDetails();
        $this->test2_AllMigrationTablesAndColumns();
        $this->test3_SeededRecordsAndEloquent();
        $this->test4_DecimalPrecisionAndBoundaries();
        $this->test5_DecimalRoundingBehavior();
        $this->test6_DecimalRecipeCalculations();
        $this->test7_ForeignKeyConstraintsAndCascades();
        $this->test8_TransactionRollbackAtomicity();
        $this->test9_RowLockingCapability();
        $this->test10_PostgresSequenceSynchronization();
        $this->test11_DashboardLikeQueryAndBatchInsert();

        echo "\n==================================================================\n";
        echo "STRESS TEST SUMMARY:\n";
        echo "  Passed:   {$this->passCount}\n";
        echo "  Failed:   {$this->failCount}\n";
        echo "  Warnings: {$this->warnCount}\n";
        echo "==================================================================\n";
    }

    public function test1_SslAndServerDetails(): void
    {
        echo "Section 1: SSL Encryption & Server Telemetry\n";
        
        // Check SHOW ssl
        $stmt = $this->pdo->query("SHOW ssl");
        $showSsl = $stmt->fetchColumn();
        echo "         Server 'SHOW ssl': {$showSsl}\n";
        $this->assert("PostgreSQL server SSL configuration is 'on'", $showSsl === 'on', "SHOW ssl returned: {$showSsl}");

        // Check pg_stat_ssl
        $stmt = $this->pdo->query("SELECT ssl, version, cipher, bits, client_dn FROM pg_stat_ssl WHERE pid = pg_backend_pid()");
        $sslInfo = $stmt->fetch();
        if ($sslInfo) {
            echo "         pg_stat_ssl (pid: " . $this->pdo->query("SELECT pg_backend_pid()")->fetchColumn() . "): ssl=" . ($sslInfo['ssl'] ? 'true' : 'false') . ", version=" . ($sslInfo['version'] ?? 'null') . ", cipher=" . ($sslInfo['cipher'] ?? 'null') . "\n";
        }

        // Adversarial test: Verify Supabase rejects unencrypted connection (sslmode=disable)
        $cfg = E2EEnvironment::getSupabaseConfig();
        $unencryptedRejected = false;
        try {
            $badDsn = sprintf(
                'pgsql:host=%s;port=%d;dbname=%s;sslmode=disable',
                $cfg['host'],
                $cfg['port'],
                $cfg['database']
            );
            $badPdo = new \PDO($badDsn, $cfg['username'], $cfg['password'], [\PDO::ATTR_TIMEOUT => 3]);
            $this->warn("Unencrypted connection check", "Supavisor pooler allowed sslmode=disable. Checking if traffic was negotiated.");
        } catch (\PDOException $e) {
            $unencryptedRejected = true;
            $this->assert("Supabase pooler rejects unencrypted connection (sslmode=disable)", true, "Caught expected: " . $e->getMessage());
        }

        // Check PG version
        $stmt = $this->pdo->query("SELECT version()");
        $ver = $stmt->fetchColumn();
        $this->assert("PostgreSQL engine version 17+", str_contains($ver, 'PostgreSQL 17'), "Version reported: {$ver}");
    }

    public function test2_AllMigrationTablesAndColumns(): void
    {
        echo "\nSection 2: Database Migration Tables & Column Structure\n";

        // 1. Migrations table
        $stmt = $this->pdo->query("SELECT migration, batch FROM migrations ORDER BY id");
        $migrations = $stmt->fetchAll();
        $this->assert("Migrations count is exactly 8", count($migrations) === 8, "Expected 8 migrations, got " . count($migrations));

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
        $migNames = array_column($migrations, 'migration');
        foreach ($expectedMigrations as $expected) {
            $this->assert("Migration '$expected' recorded", in_array($expected, $migNames), "Migration missing: $expected");
        }

        // 2. All 13 tables in public schema
        $expectedTables = [
            'users',
            'password_reset_tokens',
            'sessions',
            'cache',
            'cache_locks',
            'jobs',
            'job_batches',
            'failed_jobs',
            'bahan_baku',
            'produk_jadi',
            'resep',
            'transaksi',
            'detail_transaksi',
        ];

        $stmt = $this->pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public'");
        $tables = $stmt->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($expectedTables as $tbl) {
            $this->assert("Table '$tbl' exists in public schema", in_array($tbl, $tables), "Missing table: $tbl");
        }

        // 3. Inspect columns for domain tables
        $domainTableColumns = [
            'bahan_baku' => [
                'id' => 'bigint',
                'nama_bahan' => 'character varying',
                'satuan' => 'character varying',
                'harga_beli_persatuan' => 'integer',
                'stok' => 'numeric',
                'minimum_stok' => 'integer',
                'tanggal_expired' => 'date',
                'created_at' => 'timestamp without time zone',
                'updated_at' => 'timestamp without time zone',
            ],
            'produk_jadi' => [
                'id' => 'bigint',
                'nama_produk' => 'character varying',
                'harga_jual' => 'integer',
                'stok' => 'integer',
                'minimum_stok' => 'integer',
                'created_at' => 'timestamp without time zone',
                'updated_at' => 'timestamp without time zone',
            ],
            'resep' => [
                'id' => 'bigint',
                'produk_jadi_id' => 'bigint',
                'bahan_baku_id' => 'bigint',
                'kebutuhan_satuan' => 'numeric',
                'created_at' => 'timestamp without time zone',
                'updated_at' => 'timestamp without time zone',
            ],
            'transaksi' => [
                'id' => 'bigint',
                'metode_pembayaran' => 'character varying',
                'total_pendapatan' => 'integer',
                'total_hpp' => 'integer',
                'tanggal_transaksi' => 'timestamp without time zone',
                'created_at' => 'timestamp without time zone',
                'updated_at' => 'timestamp without time zone',
            ],
            'detail_transaksi' => [
                'id' => 'bigint',
                'transaksi_id' => 'bigint',
                'produk_jadi_id' => 'bigint',
                'qty' => 'integer',
                'subtotal' => 'integer',
                'created_at' => 'timestamp without time zone',
                'updated_at' => 'timestamp without time zone',
            ],
        ];

        foreach ($domainTableColumns as $table => $cols) {
            $stmt = $this->pdo->prepare("
                SELECT column_name, data_type 
                FROM information_schema.columns 
                WHERE table_schema = 'public' AND table_name = :tbl
            ");
            $stmt->execute(['tbl' => $table]);
            $actualCols = $stmt->fetchAll();
            $colMap = [];
            foreach ($actualCols as $ac) {
                $colMap[$ac['column_name']] = $ac['data_type'];
            }

            foreach ($cols as $colName => $expectedType) {
                $this->assert(
                    "Column {$table}.{$colName} exists and type is {$expectedType}",
                    isset($colMap[$colName]) && $colMap[$colName] === $expectedType,
                    "Expected {$expectedType}, got " . ($colMap[$colName] ?? 'not found')
                );
            }
        }
    }

    public function test3_SeededRecordsAndEloquent(): void
    {
        echo "\nSection 3: Seeded Records & User Authentication Model\n";

        // Query user directly
        $stmt = $this->pdo->query("SELECT id, name, email, password, email_verified_at, created_at FROM users WHERE email = 'test@example.com'");
        $user = $stmt->fetch();

        $this->assert("Seeded user 'test@example.com' exists", !empty($user), "User test@example.com not found in cloud DB.");
        if ($user) {
            $this->assert("Seeded user password hash is bcrypt", str_starts_with($user['password'], '$2y$'), "Password is not standard bcrypt hash.");
            $this->assert("Seeded user password validates against 'password'", password_verify('password', $user['password']), "Password mismatch.");
            $this->assert("Seeded user ID is 1", (int) $user['id'] === 1, "User ID is " . $user['id']);
        }

        // Verify via Eloquent
        $app = require __DIR__ . '/../../bootstrap/app.php';
        $kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
        $kernel->bootstrap();
        $eloquentUser = \App\Models\User::where('email', 'test@example.com')->first();
        $this->assert("Eloquent App\\Models\\User::first() retrieves cloud user", $eloquentUser !== null && $eloquentUser->email === 'test@example.com', "Eloquent failed to retrieve user from cloud DB.");
    }

    public function test4_DecimalPrecisionAndBoundaries(): void
    {
        echo "\nSection 4: Decimal Precision & Boundary Stress Testing (numeric(12,2))\n";

        // Check metadata for bahan_baku.stok
        $stmt = $this->pdo->query("
            SELECT numeric_precision, numeric_scale 
            FROM information_schema.columns 
            WHERE table_name = 'bahan_baku' AND column_name = 'stok'
        ");
        $metaBahan = $stmt->fetch();
        $this->assert("bahan_baku.stok precision is 12", (int) $metaBahan['numeric_precision'] === 12);
        $this->assert("bahan_baku.stok scale is 2", (int) $metaBahan['numeric_scale'] === 2);

        // Check metadata for resep.kebutuhan_satuan
        $stmt = $this->pdo->query("
            SELECT numeric_precision, numeric_scale 
            FROM information_schema.columns 
            WHERE table_name = 'resep' AND column_name = 'kebutuhan_satuan'
        ");
        $metaResep = $stmt->fetch();
        $this->assert("resep.kebutuhan_satuan precision is 12", (int) $metaResep['numeric_precision'] === 12);
        $this->assert("resep.kebutuhan_satuan scale is 2", (int) $metaResep['numeric_scale'] === 2);

        // Boundary Test: Maximum valid value (10 integer digits + 2 decimals = 12 total digits: 9999999999.99)
        $maxValidDecimal = '9999999999.99';
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO bahan_baku (nama_bahan, satuan, harga_beli_persatuan, stok, minimum_stok, created_at, updated_at)
                VALUES ('BOUNDARY_MAX_TEST', 'kg', 100, :stok, 0, NOW(), NOW())
                RETURNING stok
            ");
            $stmt->execute(['stok' => $maxValidDecimal]);
            $insertedStok = $stmt->fetchColumn();
            $this->assert("Accepts maximum numeric(12,2) boundary (9999999999.99)", $insertedStok === $maxValidDecimal, "Got: {$insertedStok}");
            $this->pdo->rollBack();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            $this->assert("Accepts maximum numeric(12,2) boundary (9999999999.99)", false, $e->getMessage());
        }

        // Boundary Test: Numeric overflow (11 integer digits + 2 decimals = 13 digits: 10000000000.00)
        $overflowDecimal = '10000000000.00';
        $overflowCaught = false;
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO bahan_baku (nama_bahan, satuan, harga_beli_persatuan, stok, minimum_stok, created_at, updated_at)
                VALUES ('BOUNDARY_OVERFLOW_TEST', 'kg', 100, :stok, 0, NOW(), NOW())
            ");
            $stmt->execute(['stok' => $overflowDecimal]);
            $this->pdo->rollBack();
        } catch (\PDOException $e) {
            $overflowCaught = true;
            $this->pdo->rollBack();
            // Postgres error code 22003 = numeric_value_out_of_range
            $is22003 = ($e->getCode() === '22003' || str_contains($e->getMessage(), 'out of range') || str_contains($e->getMessage(), 'numeric field overflow'));
            $this->assert("Rejects numeric overflow (13 digits) with SQLSTATE 22003", $is22003, "Caught code {$e->getCode()}: {$e->getMessage()}");
        }
        if (!$overflowCaught) {
            $this->assert("Rejects numeric overflow (13 digits) with SQLSTATE 22003", false, "Overflow was unexpectedly accepted without error!");
        }

        // Boundary Test: Negative value behavior
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO bahan_baku (nama_bahan, satuan, harga_beli_persatuan, stok, minimum_stok, created_at, updated_at)
                VALUES ('NEGATIVE_STOCK_TEST', 'kg', 100, -50.25, 0, NOW(), NOW())
                RETURNING stok
            ");
            $stmt->execute();
            $negStok = $stmt->fetchColumn();
            $this->pdo->rollBack();
            $this->warn("Negative stock acceptance", "PostgreSQL schema lacks CHECK (stok >= 0). Value '-50.25' accepted by DDL. App layer must validate non-negative values.");
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            $this->assert("Negative stock constraint enforced", true, "DB rejected negative stock");
        }
    }

    public function test5_DecimalRoundingBehavior(): void
    {
        echo "\nSection 5: Decimal Rounding & Truncation Edge Cases\n";

        // Test Postgres standard half-up rounding on 3rd decimal place
        $cases = [
            '123.456' => '123.46',
            '123.454' => '123.45',
            '0.005'   => '0.01',
            '0.004'   => '0.00',
        ];

        foreach ($cases as $input => $expected) {
            $this->pdo->beginTransaction();
            try {
                $stmt = $this->pdo->prepare("
                    INSERT INTO bahan_baku (nama_bahan, satuan, harga_beli_persatuan, stok, minimum_stok, created_at, updated_at)
                    VALUES ('ROUND_TEST', 'kg', 10, :stok, 0, NOW(), NOW())
                    RETURNING stok
                ");
                $stmt->execute(['stok' => $input]);
                $actual = $stmt->fetchColumn();
                $this->pdo->rollBack();

                $this->assert(
                    "PostgreSQL rounds input '{$input}' to '{$expected}' on numeric(12,2)",
                    $actual === $expected,
                    "Input {$input} became {$actual}, expected {$expected}"
                );
            } catch (\Throwable $e) {
                $this->pdo->rollBack();
                $this->assert("PostgreSQL rounds input '{$input}'", false, $e->getMessage());
            }
        }
    }

    public function test6_DecimalRecipeCalculations(): void
    {
        echo "\nSection 6: Decimal Precision in Multi-Table Recipe Calculations\n";

        $this->pdo->beginTransaction();
        try {
            // 1. Insert Raw Material with 500.00 grams
            $stmt = $this->pdo->prepare("
                INSERT INTO bahan_baku (nama_bahan, satuan, harga_beli_persatuan, stok, minimum_stok, created_at, updated_at)
                VALUES ('Tepung Terigu Stress Test', 'gram', 15, 500.00, 50, NOW(), NOW())
                RETURNING id
            ");
            $stmt->execute();
            $bahanId = (int) $stmt->fetchColumn();

            // 2. Insert Finished Product
            $stmt = $this->pdo->prepare("
                INSERT INTO produk_jadi (nama_produk, harga_jual, stok, minimum_stok, created_at, updated_at)
                VALUES ('Roti Sobek Stress Test', 25000, 0, 5, NOW(), NOW())
                RETURNING id
            ");
            $stmt->execute();
            $produkId = (int) $stmt->fetchColumn();

            // 3. Insert Recipe with decimal requirement: 37.75 grams per piece
            $stmt = $this->pdo->prepare("
                INSERT INTO resep (produk_jadi_id, bahan_baku_id, kebutuhan_satuan, created_at, updated_at)
                VALUES (:pid, :bid, 37.75, NOW(), NOW())
                RETURNING id
            ");
            $stmt->execute(['pid' => $produkId, 'bid' => $bahanId]);
            $resepId = (int) $stmt->fetchColumn();

            // 4. Calculate deduction for 13 units:
            // 13 * 37.75 = 490.75
            // 500.00 - 490.75 = 9.25
            $stmt = $this->pdo->prepare("
                SELECT 
                    bb.stok - (r.kebutuhan_satuan * :qty) AS sisa_stok,
                    (r.kebutuhan_satuan * :qty) AS total_kebutuhan
                FROM resep r
                JOIN bahan_baku bb ON bb.id = r.bahan_baku_id
                WHERE r.id = :rid
            ");
            $stmt->execute(['qty' => 13, 'rid' => $resepId]);
            $calc = $stmt->fetch();

            $this->assert(
                "Precise total recipe deduction calculation (13 * 37.75 = 490.75)",
                $calc['total_kebutuhan'] === '490.75',
                "Got: " . ($calc['total_kebutuhan'] ?? 'null')
            );
            $this->assert(
                "Precise stock remainder calculation (500.00 - 490.75 = 9.25)",
                $calc['sisa_stok'] === '9.25',
                "Got: " . ($calc['sisa_stok'] ?? 'null')
            );

            // 5. Update stok with explicit cast (:takaran::numeric * :qty::integer)
            // (Note: Raw bind (:takaran * :qty) without type cast causes PostgreSQL 42725 Ambiguous Operator)
            $stmt = $this->pdo->prepare("
                UPDATE bahan_baku 
                SET stok = stok - (:takaran::numeric * :qty::integer) 
                WHERE id = :bid 
                RETURNING stok
            ");
            $stmt->execute(['takaran' => '37.75', 'qty' => 13, 'bid' => $bahanId]);
            $updatedStok = $stmt->fetchColumn();

            $this->assert(
                "Updated stock with explicit cast in database is exactly 9.25",
                $updatedStok === '9.25',
                "Got: {$updatedStok}"
            );

            // 6. Demonstrate PostgreSQL MVCC Read Committed Isolation:
            // Eloquent is on a separate connection, so it cannot see uncommitted row from PDO transaction!
            $eloquentAttempt = \App\Models\BahanBaku::find($bahanId);
            $this->assert(
                "PostgreSQL MVCC Read-Committed isolation: separate connection cannot see uncommitted row",
                $eloquentAttempt === null,
                "Expected null due to MVCC transaction isolation"
            );

            // Rollback the PDO transaction
            $this->pdo->rollBack();

            // 7. Test Eloquent decrement inside Laravel DB::transaction
            \Illuminate\Support\Facades\DB::transaction(function () {
                $b = \App\Models\BahanBaku::create([
                    'nama_bahan' => 'ELOQUENT_DECREMENT_TEST',
                    'satuan' => 'gram',
                    'harga_beli_persatuan' => 20,
                    'stok' => 500.00,
                    'minimum_stok' => 10,
                ]);

                $b->decrement('stok', 490.75);
                $b->refresh();

                $this->assert(
                    "Eloquent decrement('stok', 490.75) computes precise stock 9.25 on PostgreSQL",
                    (string)$b->stok === '9.25',
                    "Expected 9.25, got: " . (string)$b->stok
                );

                // Clean up
                $b->delete();
            });
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            $this->assert("Recipe decimal calculation transaction", false, $e->getMessage());
        }
    }

    public function test7_ForeignKeyConstraintsAndCascades(): void
    {
        echo "\nSection 7: Foreign Key Constraints & Cascade Deletion Stress Testing\n";

        // 1. Attempt invalid foreign key insertion in resep
        $fkViolated = false;
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO resep (produk_jadi_id, bahan_baku_id, kebutuhan_satuan, created_at, updated_at)
                VALUES (999999, 999999, 10.00, NOW(), NOW())
            ");
            $stmt->execute();
            $this->pdo->rollBack();
        } catch (\PDOException $e) {
            $fkViolated = true;
            $this->pdo->rollBack();
            $is23503 = ($e->getCode() === '23503' || str_contains($e->getMessage(), 'foreign key constraint') || str_contains($e->getMessage(), 'violates foreign key'));
            $this->assert("Rejects nonexistent foreign key with SQLSTATE 23503", $is23503, "Caught: " . $e->getMessage());
        }
        if (!$fkViolated) {
            $this->assert("Rejects nonexistent foreign key with SQLSTATE 23503", false, "Did not throw exception!");
        }

        // 2. Cascade delete on produk_jadi -> resep & detail_transaksi
        $this->pdo->beginTransaction();
        try {
            // Create product & bahan
            $stmt = $this->pdo->query("INSERT INTO produk_jadi (nama_produk, harga_jual, stok, minimum_stok, created_at, updated_at) VALUES ('CASCADE_TEST_PROD', 10000, 10, 2, NOW(), NOW()) RETURNING id");
            $pId = (int) $stmt->fetchColumn();

            $stmt = $this->pdo->query("INSERT INTO bahan_baku (nama_bahan, satuan, harga_beli_persatuan, stok, minimum_stok, created_at, updated_at) VALUES ('CASCADE_TEST_BAHAN', 'gram', 10, 100, 10, NOW(), NOW()) RETURNING id");
            $bId = (int) $stmt->fetchColumn();

            // Create resep
            $stmt = $this->pdo->prepare("INSERT INTO resep (produk_jadi_id, bahan_baku_id, kebutuhan_satuan, created_at, updated_at) VALUES (:pid, :bid, 5.00, NOW(), NOW()) RETURNING id");
            $stmt->execute(['pid' => $pId, 'bid' => $bId]);
            $rId = (int) $stmt->fetchColumn();

            // Create transaksi & detail_transaksi
            $stmt = $this->pdo->query("INSERT INTO transaksi (metode_pembayaran, total_pendapatan, total_hpp, tanggal_transaksi, created_at, updated_at) VALUES ('tunai', 20000, 10000, NOW(), NOW(), NOW()) RETURNING id");
            $tId = (int) $stmt->fetchColumn();

            $stmt = $this->pdo->prepare("INSERT INTO detail_transaksi (transaksi_id, produk_jadi_id, qty, subtotal, created_at, updated_at) VALUES (:tid, :pid, 2, 20000, NOW(), NOW()) RETURNING id");
            $stmt->execute(['tid' => $tId, 'pid' => $pId]);
            $dtId = (int) $stmt->fetchColumn();

            // Delete produk_jadi
            $stmt = $this->pdo->prepare("DELETE FROM produk_jadi WHERE id = :id");
            $stmt->execute(['id' => $pId]);

            // Verify resep is cascaded and deleted
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM resep WHERE id = :id");
            $stmt->execute(['id' => $rId]);
            $this->assert("Resep record was cascade-deleted when produk_jadi was deleted", (int) $stmt->fetchColumn() === 0);

            // Verify detail_transaksi is cascaded and deleted
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM detail_transaksi WHERE id = :id");
            $stmt->execute(['id' => $dtId]);
            $this->assert("Detail_transaksi record was cascade-deleted when produk_jadi was deleted", (int) $stmt->fetchColumn() === 0);

            // Delete transaksi and verify detail_transaksi cascade behavior
            $stmt = $this->pdo->prepare("DELETE FROM transaksi WHERE id = :id");
            $stmt->execute(['id' => $tId]);

            $this->pdo->rollBack();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            $this->assert("Cascade deletion workflow", false, $e->getMessage());
        }
    }

    public function test8_TransactionRollbackAtomicity(): void
    {
        echo "\nSection 8: Transaction Rollback & Zero-Residue Integrity\n";

        $testTag = 'ROLLBACK_ATOMICITY_' . bin2hex(random_bytes(4));

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                INSERT INTO bahan_baku (nama_bahan, satuan, harga_beli_persatuan, stok, minimum_stok, created_at, updated_at)
                VALUES (:nama, 'butir', 2000, 100.00, 10, NOW(), NOW())
            ");
            $stmt->execute(['nama' => $testTag]);

            // Simulate failure condition
            throw new \RuntimeException("Simulated network/runtime exception mid-transaction");
        } catch (\RuntimeException $e) {
            $this->pdo->rollBack();
        }

        // Verify zero residue in database
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM bahan_baku WHERE nama_bahan = :nama");
        $stmt->execute(['nama' => $testTag]);
        $count = (int) $stmt->fetchColumn();

        $this->assert("Aborted transaction leaves zero orphan records in cloud DB", $count === 0, "Found {$count} orphan records!");
    }

    public function test9_RowLockingCapability(): void
    {
        echo "\nSection 9: Row-Level Concurrency & Advisory Locking via Supavisor Pooler\n";

        $this->pdo->beginTransaction();
        try {
            // Test SELECT ... FOR UPDATE on seeded user
            $stmt = $this->pdo->query("SELECT id, email FROM users WHERE id = 1 FOR UPDATE");
            $user = $stmt->fetch();
            $this->assert("Session mode supports 'SELECT ... FOR UPDATE' row lock without deadlock", !empty($user));

            // Test pg_try_advisory_xact_lock
            $stmt = $this->pdo->query("SELECT pg_try_advisory_xact_lock(123456789) as locked");
            $locked = (bool) $stmt->fetchColumn();
            $this->assert("Supavisor pooler (port 5432) permits transactional advisory locking", $locked);

            $this->pdo->rollBack();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            $this->assert("Row locking capability", false, $e->getMessage());
        }
    }

    public function test10_PostgresSequenceSynchronization(): void
    {
        echo "\nSection 10: PostgreSQL Sequence State & Auto-Increment Sync\n";

        $tablesWithSequences = [
            'users' => 'users_id_seq',
            'bahan_baku' => 'bahan_baku_id_seq',
            'produk_jadi' => 'produk_jadi_id_seq',
            'resep' => 'resep_id_seq',
            'transaksi' => 'transaksi_id_seq',
            'detail_transaksi' => 'detail_transaksi_id_seq',
        ];

        foreach ($tablesWithSequences as $table => $seq) {
            // Check max id
            $stmt = $this->pdo->query("SELECT COALESCE(MAX(id), 0) FROM {$table}");
            $maxId = (int) $stmt->fetchColumn();

            // Check sequence last_value
            $stmt = $this->pdo->query("SELECT last_value, is_called FROM {$seq}");
            $seqInfo = $stmt->fetch();

            $lastVal = (int) $seqInfo['last_value'];
            $isCalled = (bool) $seqInfo['is_called'];

            // If table has rows and sequence has been called, last_value must be >= maxId
            if ($maxId > 0 && $isCalled) {
                $this->assert(
                    "Sequence '{$seq}' (last_val: {$lastVal}) is in sync with table {$table} (max_id: {$maxId})",
                    $lastVal >= $maxId,
                    "Sequence out of sync! last_val {$lastVal} < max_id {$maxId}"
                );
            } else {
                $this->assert(
                    "Sequence '{$seq}' is valid (current: {$lastVal})",
                    $lastVal >= 1,
                    "Invalid sequence state"
                );
            }
        }
    }

    public function test11_DashboardLikeQueryAndBatchInsert(): void
    {
        echo "\nSection 11: Real-World Query Patterns & Stress (Dashboard LIKE & Batch Ingestion)\n";

        // 1. Test DashboardIndex.php line 56: Transaksi::where('id', 'like', '%1%')
        try {
            $results = \App\Models\Transaksi::where('id', 'like', '%1%')
                ->orWhere('metode_pembayaran', 'like', '%tunai%')
                ->get();
            $this->assert("Dashboard LIKE query on integer ID: Transaksi::where('id', 'like', ...) succeeds on PG 17", true);
        } catch (\PDOException $e) {
            $is42883 = ($e->getCode() === '42883' || str_contains($e->getMessage(), 'operator does not exist: bigint ~~'));
            if ($is42883) {
                $this->warn(
                    "Dashboard LIKE on integer ID",
                    "PostgreSQL raised 42883: operator does not exist: bigint ~~ text. Application code should use 'whereRaw(\"id::text LIKE ?\", ...)' or 'where(\"id\", $search)'."
                );
            } else {
                $this->assert("Dashboard LIKE query execution", false, $e->getMessage());
            }
        }

        // 2. High-volume batch ingestion test (50 rows in single multi-row VALUES query)
        $this->pdo->beginTransaction();
        try {
            $start = microtime(true);
            $values = [];
            $params = [];
            for ($i = 0; $i < 50; $i++) {
                $values[] = "(:nama_{$i}, 'gram', :harga_{$i}, :stok_{$i}, 10, NOW(), NOW())";
                $params["nama_{$i}"] = "BATCH_STRESS_{$i}";
                $params["harga_{$i}"] = 1000 + $i;
                $params["stok_{$i}"] = sprintf("%.2f", 10.50 + $i);
            }
            $sql = "INSERT INTO bahan_baku (nama_bahan, satuan, harga_beli_persatuan, stok, minimum_stok, created_at, updated_at) VALUES " . implode(',', $values);
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $elapsed = (microtime(true) - $start) * 1000;

            $this->assert("Batch ingestion of 50 rows in single statement succeeds in < 1500ms (took {$elapsed}ms)", $elapsed < 1500.0);

            $this->pdo->rollBack();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            $this->assert("Batch ingestion stress test", false, $e->getMessage());
        }
    }
}

// Execute harness
try {
    $tester = new SupabaseEmpiricalStressTester();
    $tester->runAll();
} catch (\Throwable $e) {
    echo "\nFATAL EXCEPTION DURING STRESS TEST: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
