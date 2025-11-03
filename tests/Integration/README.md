# MODX CLI Integration Testing

## Overview

This directory contains integration tests for the MODX CLI. Unlike unit tests that use mocked dependencies, integration tests execute the actual CLI commands against real MODX instances to verify end-to-end functionality.

## Directory Structure

```
tests/Integration/
├── README.md                           # This file
├── BaseIntegrationTest.php              # Base class for all integration tests
├── docker-compose.yml                   # Docker environment for tests
├── Fixtures/                           # Test data and configurations
│   ├── MODXInstances.php                # MODX instance management
│   ├── SampleData.php                   # Sample test data
│   └── TestConfigs/                     # Configuration files
├── Commands/                           # Command integration tests
│   └── Category/
│       └── CategoryListTest.php         # Example test
└── EndToEnd/                          # Full workflow tests
```

## Requirements

- Docker and Docker Compose
- PHP 7.4 or higher
- MySQL 8.0 (via Docker)
- PHPUnit 9.5+
- A MODX 3.x installation for testing

## Environment Setup

### 1. Environment Variables

Integration tests require specific environment variables to be set:

```bash
export MODX_INTEGRATION_TESTS=1
export MODX_TEST_INSTANCE_PATH=/path/to/modx/test/instance
export MODX_TEST_DB_HOST=localhost
export MODX_TEST_DB_NAME=modx_test
export MODX_TEST_DB_PREFIX=modx_
export MODX_TEST_DB_USER=root
export MODX_TEST_DB_PASS=testpass
```

### 2. Using Docker (Recommended)

Start the test environment using Docker Compose:

```bash
cd tests/Integration
docker-compose up -d
```

This will create:
- A MySQL container for test databases
- A PHP container for running tests
- Isolated networks and volumes

To stop the environment:

```bash
docker-compose down
```

To clean up everything including volumes:

```bash
docker-compose down -v
```

### 3. Manual Setup

If not using Docker, ensure you have:

1. A MySQL database accessible for testing
2. A MODX 3.x installation configured to use the test database
3. PHP CLI with required extensions (PDO, MySQL)

## Running Integration Tests

### Run All Integration Tests

```bash
# From project root
MODX_INTEGRATION_TESTS=1 vendor/bin/phpunit --testsuite=Integration
```

### Run Specific Test Class

```bash
MODX_INTEGRATION_TESTS=1 vendor/bin/phpunit tests/Integration/Commands/Category/CategoryListTest.php
```

### Run with Verbose Output

```bash
MODX_INTEGRATION_TESTS=1 vendor/bin/phpunit --testsuite=Integration --verbose
```

### Skip Integration Tests

Integration tests are automatically skipped if `MODX_INTEGRATION_TESTS` is not set to `1`. This prevents accidental execution during regular unit test runs.

## Writing Integration Tests

### Basic Structure

All integration tests should extend `BaseIntegrationTest`:

```php
<?php

namespace MODX\CLI\Tests\Integration\Commands\Category;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

class CategoryCreateTest extends BaseIntegrationTest
{
    public function testCategoryCreation()
    {
        // Execute command
        $process = $this->executeCommandSuccessfully([
            'category:create',
            'TestCategory',
            '--parent=0'
        ]);
        
        // Verify output
        $output = $process->getOutput();
        $this->assertStringContainsString('created successfully', $output);
        
        // Verify database state
        $count = $this->countTableRows($this->categoriesTable, 'category = ?', ['TestCategory']);
        $this->assertEquals(1, $count);
    }
}
```

### Available Helper Methods

#### Command Execution

**executeCommand(array $arguments, int $timeout = 30): Process**
- Execute a CLI command and return the Process object
- Does not assert success or failure

**executeCommandSuccessfully(array $arguments): Process**
- Execute a command and assert it succeeded (exit code 0)
- Throws assertion failure with output on error

**executeCommandJson(array $arguments): array**
- Execute a command with --json flag
- Returns decoded JSON array
- Asserts valid JSON output

#### Database Operations

**getTestDatabase(): \PDO**
- Get a PDO connection to the test database

**queryDatabase(string $sql, array $params = []): array**
- Execute a SQL query and return results

**countTableRows(string $table, string $where = '', array $params = []): int**
- Count rows in a table with optional WHERE clause

### Testing Patterns

#### 1. Command Execution Tests

Test that commands execute without errors:

```php
public function testCommandExecutes()
{
    $process = $this->executeCommand(['category:list']);
    $this->assertEquals(0, $process->getExitCode());
}
```

#### 2. JSON Output Tests

Verify JSON output format:

```php
public function testJsonOutput()
{
    $data = $this->executeCommandJson(['category:list']);
    $this->assertIsArray($data);
    
    if (!empty($data)) {
        $this->assertArrayHasKey('category', $data[0]);
    }
}
```

#### 3. Database State Verification

Ensure commands modify database correctly:

```php
public function testDatabaseChanges()
{
    $beforeCount = $this->countTableRows($this->categoriesTable);
    
    $this->executeCommandSuccessfully([
        'category:create',
        'NewCategory'
    ]);
    
    $afterCount = $this->countTableRows($this->categoriesTable);
    $this->assertEquals($beforeCount + 1, $afterCount);
}
```

#### 4. Error Handling Tests

Verify proper error messages:

```php
public function testErrorHandling()
{
    $process = $this->executeCommand([
        'category:get',
        '999999'  // Non-existent ID
    ]);
    
    $this->assertNotEquals(0, $process->getExitCode());
    $this->assertStringContainsString('not found', $process->getOutput());
}
```

#### 5. Performance Tests

Ensure commands execute within acceptable time:

```php
public function testPerformance()
{
    $startTime = microtime(true);
    $this->executeCommandSuccessfully(['category:list']);
    $executionTime = microtime(true) - $startTime;
    
    $this->assertLessThan(5.0, $executionTime);
}
```

## Test Data Management

### Using Fixtures

The `SampleData` class provides test data:

```php
use MODX\CLI\Tests\Integration\Fixtures\SampleData;

$categories = SampleData::getCategories();
$chunks = SampleData::getChunks();
```

### Database Cleanup

Always clean up test data in tearDown():

```php
protected function tearDown(): void
{
    // Remove test data
    $this->queryDatabase('DELETE FROM '. $this->categoriesTable .' WHERE category LIKE ?', ['Test%']);
    
    parent::tearDown();
}
```

## Best Practices

### 1. Test Isolation

Each test should be independent:
- Don't rely on data from other tests
- Clean up after each test
- Use unique identifiers for test data

### 2. Descriptive Test Names

Use clear, descriptive test method names:

```php
// Good
public function testCategoryCreationWithValidData()

// Bad
public function testCategory()
```

### 3. Comprehensive Assertions

Verify multiple aspects of behavior:

```php
public function testCategoryCreation()
{
    $process = $this->executeCommandSuccessfully(['category:create', 'Test']);
    
    // Verify exit code
    $this->assertEquals(0, $process->getExitCode());
    
    // Verify output message
    $this->assertStringContainsString('created successfully', $process->getOutput());
    
    // Verify database state
    $exists = $this->countTableRows($this->categoriesTable, 'category = ?', ['Test']) > 0;
    $this->assertTrue($exists);
}
```

### 4. Error Message Verification

Test both success and failure paths:

```php
public function testCreateWithInvalidData()
{
    $process = $this->executeCommand(['category:create', '']);
    
    $this->assertNotEquals(0, $process->getExitCode());
    $this->assertStringContainsString('required', $process->getErrorOutput());
}
```

### 5. Performance Considerations

Keep tests fast:
- Use minimal test data
- Clean up efficiently
- Run expensive tests separately if needed

## Troubleshooting

### Tests Are Skipped

**Problem**: All integration tests are skipped

**Solution**: Set environment variable `MODX_INTEGRATION_TESTS=1`

### Cannot Connect to Database

**Problem**: Tests fail with database connection errors

**Solution**: 
- Verify Docker containers are running: `docker-compose ps`
- Check environment variables are set correctly
- Ensure MySQL is healthy: `docker-compose logs mysql`

### MODX Instance Not Found

**Problem**: Tests skip with "Test MODX instance not found"

**Solution**:
- Verify `MODX_TEST_INSTANCE_PATH` points to valid MODX installation
- Ensure MODX is properly configured with test database

### Command Not Found

**Problem**: Tests fail with "Command ... not found"

**Solution**:
- Verify `bin/modx` file exists and is executable
- Check working directory is set correctly
- Ensure MODX CLI is properly installed

## CI/CD Integration

### GitHub Actions Example

```yaml
name: Integration Tests

on: [push, pull_request]

jobs:
  integration:
    runs-on: ubuntu-latest
    
    steps:
      - uses: actions/checkout@v2
      
      - name: Start Docker Environment
        run: |
          cd tests/Integration
          docker-compose up -d
          
      - name: Run Integration Tests
        env:
          MODX_INTEGRATION_TESTS: 1
        run: vendor/bin/phpunit --testsuite=Integration
        
      - name: Stop Docker Environment
        run: |
          cd tests/Integration
          docker-compose down -v
```

## Contributing

When adding new integration tests:

1. Follow existing patterns and structure
2. Extend `BaseIntegrationTest`
3. Use descriptive test and assertion messages
4. Include both success and failure scenarios
5. Document any special setup requirements
6. Ensure tests clean up after themselves

## Additional Resources

- [PHPUnit Documentation](https://phpunit.de/documentation.html)
- [Symfony Process Component](https://symfony.com/doc/current/components/process.html)
- [MODX Documentation](https://docs.modx.com/)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
