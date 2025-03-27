# Tech Context

## Technologies used
- **PHP**: The project requires PHP version `>=7.4`.
- **Symfony Components**: Utilized for CLI tools, process management, and file system utilities.
  - symfony/console: Provides the foundation for the CLI application
  - symfony/process: Manages system processes
  - symfony/finder: Handles file system operations
- **PHPUnit**: Used for unit testing the application.
- **MODX Revolution**: The CMS that this CLI tool interacts with, specifically targeting version 3.x.

## Development setup
- **Project Name**: `modx/cli`
- **Description**: MODX 3 CLI application.
- **License**: MIT
- **Authors**: Developed by Finetuned Limited.
- **Autoloading**:
  - Production: PSR-4 autoloading for the namespace `MODX\CLI\` mapped to the `src/` directory.
  - Development: PSR-4 autoloading for the namespace `MODX\CLI\Tests\` mapped to the `tests/` directory.
  - MODX Revolution: PSR-4 autoloading for the namespace `MODX\Revolution\` mapped to the `vendor/modx/revolution/core/src/Revolution/` directory.
- **Executable**: The CLI entry point is `bin/modx`.

## Technical constraints
- Requires PHP version `>=7.4`.
- Relies on Symfony components (`^5.4`), which may limit compatibility with newer Symfony versions.
- Development and testing require `phpunit/phpunit` version `^9.5`.
- Must be compatible with MODX Revolution 3.x, which has its own set of dependencies and requirements.
- Must handle different operating systems and file system structures.

## Dependencies
- **Production**:
  - `symfony/console`: Provides CLI tools.
  - `symfony/process`: Manages system processes.
  - `symfony/finder`: Handles file system operations.
- **Development**:
  - `phpunit/phpunit`: Used for unit testing.
  - `modx/revolution`: Used for development and testing against MODX 3.x.
  - `phpspec/prophecy`: Used for mocking in tests.

## Testing Approach
- Unit tests are written using PHPUnit.
- Mock objects are used to simulate MODX components without requiring an actual MODX installation.
- Tests are organized to mirror the structure of the source code.
- The test suite can be run with `vendor/bin/phpunit`.
