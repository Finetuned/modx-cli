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

## What's left to build

- ✅ Add a --json option to all commands that return data
- ✅ Add --ssh functionality like the WP-CLI
- ✅ Standardize command naming convention (`:getlist` to `:list`)
- Add an internal API like WP-CLI
- Implement self-update functionality
- Add TAB completions
- Add missing CRUD commands for context and source
- Add ASCII art version of the MODX logo

## Current status

The project is around 25-30% complete. The basic structure and many core commands are implemented and working, but there are still several features to add and improvements to make.

## Known issues

### Fixed Issues

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
  - Added disableOriginalConstructor() to mock builders to prevent constructor issues
  - Updated the modCacheManager mock to use the fully qualified class name
  - Enhanced the Component class implementation to match test expectations

- Remaining test failures:
  - ExtensionTest.php has 4 failures related to array handling and formatting
  - InstanceTest.php has 4 failures related to configuration handling

### Command Issues

- crawl: raises an error
- ns:list does not return a list of namespaces
- ns:create does not create a namespace
- ns:update cannot be tested until ns:list and ns:create are fixed
- ns:remove cannot be tested until ns:list and ns:create are fixed
- extra:list does not show version numbers (package:list does display version numbers)
- tv:update --description "test description" 6 raises an error: name : tv_err ns_name

### Namespace Issues

- Some classes are not properly using namespaces, especially when interacting with MODX Revolution 3.x classes
- Mock objects in tests need to be updated to use the correct namespaces
