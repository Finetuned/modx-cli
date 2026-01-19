# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.8.0-beta] - 2026-01-19

### Added
- Completed user management command set and JSON output parity (Task 20).
- Integration and unit test suite compliance updates (Task 21), including test helper splits and namespace fixes.

### Fixed
- Version command now exposes CLI version in JSON output for integration runs.
- PHPCS/PHPStan cleanup across commands, configuration, and test helpers tied to Tasks 20/21.

## [0.7.2-beta] - 2026-01-14

### Fixed
- Fixed all skipping unit and integration tests

## [0.7.1-beta] - 2026-01-12

### Added
- `self-update` command for Phar installs with release checks, download/verification, and safe replacement workflow.

## [0.7.0-beta] - 2026-01-12

### Added
- Beta release readiness milestone (unit and integration suites green with `MODX_INTEGRATION_TESTS=1`).
- Comprehensive parameter and integration coverage across command groups (Task 14 + Task 15 completion).
- Total test count: 2978 tests (unit + integration).

### Fixed
- ResetPassword unit tests now mock Profile access consistently.
- MODX_CLI registry lazy-init to avoid null registry crashes in Application integration tests.
- Integration user list helper now uses a single PDO connection for insert IDs.

## [0.6.0-alpha] - 2025-11-21

### Added
- **Complete Test Isolation Infrastructure**:
  - Separated integration tests into dedicated `tests/Integration/` directory
  - Created separate bootstrap files (`tests/bootstrap.php` for unit, `tests/Integration/bootstrap.php` for integration)
  - Separate PHPUnit configuration (`phpunit.integration.xml`) for integration tests
  - Environment-based test skipping with `MODX_INTEGRATION_TESTS` flag
  - Total test count: 944 tests (758 unit + 186 integration) with 100% pass rate
- **Enhanced Logging System**:
  - Comprehensive logging infrastructure with configurable log levels
  - Logger level thresholds for better control
  - Enhanced error tracking and debugging capabilities
- **Plugin Architecture Foundation**:
  - Extensible plugin system for future enhancements
  - Hook-based architecture for command extensibility
- **Command Output Streaming**:
  - Improved real-time output handling
  - Better progress reporting for long-running operations
- **Documentation Enhancements**:
  - Code quality infrastructure documentation
  - Project planning documentation
  - Enhanced debugging setup guides
  - Comprehensive test suite documentation in `tests/Integration/README.md`

### Enhanced
- **Code Quality & Type Safety**:
  - **PHPStan Integration**: Reduced from 218 errors to ZERO errors
  - Added explicit return type declarations across all `processResponse()` implementations
  - Normalized MODX class lookups to class-string constants
  - Type-safe ID casting in `prePopulateFromExisting()` methods
  - Comprehensive type annotations throughout codebase
- **Test Infrastructure**:
  - Complete isolation of unit and integration tests (resolved all "Cannot declare class modX" errors)
  - Integration tests conditionally load real `modX` from `MODX_TEST_INSTANCE_PATH`
  - Unit tests use stub/mock `modX` for isolation
  - Database reachability guards for graceful test skipping
  - Dynamic table name support for custom table prefixes
  - Improved fixture management and cleanup
- **ListProcessor Improvements**:
  - Enhanced response handling for processors without explicit `total` field
  - Defaults to result count when total unavailable
  - Prevents undefined index errors in list responses
- **Configuration Management**:
  - Guarded YAML configuration loads to prevent scalar merge errors
  - Better error handling during bootstrap configuration
  - Improved handling of component configuration saves

### Fixed
- **Resource Commands**:
  - `resource:purge` renamed to `resource:erase` (aligned with MODX UI conventions)
  - `resource:remove` renamed to `resource:delete` (moves to trash)
  - `resource:erase` now uses correct processor path (`Resource\\Trash\\Purge`)
  - Pre-condition checks ensure resource must be in trash before erasing
  - Proper `ids` parameter passing (comma-separated list)
  - Added 24 comprehensive tests for delete/erase operations
- **Namespace Commands** (Complete Fix):
  - `ns:list` - Fixed response handling, now properly displays all namespaces with IDs
  - `ns:create` - Enhanced path/assets_path handling
  - `ns:update` - Individual field updates working correctly
  - `ns:remove` - Proper validation and confirmation flow
  - Added 44 tests with 72 assertions across 4 test files
  - 100% test pass rate for all namespace operations
- **Plugin Commands**:
  - `plugin:disabled` - Fixed `beforeRun()` to properly pass `disabled => 1` parameter
  - MODX processor now correctly filters disabled plugins
  - Added comprehensive unit tests for disabled plugin functionality
- **Package Provider Commands**:
  - `package:provider:info` - Now uses correct `Workspace\\Providers\\GetList` processor
  - `package:provider:packages` - Uses `Workspace\\Packages\\Rest\\GetList` processor
  - `package:provider:categories` - Uses `Workspace\\Packages\\Rest\\GetNodes` processor
  - Fixed JSON output handling for all provider commands
- **System Commands**:
  - `system:log:actions:list` - Enhanced pagination and filtering capabilities
  - Better parameter handling and error reporting
- **User Commands**:
  - Added `user:reset-password` to custom commands configuration
  - Proper command registration and functionality
- **Integration Test Fixes**:
  - Application instantiation guards to prevent boot failures when MODX unavailable
  - Early `setUp()` skip in package upgrade tests for missing MODX configuration
  - Graceful handling of missing test database connections
  - Fixed class redeclaration issues across all test suites
  - Proper namespace handling (`MODX\\CLI\\Tests\\Integration`)

### Changed
- **Test Suite Organization**:
  - Unit tests remain in `tests/` directory with `tests/bootstrap.php`
  - Integration tests moved to `tests/Integration/` with dedicated bootstrap
  - Separate execution: `./vendor/bin/phpunit` (unit) vs `MODX_INTEGRATION_TESTS=1 ./vendor/bin/phpunit -c phpunit.integration.xml` (integration)
  - Complete isolation prevents class conflicts and enables CI/CD readiness
- **PHPStan Configuration**:
  - Removed non-existent configuration items
  - Updated to stricter type checking levels
  - Enhanced code quality standards enforcement

### Performance
- **Improved Bootstrap Performance**:
  - Conditional loading of MODX classes based on test context
  - Reduced unnecessary class instantiation in unit tests
  - Optimized configuration loading during test initialization

## [0.5.0-alpha] - 2025-11-10

### Added
- Integration Testing Infrastructure: 
  - 161 integration tests with 96.9% pass rate (155/160 passing)
  - Real CLI command execution via Symfony Process
  - Database state verification with direct SQL queries
  - JSON output validation for all CRUD operations
  - Test instance isolation with alias-based routing
  - Comprehensive fixture management system
  - Docker-based test environment setup
  - BaseIntegrationTest class with helper methods
  - Environment-aware test skipping (MODX_INTEGRATION_TESTS flag)
  - Automated cleanup in tearDown() for test isolation
- PHPUnit test suite configuration enhancements
  - Added "all" test suite to run both unit and integration tests (854 total tests)
  - Renamed "Integration" to "integration" for consistency
  - Created comprehensive test suite documentation
- Documentation improvements
  - Added `docs/running-tests.md` with complete testing guide
  - Documented test suite structure and usage
  - Added troubleshooting section for common test issues

### Fixed
- Resolved "Class 'modX' not found" error during coverage report generation
- Created `tests/bootstrap.php` with minimal `modX` mock for coverage analysis
- Coverage reports now generate successfully (Clover XML, HTML, Text)
- Integration tests can now generate full code coverage metrics

## [0.5.0-alpha] - 2025-11-10



## [0.4.0-alpha] - 2025-10-23

### Added
- Package Upgrade Custom Commands using Internal API:
  - `package:list-upgrades` - List packages with available upgrades
  - `package:list-remote` - List remote versions for packages
  - `package:download` - Download packages from providers
  - `package:upgrade-all` - Complete workflow for upgrading all packages
  - Reusable helper functions for package management
  - YAML configuration system for custom commands
  - Bootstrap integration for external custom commands
- Comprehensive Test Coverage:
  - 812 tests across all test suites with 100% pass rate
  - 44 test files created in Command directory
  - Coverage infrastructure with Xdebug v3.3.1
  - HTML, Text, and Clover XML coverage reports
  - 65.34% overall coverage, ~75% effective coverage
  - 90-100% coverage on business logic and API components

### Enhanced
- Internal API improvements:
  - ClosureCommand argument conflict resolution
  - CommandPublisher enhancements
  - CommandRegistry improvements
  - HookRegistry functionality
- Package management workflow:
  - Optional location parameter for efficient downloads
  - Exact signature matching in fallback logic
  - Package filtering via `--packages` option
  - Dry-run mode for testing
  - Force mode to skip confirmations
  - Interactive confirmation prompts
  - Comprehensive error handling and progress reporting
  - Summary report with success/failure counts

### Fixed
- Argument conflict fix in ClosureCommand class
- Package download provider context issue
- Integration command naming conventions
- Test infrastructure improvements

## [0.3.0-alpha] - 2025-08-12

### Added
- Major infrastructure improvements:
- Enhanced test infrastructure
  - Core infrastructure testing (Application, CommandRegistrar, TreeBuilder, Xdom)
  - SSH and alias functionality testing
  - Essential command categories testing
  - Formatter and utility components testing
- Command pre-population functionality:
  - Update commands now pre-populate existing data
  - Enhanced field mapping with safety defaults
  - Proper error handling for non-existent objects
- Smart pagination system:
  - Automatic pagination detection in ListProcessor
  - Consistent --limit and --start options across all list commands
  - Conflict resolution for duplicate options

### Enhanced
- ProcessorCmd improvements
  - Helper methods for pre-population and defaults
  - Better error handling and validation
  - Improved field mapping
- ListProcessor enhancements
  - Smart pagination detection
  - Automatic option management
  - Enhanced response handling
- Command option inheritance
  - Fixed --json option inheritance
  - Global options properly displayed in help text

### Fixed
- chunk:update now pre-populates existing data
- tv:update now pre-populates existing data
- snippet:update now pre-populates existing data
- template:update now pre-populates existing data
- resource:update now pre-populates existing data
- resource:create properly applies default values
- resource:update null classKey error
- crawl command error handling
- ns:list enhanced response handling
- extra:list version display
- Pagination conflict resolution ("An option named 'limit' already exists")
- --start option shortcut changed to avoid --ssh conflict

## [0.2.0-alpha] - 2025-06-03

### Added
- Enhanced package management and testing
- JSON output support:
  - Added --json option to all commands that return data
  - Machine-readable output format
  - Proper inheritance in ProcessorCmd
- SSH functionality:
  - Remote command execution support
  - Alias system for MODX instances
  - Connection parser and handler
  - SSH command proxy
- Command naming standardization:
  - Migrated from `:getlist` to `:list` convention
  - Consistent command naming across all categories
- Internal API:
  - CommandPublisher for registering commands
  - CommandRegistry for managing command lifecycle
  - HookRegistry for extensibility
  - ClosureCommand for simple command creation
  - CommandRunner for executing commands programmatically

### Enhanced
- Configuration management improvements
- Package management commands
- User management commands
- Resource commands
- System commands enhancements

### Fixed
- CommandRunner return value handling
- Hook naming convention (colon separator)
- RunSequence command argument handling
- Various test failures in API components

## [0.1.0-alpha] - 2025-01-03

### Added
- Initial release of the MODX 3 CLI
- Support for MODX 3.0.0 and higher
- Command system based on Symfony Console
- Configuration system for managing MODX instances
- Basic commands:
  - `version` - Display the CLI version
  - `system:info` - Get general system information
  - `system:clearcache` - Clear the MODX cache
  - `resource:getlist` - Get a list of resources
- Bash completion script
- Support for building a PHAR file
