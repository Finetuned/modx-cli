# Custom Command Loading and Execution Integration Tests

This directory contains integration tests for the custom command system in MODX CLI, which allows loading external commands from YAML configuration files.

## Test Files

### 1. CustomCommandLoadingTest.php (6 tests)
Tests the custom command loading and registration system:
- **testCustomCommandConfigurationExists** - Verifies config.yml exists and is valid YAML
- **testCustomCommandsAreRegistered** - Confirms custom commands appear in `modx list`
- **testCustomCommandExecutionBasic** - Tests basic command execution with JSON format
- **testCustomCommandWithOptions** - Tests commands with filter and format options
- **testCustomCommandTableFormat** - Tests table format output
- **testCustomCommandHelp** - Verifies help text displays properly

### 2. CustomCommandFunctionsTest.php (7 tests)
Tests the underlying PHP functions and error handling:
- **testFunctionFileExists** - Verifies functions file exists and is loadable
- **testHelperFunctionsAvailable** - Confirms helper functions are defined
- **testParseVersionHelper** - Tests version string parsing
- **testIsNewerVersionHelper** - Tests version comparison logic
- **testCustomCommandMissingRequiredArgument** - Tests error handling for missing arguments
- **testPackageListRemoteExecution** - Tests remote package listing
- **testDryRunMode** - Verifies dry-run mode doesn't make changes

## Total Test Coverage
**13 comprehensive integration tests** covering the complete custom command system.

## Custom Command System Architecture

### How It Works
1. **Bootstrap Loading**: `src/bootstrap.php` calls `loadCustomCommands()` at startup
2. **YAML Parsing**: Reads `custom-commands/config.yml` using Symfony YAML component
3. **Function Loading**: Loads PHP files specified in `functions_file` parameter
4. **Command Registration**: Registers commands using `MODX_CLI::add_command()`
5. **Execution**: Commands execute like core commands with arguments and options

### Current Custom Commands
- `package:list-upgrades` - List downloaded package upgrades
- `package:list-remote` - List remote versions available
- `package:download` - Download specific package versions
- `package:upgrade-all` - Orchestrate complete upgrade workflow

## Running the Tests

### Run All Custom Command Tests
```bash
MODX_INTEGRATION_TESTS=1 vendor/bin/phpunit tests/Integration/Commands/Custom/
```

### Run Individual Test Classes
```bash
# Loading tests
MODX_INTEGRATION_TESTS=1 vendor/bin/phpunit tests/Integration/Commands/Custom/CustomCommandLoadingTest.php

# Function tests
MODX_INTEGRATION_TESTS=1 vendor/bin/phpunit tests/Integration/Commands/Custom/CustomCommandFunctionsTest.php
```

### Run with Verbose Output
```bash
MODX_INTEGRATION_TESTS=1 vendor/bin/phpunit tests/Integration/Commands/Custom/ --verbose
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

All custom command tests extend `BaseIntegrationTest`, which provides:
- Environment variable loading from `.env` file
- MODX CLI command execution helpers
- Database query helpers
- Automatic test skipping when integration tests are disabled

## Components Tested

### Configuration System
- **config.yml**: YAML configuration parsing
- **Bootstrap loading**: Automatic command registration at startup
- **YAML structure validation**: Proper config file structure

### Function Loading
- **package-upgrade-functions.php**: PHP function file loading
- **Function definitions**: Main command functions
- **Helper functions**: Supporting utility functions

### Command Execution
- **Registration**: Commands appear in `modx list`
- **Execution**: Commands run with arguments and options
- **Output formats**: JSON and table output
- **Error handling**: Missing arguments and invalid options
- **Help system**: Command help text display

## Test Strategy

1. **Configuration Tests**: Verify YAML parsing and structure
2. **Registration Tests**: Confirm commands are loaded into CLI
3. **Execution Tests**: Test actual command execution
4. **Format Tests**: Verify JSON and table output
5. **Error Tests**: Test error handling and validation
6. **Helper Tests**: Validate utility functions

## Test Execution Behavior

When MODX instance not configured:
- Tests skip with clear message: "Test MODX instance not found"
- No false failures
- Framework validation still occurs

When MODX instance configured:
- Tests execute full command execution
- Database state verified where applicable
- Output format validation
- Error scenario testing

## Notes

- Tests use real CLI command execution via Symfony Process
- No actual package downloads occur in tests
- Dry-run mode tests verify no changes are made
- Helper function tests verify version comparison logic
- Configuration validation ensures YAML structure is correct

## Future Enhancements

Possible extensions to this test suite:
1. Test custom command with multiple function files
2. Test command groups and namespacing
3. Test async command execution
4. Test command hooks and filters
5. Test custom command with database interactions
6. Test malformed YAML configuration handling
7. Test function file with syntax errors
8. Test command argument validation edge cases
