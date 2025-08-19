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

## MODX Core Source Code Patterns

**Critical Note**: Development frequently requires reading MODX core source code to understand processor structures, API patterns, and implementation details.

### Core Source Location
- **Primary Path**: `/Users/julianweaver/Sites/commonplace/core/src/Revolution/`
- **Processor Base Path**: `core/src/Revolution/Processors/`

### Key Processor Directory Structure
- **`Processors/Workspace/Packages/`** - Local package operations (Get.php, GetList.php, Install.php, etc.)
- **`Processors/Workspace/Packages/Rest/`** - Remote provider queries (GetList.php, Download.php, GetInfo.php)
- **`Processors/Workspace/Providers/`** - Provider management (GetList.php, Create.php, Update.php)
- **`Processors/Model/`** - Base processor classes (GetListProcessor.php)

### Common Processor Patterns
1. **Naming Convention**: Processors follow `workspace/path/action` pattern
2. **Class Structure**: Extend base processor classes with specific functionality
3. **Parameter Handling**: Use `getProperty()` method for input parameters
4. **Response Format**: Return structured arrays with `results` and metadata
5. **Error Handling**: Use `isError()` and `getMessage()` for error checking

### Debugging Context
When troubleshooting processor calls:
1. **Verify Processor Existence**: Check actual file paths in core source
2. **Examine Parameter Requirements**: Read processor `initialize()` and `process()` methods
3. **Understand Response Structure**: Check `prepareRow()` and output formatting
4. **Trace Processor Inheritance**: Follow class hierarchy for base functionality

### Common Processor Corrections
- **Provider Queries**: Use `workspace/providers/getlist` not `workspace/packages/providers/get`
- **Remote Packages**: Use `workspace/packages/rest/getlist` not `workspace/packages/providers/packages`
- **Package Lists**: Use `workspace/packages/getlist` for local package operations
