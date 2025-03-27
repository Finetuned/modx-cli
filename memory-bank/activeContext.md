# Active Context

## Current work focus

- Adding a --json option to all CLI commands that return data
- Implementing SSH functionality and aliases for remote command execution
- Fixing unit test failures and ensuring all tests pass
- Improving compatibility with MODX Revolution 3.x
- Enhancing the Configuration classes to properly interact with MODX system settings
- Ensuring proper namespace usage throughout the codebase

## Recent changes

- Standardized command naming convention:
  - Updated all commands using `:getlist` to use `:list` instead for consistency
  - Updated 22 command classes to use the new naming convention
  - Updated the comment in `ListProcessor.php` to reflect the new naming convention
  - Updated the test in `ApplicationTest.php` to check for the new command name format
  - Verified that all tests pass and `modx list` shows the updated command names

- Implemented SSH functionality and aliases for remote command execution:
  - Added the --ssh option to BaseCmd to allow running commands on remote servers
  - Created SSH connection string parser to handle the format [<user>@]<host>[:<port>][<path>]
  - Implemented command proxying to execute commands on remote servers via SSH
  - Added support for SSH config aliases from ~/.ssh/config
  - Created YAML-based configuration system for defining aliases
  - Added support for alias groups to run commands on multiple servers
  - Created documentation and examples for SSH and alias usage

- Fixed issues with the --json option implementation:
  - Updated ProcessorCmd::getOptions() to call parent::getOptions() and merge the results
  - This ensures that all commands that extend ProcessorCmd inherit the --json option from BaseCmd
  - Now all commands properly support the --json option for machine-readable output
  - Added unit tests for the --json option to all Get command test files

- Fixed unit test failures in ComponentTest.php related to the modSystemSetting class:
  - Updated mock builders to use the fully qualified class name or class reference (modSystemSetting::class)
  - Added disableOriginalConstructor() to mock builders to prevent constructor issues
  - Updated the modCacheManager mock to use the fully qualified class name
  - Enhanced the Component class implementation to match test expectations

- Enhanced the Component class to:
  - Accept items in the constructor
  - Load settings from MODX when available
  - Save settings to MODX system settings
  - Ensure items are empty when no MODX instance is available

## Next steps

- Fixed issue with `modx list` command not showing the global options added in Tasks 5 and 6:
  - Updated the Application class to add the `--json` and `--ssh` options to the default input definition
  - Now all commands properly show these global options in the command list and help text

- Fix remaining test failures in other test files:
  - ExtensionTest.php has 4 failures related to array handling and formatting
  - InstanceTest.php has 4 failures related to configuration handling
  
- Add an internal API like WP-CLI
- Implement self-update functionality
- Add TAB completions
- Add missing CRUD commands for context and source
- Add ASCII art version of the MODX logo similar to that shown when running Composer
- Enhance SSH functionality with additional features:
  - Add support for SSH key authentication options
  - Implement connection pooling for better performance
  - Add support for environment variable passing

## Active decisions and considerations

- Namespace handling: Ensuring proper use of namespaces throughout the codebase, especially when dealing with MODX Revolution 3.x classes
- Mock objects in tests: Using proper mocking techniques to avoid requiring an actual MODX installation
- Configuration storage: Balancing between file-based configuration and MODX system settings
- Compatibility: Maintaining compatibility with different MODX versions while leveraging new features
