# MODX CLI Tests

This directory contains unit tests for the MODX CLI application. The tests are organized by component and cover both the core functionality and the internal API.

## Test Structure

- `tests/API/`: Tests for the internal API components
  - `CommandRegistryTest.php`: Tests for the command registration and retrieval
  - `HookRegistryTest.php`: Tests for the hook registration and execution
  - `ClosureCommandTest.php`: Tests for the closure command wrapper
  - `CommandRunnerTest.php`: Tests for running commands programmatically
  - `CommandPublisherTest.php`: Tests for asynchronous command execution
  - `MODX_CLITest.php`: Tests for the static API methods

- `tests/Command/`: Tests for the CLI commands
  - `RunSequenceTest.php`: Tests for the run-sequence command

- `tests/Configuration/`: Tests for the configuration classes
  - `BaseTest.php`: Tests for the base configuration class
  - `ComponentTest.php`: Tests for the component configuration
  - `ExtensionTest.php`: Tests for the extension configuration
  - `InstanceTest.php`: Tests for the instance configuration

## Running Tests

To run the tests, use PHPUnit:

```bash
# Run all tests
vendor/bin/phpunit

# Run a specific test file
vendor/bin/phpunit tests/API/CommandRegistryTest.php

# Run a specific test method
vendor/bin/phpunit --filter testRegisterClosureCommand tests/API/CommandRegistryTest.php
```

## Test Dependencies

The tests use PHPUnit for unit testing. Some tests may require additional dependencies:

- `PHPUnit`: For running the tests
- `Mockery`: For mocking objects
- `Symfony/Console`: For testing console commands

## Test Coverage

The tests cover the following functionality:

### Internal API Tests

- **CommandRegistry**: Tests the registration, unregistration, and retrieval of commands
- **HookRegistry**: Tests the registration, unregistration, and execution of hooks
- **ClosureCommand**: Tests the execution of commands created from closures and the hook integration
- **CommandRunner**: Tests running commands programmatically with various options
- **CommandPublisher**: Tests the asynchronous execution of commands
- **MODX_CLI**: Tests the static API methods for registering commands, running commands, and hooking into the command lifecycle

### Command Tests

- **RunSequence**: Tests the run-sequence command with various CRUD operations for different MODX elements

### Configuration Tests

- **Base**: Tests the base configuration class
- **Component**: Tests the component configuration
- **Extension**: Tests the extension configuration
- **Instance**: Tests the instance configuration

## Writing Tests

When writing tests for the MODX CLI, follow these guidelines:

1. **Use PHPUnit Assertions**: Use PHPUnit assertions to verify the expected behavior of the code.
2. **Mock Dependencies**: Use mocks to isolate the code being tested from its dependencies.
3. **Test Edge Cases**: Test both the happy path and edge cases, such as error conditions.
4. **Clean Up**: Clean up any resources created during the test in the `tearDown()` method.
5. **Use Reflection**: Use reflection to access private properties and methods when necessary.

## Test Data

Some tests may require test data, such as configuration files or mock MODX elements. These should be created in the test itself and cleaned up afterward.

## Mocking Static Methods

For tests that need to mock static methods, use reflection to replace the static instance with a mock object. For example:

```php
// Create a mock for MODX_CLI
$modxCliMock = $this->getMockBuilder(MODX_CLI::class)
    ->disableOriginalConstructor()
    ->setMethods(['run_command'])
    ->getMock();

// Configure the mock
$modxCliMock->method('run_command')
    ->willReturn($result);

// Use reflection to replace the static instance
$reflection = new \ReflectionClass(MODX_CLI::class);
$instanceProperty = $reflection->getProperty('instance');
$instanceProperty->setAccessible(true);
$originalInstance = $instanceProperty->getValue(null);
$instanceProperty->setValue(null, $modxCliMock);

// Restore the original instance in tearDown()
$instanceProperty->setValue(null, $originalInstance);
```

## Test Isolation

Tests should be isolated from each other and from the system. This means:

1. **Don't Rely on External State**: Tests should not rely on the state of the system or other tests.
2. **Clean Up After Tests**: Clean up any resources created during the test.
3. **Mock External Dependencies**: Mock external dependencies to isolate the code being tested.
4. **Reset Static State**: Reset any static state that may be modified during the test.

## Continuous Integration

The tests are run as part of the continuous integration process. The CI pipeline will run the tests on each commit to ensure that the code is working as expected.
