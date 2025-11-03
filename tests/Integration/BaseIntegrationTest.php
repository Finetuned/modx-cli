<?php

namespace MODX\CLI\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;
use Dotenv\Dotenv;

/**
 * Base class for integration tests that execute commands against real MODX instances
 */
abstract class BaseIntegrationTest extends TestCase
{
    /**
     * Path to the MODX CLI binary
     */
    protected string $binPath;

    /**
     * Path to the test MODX instance
     */
    protected string $modxPath;

    /**
     * Instance alias name for CLI -s flag
     */
    protected string $instanceAlias;

    /**
     * Database configuration for test instance
     */
    protected array $dbConfig;

    /**
     * Whether integration tests are enabled
     */
    protected bool $integrationTestsEnabled;

    /**
     * Database table prefix
     */
    protected string $tablePrefix;

    /**
     * Cached table names (computed once in setUp)
     */
    protected string $categoriesTable;
    protected string $chunksTable;
    protected string $snippetsTable;
    protected string $templatesTable;
    protected string $tvsTable;

    /**
     * Setup the test environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Load environment variables from .env file
        $this->loadEnvironment();
        
        $this->binPath = realpath(__DIR__ . '/../../bin/modx');
        $this->integrationTestsEnabled = (bool) getenv('MODX_INTEGRATION_TESTS');
        
        

        // Skip tests if integration testing is not enabled
        if (!$this->integrationTestsEnabled) {
            $this->markTestSkipped(
                'Integration tests are disabled. Set MODX_INTEGRATION_TESTS=1 to enable.'
            );
        }

        // Load test environment configuration
        $this->modxPath = $_ENV['MODX_TEST_INSTANCE_PATH'] ?: '/tmp/modx-test';
        $this->instanceAlias = $_ENV['MODX_TEST_INSTANCE_ALIAS'] ?: 'test';
        $this->dbConfig = [
            'host' => $_ENV['MODX_TEST_DB_HOST'] ?: 'localhost',
            'name' => $_ENV['MODX_TEST_DB_NAME'] ?: 'modx_test',
            'user' => $_ENV['MODX_TEST_DB_USER'] ?: 'root',
            'pass' => $_ENV['MODX_TEST_DB_PASS'] ?: '',
        ];

        // Verify test environment exists
        if (!file_exists($this->modxPath)) {
            $this->markTestSkipped(
                "Test MODX instance not found at {$this->modxPath}. Add an environment variables file at tests/integration/.env."
            );
        }
        $this->tablePrefix = $_ENV['MODX_TEST_DB_PREFIX'] ?? 'modx_';
        
        // Cache table names once
        $this->categoriesTable = $this->getTableName('categories');
        $this->chunksTable = $this->getTableName('site_htmlsnippets');
        $this->snippetsTable = $this->getTableName('site_snippets');
        $this->templatesTable = $this->getTableName('site_templates');
        $this->tvsTable = $this->getTableName('site_tmplvars');
    }

    /**
     * Load environment variables from .env file
     */
    protected function loadEnvironment(): void
    {
         $envDir = __DIR__; // .env is in the same directory as this file
        // error_log( 'env file expected in: '. $envDir, 3, '/dev/stdout' );

        if (file_exists($envDir . '/.env')) {
            $dotenv = Dotenv::createImmutable($envDir);
            $dotenv->load();
        }
    }

    /**
     * Get full table name with configured prefix
     *
     * @param string $tableName Base table name (e.g., 'categories', 'site_htmlsnippets')
     * @return string Full table name with prefix
     */
    protected function getTableName(string $tableName): string
    {
        return $this->tablePrefix . $tableName;
    }
    
    /**
     * Execute a MODX CLI command and return the process result
     *
     * @param array $arguments Command arguments (e.g., ['category:list', '--json'])
     * @param int $timeout Process timeout in seconds
     * @return Process
     */
    protected function executeCommand(array $arguments, int $timeout = 30): Process
    {
        // Add -s flag to force CLI to use test instance
        $command = array_merge(
            ['php', $this->binPath, '-s', $this->instanceAlias],
            $arguments
        );
        
        $process = new Process($command);
        $process->setTimeout($timeout);
        // Working directory not needed with -s flag, but set for consistency
        $process->setWorkingDirectory($this->modxPath);
        
        // Set environment variables for the command
        $process->setEnv([
            'MODX_CONFIG_KEY' => 'config',
        ]);
        
        $process->run();
        
        return $process;
    }

    /**
     * Execute a command and assert it succeeded
     *
     * @param array $arguments Command arguments
     * @return Process
     */
    protected function executeCommandSuccessfully(array $arguments): Process
    {
        $process = $this->executeCommand($arguments);
        
        $this->assertEquals(
            0,
            $process->getExitCode(),
            sprintf(
                "Command failed with exit code %d\nOutput: %s\nError: %s",
                $process->getExitCode(),
                $process->getOutput(),
                $process->getErrorOutput()
            )
        );
        
        return $process;
    }

    /**
     * Execute a command and get JSON output
     *
     * @param array $arguments Command arguments (--json will be added)
     * @return array Decoded JSON response
     */
    protected function executeCommandJson(array $arguments): array
    {
        // Ensure --json flag is present
        if (!in_array('--json', $arguments)) {
            $arguments[] = '--json';
        }
        
        $process = $this->executeCommandSuccessfully($arguments);
        $output = $process->getOutput();
        
        $data = json_decode($output, true);
        
        $this->assertIsArray($data, 'Command output is not valid JSON: ' . $output);
        
        return $data;
    }

    /**
     * Get a PDO connection to the test database
     *
     * @return \PDO
     */
    protected function getTestDatabase(): \PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=utf8mb4',
            $this->dbConfig['host'],
            $this->dbConfig['name']
        );
        
        return new \PDO(
            $dsn,
            $this->dbConfig['user'],
            $this->dbConfig['pass'],
            [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]
        );
    }

    /**
     * Execute a SQL query on the test database
     *
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array Query results
     */
    protected function queryDatabase(string $sql, array $params = []): array
    {
        $pdo = $this->getTestDatabase();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    }

    /**
     * Count rows in a database table
     *
     * @param string $table Table name
     * @param string $where Optional WHERE clause
     * @param array $params Query parameters
     * @return int Row count
     */
    protected function countTableRows(string $table, string $where = '', array $params = []): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$table}";
        if ($where) {
            $sql .= " WHERE {$where}";
        }
        
        $result = $this->queryDatabase($sql, $params);
        
        return (int) $result[0]['count'];
    }

    /**
     * Clean up test data after test execution
     */
    protected function tearDown(): void
    {
        // Subclasses can override to implement cleanup logic
        parent::tearDown();
    }


}
