# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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
