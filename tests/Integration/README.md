# Integration Tests

This directory contains integration tests that require a real MODX installation.

## Directory Structure

```
tests/Integration/
├── bootstrap.php              # Integration-specific bootstrap
├── .env                       # Integration test configuration
├── BaseIntegrationTest.php    # Base class for integration tests
├── ApplicationTest.php        # Application integration tests
├── XdomIntegrationTest.php    # Xdom integration tests
├── IntegratedPackageUpgradeTest.php  # Package upgrade integration tests
├── Commands/                  # Command integration tests
├── EndToEnd/                  # End-to-end workflow tests
├── SSH/                       # SSH functionality tests
└── Fixtures/                  # Test fixtures and helpers
```

## Key Changes to Resolve modX Redeclare Errors

### Problem
Previously, running integration tests would cause "Cannot declare class modX" errors because:
1. Unit tests used a stub `modX` class via `tests/fixtures/XdomModxStub.php`
2. Integration tests needed to load the real MODX CMS `modX` class
3. These two conflicted when run in the same process

### Solution
We implemented **complete test isolation** using separate directories and bootstraps:

1. **Unit Tests**: Use `tests/bootstrap.php` which sets up a temporary HOME and loads stubs
2. **Integration Tests**: Use `tests/Integration/bootstrap.php` which NEVER loads stubs

### File Movements
- `tests/XdomIntegrationTest.php` → `tests/Integration/XdomIntegrationTest.php`
- `tests/ApplicationTest.php` → `tests/Integration/ApplicationTest.php`
- `tests/Command/Package/Upgrade/IntegratedPackageUpgradeTest.php` → `tests/Integration/IntegratedPackageUpgradeTest.php`

All moved files had their namespaces updated from `MODX\CLI\Tests` to `MODX\CLI\Tests\Integration`.

## Running Tests

### Unit Tests (Default)
Run unit tests without MODX requirement:

```bash
./vendor/bin/phpunit --testsuite default
```

This runs all tests EXCEPT integration tests, using `tests/bootstrap.php`.

### Integration Tests
Run integration tests with a real MODX instance:

```bash
MODX_INTEGRATION_TESTS=1 ./vendor/bin/phpunit -c phpunit.integration.xml
```

This runs ONLY integration tests, using `tests/Integration/bootstrap.php`.

**Important**: Integration tests require:
- A real MODX installation configured in `tests/Integration/.env`
- `MODX_INTEGRATION_TESTS=1` environment variable set

### All Tests
To run all tests (unit + integration):

```bash
MODX_INTEGRATION_TESTS=1 ./vendor/bin/phpunit --testsuite all
```

Note: This will run unit tests first (with unit bootstrap), then integration tests will be skipped unless you use the integration config.

## Configuration

### Unit Test Configuration
- File: `phpunit.xml.dist`
- Bootstrap: `tests/bootstrap.php`
- Test Suite: `default`
- Excludes: `tests/Integration/`

### Integration Test Configuration
- File: `phpunit.integration.xml`
- Bootstrap: `tests/Integration/bootstrap.php`
- Test Suite: `integration`
- Includes: Only `tests/Integration/`

## Environment Setup

Integration tests require a `.env` file in this directory with the following variables:

```env
MODX_INTEGRATION_TESTS=1
MODX_TEST_INSTANCE_PATH=/path/to/modx
MODX_TEST_INSTANCE_ALIAS=test
MODX_TEST_DB_HOST=localhost
MODX_TEST_DB_NAME=modx_test
MODX_TEST_DB_USER=root
MODX_TEST_DB_PASS=
MODX_TEST_DB_PREFIX=modx_
```

See `tests/Integration/.env.example` for a template (if available).

## Bootstrap Differences

### Unit Bootstrap (`tests/bootstrap.php`)
- Sets temporary `HOME` directory to isolate unit tests
- Loads Composer autoloader
- Loads `XdomModxStub.php` when needed by unit tests

### Integration Bootstrap (`tests/Integration/bootstrap.php`)
- Uses REAL user `HOME` directory to find MODX configurations
- Loads Composer autoloader
- Loads `.env` file from `tests/Integration/.env`
- NEVER loads modX stubs - relies on real MODX class

## Why This Works

The separation ensures:
1. **Unit tests** run in isolation with stubs, testing code logic without MODX
2. **Integration tests** run in isolation with real MODX, testing actual MODX interactions
3. **No cross-contamination**: The two test types never share the same PHP process with conflicting `modX` definitions

## Troubleshooting

### "Cannot declare class modX" error
This means a stub was loaded before the real MODX class. Ensure:
- You're using `phpunit.integration.xml` for integration tests
- `MODX_INTEGRATION_TESTS=1` is set
- Integration tests extend `BaseIntegrationTest` or properly guard against modX conflicts

### Integration tests are skipped
Integration tests require:
1. `MODX_INTEGRATION_TESTS=1` environment variable
2. Valid MODX installation path in `.env`
3. Database configured and accessible

### Tests fail to find MODX
Check your `.env` file has correct paths:
- `MODX_TEST_INSTANCE_PATH` should point to your MODX installation root
- Path should contain `config.core.php`

## Writing New Integration Tests

When creating new integration tests:

1. **Place in `tests/Integration/` directory**
2. **Extend `BaseIntegrationTest`** for helper methods
3. **Use namespace `MODX\CLI\Tests\Integration`**
4. **Add `@group integration` annotation**

Example:

```php
<?php

namespace MODX\CLI\Tests\Integration;

/**
 * @group integration
 * @group requires-modx
 */
class MyFeatureTest extends BaseIntegrationTest
{
    protected function setUp(): void
    {
        parent::setUp();
        // Your setup code
    }
    
    public function testMyFeature()
    {
        // Test using real MODX via $this->executeCommand(), etc.
    }
}
```

## CI/CD Integration

For continuous integration:

```bash
# Run unit tests (no MODX required)
./vendor/bin/phpunit --testsuite default

# Run integration tests (requires MODX setup)
MODX_INTEGRATION_TESTS=1 ./vendor/bin/phpunit -c phpunit.integration.xml
```

Consider running these as separate CI jobs for better isolation.

## Skipped Tests

Some tests are intentionally skipped due to environment constraints or known limitations.
Skip messages should point here for context.

### Integration Environment
- Tests extending `BaseIntegrationTest` are skipped when `MODX_INTEGRATION_TESTS=1` is not set.
- They are also skipped when the MODX install path or database is unavailable.

### SSH Tests
- SSH handler/proxy tests are skipped because they require a real SSH connection or a mockable transport layer.
- Alias detection tests may be skipped when no temp SSH config fixture is provided.

### Known Limitations
- TreeBuilder custom parent field handling with `parent=0` is a known limitation and the test is skipped until addressed.

### Composer IO Coupling
- `CommandRegistrar::unRegister` tests are skipped because Composer IO is tightly coupled and not yet mockable.
