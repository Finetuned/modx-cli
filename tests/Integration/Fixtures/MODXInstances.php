<?php

namespace MODX\CLI\Tests\Integration\Fixtures;

/**
 * Manages MODX test instance configurations and fixtures
 */
class MODXInstances
{
    /**
     * Get configuration for an empty MODX instance
     * 
     * @return array Configuration array with instance path and database details
     */
    public static function getEmptyInstance(): array
    {
        return [
            'path' => getenv('MODX_TEST_INSTANCE_PATH') ?: '/tmp/modx-test',
            'database' => [
                'host' => getenv('MODX_TEST_DB_HOST') ?: 'localhost',
                'name' => getenv('MODX_TEST_DB_NAME') ?: 'modx_test_empty',
                'user' => getenv('MODX_TEST_DB_USER') ?: 'root',
                'pass' => getenv('MODX_TEST_DB_PASS') ?: '',
            ],
            'description' => 'Clean MODX installation with no custom content',
        ];
    }

    /**
     * Get configuration for a populated MODX instance with sample data
     * 
     * @return array Configuration array with instance path and database details
     */
    public static function getPopulatedInstance(): array
    {
        return [
            'path' => getenv('MODX_TEST_INSTANCE_PATH') ?: '/tmp/modx-test',
            'database' => [
                'host' => getenv('MODX_TEST_DB_HOST') ?: 'localhost',
                'name' => getenv('MODX_TEST_DB_NAME') ?: 'modx_test_populated',
                'user' => getenv('MODX_TEST_DB_USER') ?: 'root',
                'pass' => getenv('MODX_TEST_DB_PASS') ?: '',
            ],
            'description' => 'MODX installation with sample categories, chunks, snippets, etc.',
            'fixtures' => [
                'categories' => 5,
                'chunks' => 10,
                'snippets' => 8,
                'templates' => 3,
                'resources' => 20,
            ],
        ];
    }

    /**
     * Get configuration for a broken/misconfigured MODX instance
     * Used to test error handling and recovery scenarios
     * 
     * @return array Configuration array with instance path and database details
     */
    public static function getBrokenInstance(): array
    {
        return [
            'path' => getenv('MODX_TEST_INSTANCE_PATH') ?: '/tmp/modx-test',
            'database' => [
                'host' => getenv('MODX_TEST_DB_HOST') ?: 'localhost',
                'name' => getenv('MODX_TEST_DB_NAME') ?: 'modx_test_broken',
                'user' => getenv('MODX_TEST_DB_USER') ?: 'root',
                'pass' => getenv('MODX_TEST_DB_PASS') ?: '',
            ],
            'description' => 'MODX installation with intentional configuration issues for error testing',
            'issues' => [
                'missing_config' => true,
                'invalid_processor_paths' => true,
            ],
        ];
    }

    /**
     * Clean up all test instances and databases
     * 
     * @param array $instances Array of instance configurations to clean up
     * @return void
     */
    public static function cleanup(array $instances = []): void
    {
        if (empty($instances)) {
            $instances = [
                self::getEmptyInstance(),
                self::getPopulatedInstance(),
                self::getBrokenInstance(),
            ];
        }

        foreach ($instances as $instance) {
            self::cleanupDatabase($instance['database']);
        }
    }

    /**
     * Clean up a specific test database
     * 
     * @param array $dbConfig Database configuration
     * @return void
     */
    protected static function cleanupDatabase(array $dbConfig): void
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;charset=utf8mb4',
                $dbConfig['host']
            );
            
            $pdo = new \PDO(
                $dsn,
                $dbConfig['user'],
                $dbConfig['pass'],
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                ]
            );

            // Drop test database if it exists
            $pdo->exec("DROP DATABASE IF EXISTS `{$dbConfig['name']}`");
            
        } catch (\PDOException $e) {
            // Log error but don't fail - cleanup is best effort
            error_log("Failed to cleanup test database {$dbConfig['name']}: " . $e->getMessage());
        }
    }

    /**
     * Create a test database for an instance
     * 
     * @param array $dbConfig Database configuration
     * @return bool Success status
     */
    public static function createDatabase(array $dbConfig): bool
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;charset=utf8mb4',
                $dbConfig['host']
            );
            
            $pdo = new \PDO(
                $dsn,
                $dbConfig['user'],
                $dbConfig['pass'],
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                ]
            );

            // Create test database
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbConfig['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            return true;
            
        } catch (\PDOException $e) {
            error_log("Failed to create test database {$dbConfig['name']}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a test instance is properly configured and accessible
     * 
     * @param array $instance Instance configuration
     * @return bool True if instance is accessible
     */
    public static function isInstanceAccessible(array $instance): bool
    {
        // Check if instance path exists
        if (!file_exists($instance['path'])) {
            return false;
        }

        // Check if database is accessible
        try {
            $dsn = sprintf(
                'mysql:host=%s;dbname=%s;charset=utf8mb4',
                $instance['database']['host'],
                $instance['database']['name']
            );
            
            $pdo = new \PDO(
                $dsn,
                $instance['database']['user'],
                $instance['database']['pass'],
                [
                    \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                ]
            );
            
            // Simple query to verify database is functional
            $pdo->query('SELECT 1');
            
            return true;
            
        } catch (\PDOException $e) {
            return false;
        }
    }
}
