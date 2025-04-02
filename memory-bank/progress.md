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
- Implement self-update functionality
- Add TAB completions
- Add missing CRUD commands for context and source
- Add ASCII art version of the MODX logo

## Current status

The project is around 35-40% complete. The basic structure, many core commands, SSH functionality, and the internal API are implemented and working. Unit tests have been created for most components, including the internal API. There are still several features to add and improvements to make, but the project is making good progress.

## Known issues

### Fixed Issues

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

- crawl: raises an error
- ns:list does not return a list of namespaces
- ns:create does not create a namespace
- ns:update cannot be tested until ns:list and ns:create are fixed
- ns:remove cannot be tested until ns:list and ns:create are fixed
- ✅ extra:list does not show version numbers (fixed by implementing better package matching)
- tv:update --description "test description" 6 raises an error: name : tv_err ns_name

### Namespace Issues

- Some classes are not properly using namespaces, especially when interacting with MODX Revolution 3.x classes
- Mock objects in tests need to be updated to use the correct namespaces
