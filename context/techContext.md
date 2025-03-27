# Tech Context

## Technologies used
- **PHP**: The project requires PHP version `>=7.4`.
- **Symfony Components**: Utilized for CLI tools, process management, and file system utilities.

## Development setup
- **Project Name**: `modx/cli`
- **Description**: MODX 3 CLI application.
- **License**: MIT
- **Authors**: Developed by Finetuned Limited.
- **Autoloading**:
  - Production: PSR-4 autoloading for the namespace `MODX\CLI\` mapped to the `src/` directory.
  - Development: PSR-4 autoloading for the namespace `MODX\CLI\Tests\` mapped to the `tests/` directory.
- **Executable**: The CLI entry point is `bin/modx`.

## Technical constraints
- Requires PHP version `>=7.4`.
- Relies on Symfony components (`^5.4`), which may limit compatibility with newer Symfony versions.
- Development and testing require `phpunit/phpunit` version `^9.5`.

## Dependencies
- **Production**:
  - `symfony/console`: Provides CLI tools.
  - `symfony/process`: Manages system processes.
  - `symfony/finder`: Handles file system operations.
- **Development**:
  - `phpunit/phpunit`: Used for unit testing.