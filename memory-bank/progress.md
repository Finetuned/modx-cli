# Progress

## What works

- Basic CLI structure and command registration
- Configuration management
- Many MODX element commands (Category, Chunk, Snippet, Template, TV)
- System commands (clear cache, info, refresh URIs)
- Package management commands
- User management commands
- Resource commands
- JSON output for all commands via the --json option
- SSH functionality and aliases for remote command execution
- Internal API for extending and customizing the CLI
- Unit tests for core components and the internal API

## What's left to build

- ✅ Add a --json option to all commands that return data
- ✅ Add --ssh functionality like the WP-CLI
- ✅ Standardize command naming convention (`:getlist` to `:list`)
- ✅ Add an internal API like WP-CLI
- ✅ Add launch.json to manually debug commands
- ✅ Task 11 - Package Upgrade Custom Commands using internal API
- Implement self-update functionality
- Add TAB completions
- Add missing CRUD commands for context and source
- Add ASCII art version of the MODX logo

## Current status

The project is around 40-45% complete. The basic structure, many core commands, SSH functionality, the internal API, and Task 11 package upgrade commands are implemented and working. Unit tests have been created for most components, including the internal API and custom commands. The TDD approach used for Task 11 has established a solid foundation for future custom command development. There are still several features to add and improvements to make, but the project is making excellent progress.

## Task 11 Implementation Details

**Completed using Test-Driven Development (TDD) approach:**

### Critical Issues Resolved
- **Argument Conflict Fix:** Resolved "An argument with name 'command' already exists" error
  - Root cause: ClosureCommand manually adding 'command' argument conflicting with Symfony Console
  - TDD approach: Wrote failing test `testClosureCommandDoesNotAddCommandArgument()` first
  - Implementation: Removed manual argument handling from ClosureCommand class
  - Result: All 57 tests passing with 149 assertions

### Architectural Improvements
- **Integrated Command Naming:** Replaced problematic parallel hierarchy
  - Before: `package:upgrade:list`, `package:upgrade:download` (parallel namespace)
  - After: `package:list-upgrades`, `package:download` (integrated namespace)
  - Benefits: Eliminates namespace pollution, improves discoverability, consistent UX

### Real Provider Integration
- **Replaced Placeholder Code:** Implemented actual MODX provider querying
  - `getRemoteVersionsForPackage()` now uses `workspace/packages/providers/packages` processor
  - Filters versions to show only newer than currently installed
  - Provides downloadable signatures and comprehensive metadata
  - Includes provider name resolution and robust error handling

### Comprehensive Test Coverage
- **4 Test Files Created:** Full coverage of functionality
  - `ClosureCommandTest.php` - 11 tests, 14 assertions (argument conflict fix)
  - `IntegratedPackageUpgradeTest.php` - 5 tests, 40 assertions (integration tests)
  - `CustomPackageUpgradeTest.php` - 9 tests, 27 assertions (functionality tests)
  - `CLIIntegrationTest.php` - 4 tests, 44 assertions (real CLI verification)

### Production-Ready Features
- **Enhanced User Experience:** Table/JSON output, filtering, dry-run mode
- **Extensible Architecture:** YAML configuration system for future custom commands
- **No Core Modifications:** Uses internal API, commands stored outside phar
- **Full Backward Compatibility:** Works alongside existing commands seamlessly

## Known issues

### Fixed Issues

- Fixed test failures in API tests:
  - Fixed the CommandRunner class to always return 0 for success when the 'return' option is not set
  - Updated the MODX_CLI class to use a colon separator instead of an underscore in hook names (e.g., "before_invoke:command" instead of "before_invoke_command")
  - Modified CommandRunnerTest to handle the return value correctly
  - Updated MODX_CLITest to use assertStringContainsString for colored output tests
  - Fixed hook-related tests to match the new hook naming convention
  - All 291 tests are now passing successfully

- Fixed test failures in RunSequenceTest.php:
  - Added a 'command' argument to the RunSequence command to properly define the expected command argument
  - Modified the CommandRunner class to check if the 'command' key already exists in the arguments before adding it
  - Updated both the call() and callSilent() methods in BaseCmd.php to check if the 'command' key already exists
  - Updated the RunSequence.php file to properly return an integer error code when there are no command sets
  - Improved test mocking to properly mock the CommandRunner dependency
  - All 11 tests in RunSequenceTest.php now pass successfully

- Fixed version display issue in `extra:list` command:
  - Implemented a more robust approach to match extras (namespaces) with their corresponding packages
  - Added a method to get all packages using the same processor as `package:list` to create a lookup table
  - Created a method to find the correct package for a namespace using multiple matching strategies
  - Added fallback mechanisms for when the processor fails or no match is found
  - Now `extra:list` displays version numbers similar to `package:list`

- Fixed inheritance issue with the --json option:
  - ProcessorCmd was overriding getOptions() without calling parent::getOptions()
  - Updated ProcessorCmd::getOptions() to merge parent options with its own options
  - Now all commands properly support the --json option for machine-readable output
  - Added unit tests for the --json option to all Get command test files

- Fixed issue with `modx list` command not showing global options:
  - Updated the Application class to add the `--json` and `--ssh` options to the default input definition
  - Now all commands properly show these global options in the command list and help text

### Unit Test Failures

- Fixed issues in ComponentTest.php:
  - Updated mock builders to use the fully qualified class name or class reference (modSystemSetting::class)
  - Added disableOriginalConstructor() to disable constructor issues
  - Updated the modCacheManager mock to use the fully qualified class name
  - Enhanced the Component class implementation to match test expectations

- Remaining test failures:
  - ExtensionTest.php has 4 failures related to array handling and formatting
  - InstanceTest.php has 4 failures related to configuration handling

- Fixed test failures in ExtensionTest.php and InstanceTest.php:
  - Added a `$loadExisting` parameter to constructors to skip loading existing configuration during tests
  - Fixed array handling in Extension class to properly handle class names as values
  - Implemented proper `remove()` method in Extension class to handle array values
  - Made `formatConfigurationData()` method public in Instance class
  - Updated tests to use the new constructor parameter

- Remaining test failures:
  - ComponentTest.php has errors related to missing MODX classes (modX)

### Command Issues

#### Fixed Issues
- ✅ chunk:update requires --name to update other fields (FIXED: Now pre-populates existing data)
- ✅ tv:update requires --name to be passed as well as the id in order to update other fields (FIXED: Now pre-populates existing data)
- ✅ snippet:update requires --name to be passed as well as the id in order to update other fields (FIXED: Now pre-populates existing data)
- ✅ template:update requires --name to be passed as well as the id in order to update other fields (FIXED: Now pre-populates existing data)
- ✅ resource:update requires --name to be passed as well as the id in order to update other fields (FIXED: Now pre-populates existing data)
- ✅ resource:create ignores published argument (FIXED: Now properly applies default values and handles boolean conversion)
- ✅ crawl: raises an error (FIXED: Added proper error handling and cURL validation)
- ✅ ns:list does not return a list of namespaces (FIXED: Enhanced response handling for different processor formats)
- ✅ package:list needs a way to page through the list (FIXED: Added --limit and --start options to all list commands)
- ✅ extra:list does not show version numbers (fixed by implementing better package matching)

#### Unit Tests Updated
- ✅ Updated all update command tests to reflect the new pre-population functionality
- ✅ Added tests for non-existent object handling (proper error messages)
- ✅ Created tests for ProcessorCmd helper methods (prePopulateFromExisting, applyDefaults, etc.)
- ✅ Added tests for ListProcessor pagination functionality
- ✅ Fixed test setup to avoid Application class conflicts
- ✅ All enhanced functionality tests are now passing

#### Pagination Conflict Resolution
- ✅ **FIXED: "An option named 'limit' already exists" error**
- ✅ Implemented smart pagination in ListProcessor base class that detects existing options
- ✅ Removed duplicate pagination options from individual list commands (Snippet/GetList.php, System/Snippet/GetList.php)
- ✅ Changed --start option shortcut from 's' to none to avoid conflict with --ssh option
- ✅ All list commands now have consistent pagination: --limit (-l) and --start options
- ✅ Commands that didn't have pagination (like chunk:list) now have it automatically
- ✅ Commands that already had pagination (like snippet:list) work without conflicts
- ✅ All tests updated and passing

#### Remaining Issues
- ✅ **FIXED: resource:update null classKey error** - Enhanced field mapping and added safety defaults for critical fields
- resource:update passing --pagetitle string -- id returns: "Too many arguments to "resource:update" command, expected arguments "id"." (Needs investigation of argument parsing)
- resource:purge did not purge the resource and afterwards resource:remove did not work again in the session
- ns:create does not create a namespace (Needs testing after ns:list fix)
- ns:update cannot be tested until ns:list and ns:create are fixed
- ns:remove cannot be tested until ns:list and ns:create are fixed
- category:get returns an empty string if int not found (Already has proper error handling)
- package:provider:info returns empty string
- package:provider:packages returns empty string
- package:provider:categories returns empty string
- package:upgradeable returns upgradeable packages but does not include the signature of the upgrade
- package:install does not fetch the package to be installed. It probably should not so we are missing functionality i.e. package:download 
- plugin:disabled shows the same as plugin:list
- session:list returns empty string
- session:flush does not flush the session
- session:remove does not delete the session
- system:log:actions:list needs a way to page through results (FIXED: Added pagination options)
- user:resetpassword returns username and email are required. Adding either return option does not exist.

### Debugging issues
- the launch.json created to manually debug commands using VS Code causes an error as the command parameters are evaluated too early: Symfony (ArgvInput) is evaluating the arguments prior to executing the command. As the args are for the specific command, it is failing as the args are not found in the base call to bin/modx
### Namespace Issues

- Some classes are not properly using namespaces, especially when interacting with MODX Revolution 3.x classes
- Mock objects in tests need to be updated to use the correct namespaces
