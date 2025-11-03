# SSH/Alias Integration Tests

This directory contains integration tests for SSH remote execution and YAML alias resolution functionality in MODX CLI.

## Test Files

### 1. SSHConnectionTest.php (8 tests)
Tests SSH connection string parsing and validation:
- Full connection string parsing (`user@host:port/path`)
- Connection strings with default port
- Connection strings without user (uses current system user)
- Minimal connection strings (host only)
- Connection strings with tilde paths (`~/path`)
- Connection string reconstruction via `__toString()`
- IPv4 address support
- Port validation and extraction

### 2. AliasCommandTest.php (6 tests)
Tests YAML alias resolution and group management:
- Alias detection (commands starting with `@`)
- Single alias resolution to SSH connection strings
- Multiple alias resolution
- Alias group detection
- Alias group member retrieval
- Error handling for non-existent aliases

### 3. RemoteExecutionTest.php (8 tests)
Tests SSH command construction and execution:
- SSH command building with all connection components
- SSH command building without custom port
- SSH command building without remote path
- Remote command construction with arguments
- Special character escaping in arguments
- Handler class delegation to CommandProxy
- IPv4 address support in SSH commands
- Tilde path handling in SSH commands

## Total Test Coverage
**22 comprehensive integration tests** covering all SSH/alias functionality.

## Running the Tests

### Run All Integration Tests (including SSH tests)
```bash
MODX_INTEGRATION_TESTS=1 vendor/bin/phpunit --testsuite=Integration
```

### Run Only SSH Integration Tests
```bash
MODX_INTEGRATION_TESTS=1 vendor/bin/phpunit tests/Integration/SSH/
```

### Run Individual Test Classes
```bash
# Connection parsing tests
MODX_INTEGRATION_TESTS=1 vendor/bin/phpunit tests/Integration/SSH/SSHConnectionTest.php

# Alias resolution tests
MODX_INTEGRATION_TESTS=1 vendor/bin/phpunit tests/Integration/SSH/AliasCommandTest.php

# Remote execution tests
MODX_INTEGRATION_TESTS=1 vendor/bin/phpunit tests/Integration/SSH/RemoteExecutionTest.php
```

## Environment Configuration

These tests require the following environment variables (configured in `tests/Integration/.env`):

```bash
MODX_INTEGRATION_TESTS=1
MODX_TEST_INSTANCE_PATH="/path/to/modx/test/instance"
MODX_TEST_DB_HOST="mysql"
MODX_TEST_DB_NAME="cli-test"
MODX_TEST_DB_USER="root"
MODX_TEST_DB_PASS="password"
```

## Test Architecture

All SSH integration tests extend `BaseIntegrationTest`, which provides:
- Environment variable loading from `.env` file
- MODX CLI command execution helpers
- Database query helpers
- Automatic test skipping when integration tests are disabled

## Components Tested

### SSH Components
- **ConnectionParser**: Parses SSH connection strings
- **Handler**: Delegates command execution to CommandProxy
- **CommandProxy**: Builds and executes SSH commands using Symfony Process

### Alias Components
- **Alias\Resolver**: Resolves YAML aliases and groups
- **Configuration\Yaml\YamlConfig**: Loads YAML configuration

## Test Strategy

1. **Unit-like Integration Tests**: Most tests verify component behavior without actual SSH connections
2. **Protected Method Testing**: Uses PHP Reflection to test protected methods like `buildSSHCommand()`
3. **Temporary Test Data**: AliasCommandTest creates temporary YAML config files for isolated testing
4. **Structural Verification**: Tests verify proper command construction and component delegation

## Notes

- Remote execution tests use Reflection to test internal command building logic
- Actual SSH command execution is not performed to avoid requiring live SSH infrastructure
- Tests focus on verifying correct SSH command construction and parameter handling
- Alias tests create temporary YAML configuration files that are cleaned up after each test

## Future Enhancements

Possible extensions to this test suite:
1. Mock SSH server integration for end-to-end execution tests
2. Docker-based SSH server for live testing
3. SSH config file alias resolution tests
4. Multi-server group execution tests
5. Error handling for network timeouts and connection failures
