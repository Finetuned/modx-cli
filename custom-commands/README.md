# Custom Commands

This directory contains the custom commands system for MODX CLI. Custom commands are defined in YAML configuration files and automatically loaded at runtime.

## Structure

```
custom-commands/
├── config.yml                     # Main configuration file
├── package-upgrade-functions.php  # PHP functions for package upgrade commands
└── README.md                      # This file
```

## Configuration Format

The `config.yml` file defines custom commands using the following structure:

```yaml
custom_commands:
  # Command group name
  package_upgrade:
    functions_file: "package-upgrade-functions.php"
    commands:
      command:name:
        function: functionName
        description: "Command description"
        arguments:
          - name: arg_name
            required: true
            description: "Argument description"
        options:
          - name: option_name
            description: "Option description"
            default: default_value
```

## Available Commands

### Package Upgrade Commands

The following package upgrade commands are available:

#### `package:upgrade:list`
Lists downloaded package upgrades ready for installation.

**Options:**
- `--filter`: Filter packages by name pattern
- `--format`: Output format (table, json)

**Example:**
```bash
php bin/modx package:upgrade:list
php bin/modx package:upgrade:list --filter=pdotools --format=json
```

#### `package:upgrade:list-remote`
Retrieves all available versions after the installed version from providers.

**Options:**
- `--package`: Filter by specific package name
- `--provider`: Filter by specific provider
- `--format`: Output format (table, json)

**Example:**
```bash
php bin/modx package:upgrade:list-remote
php bin/modx package:upgrade:list-remote --package=pdotools
```

#### `package:upgrade:download`
Downloads specific package versions to core/packages.

**Arguments:**
- `signature`: Package signature to download (e.g., pdotools-3.0.2-pl)

**Options:**
- `--force`: Overwrite existing downloads
- `--verify`: Verify download integrity

**Example:**
```bash
php bin/modx package:upgrade:download pdotools-3.0.2-pl
php bin/modx package:upgrade:download pdotools-3.0.2-pl --force
```

#### `package:upgrade:all`
Orchestrates the complete upgrade workflow.

**Options:**
- `--packages`: Comma-separated list of packages to upgrade
- `--dry-run`: Show what would be upgraded without doing it
- `--force`: Skip confirmation prompts
- `--async`: Download packages asynchronously
- `--backup`: Create backup before upgrading

**Example:**
```bash
php bin/modx package:upgrade:all --dry-run
php bin/modx package:upgrade:all --packages=pdotools,migx --force
```

## How It Works

1. **Bootstrap Loading**: The `src/bootstrap.php` file automatically loads custom commands during application startup
2. **YAML Parsing**: Uses Symfony YAML component to parse the configuration
3. **Function Loading**: Loads PHP function files specified in the configuration
4. **Command Registration**: Registers commands with the MODX CLI internal API using `MODX_CLI::add_command()`

## Adding New Commands

To add new custom commands:

1. **Create a PHP functions file** with your command logic
2. **Add the command group to `config.yml`** with the functions file and command definitions
3. **Commands are automatically loaded** on next CLI execution

### Example Function

```php
function myCustomCommand($args, $assoc_args)
{
    // Get MODX instance
    $app = new \MODX\CLI\Application();
    $modx = $app->getMODX();
    
    if (!$modx) {
        MODX_CLI::error('MODX instance not available');
        return 1;
    }
    
    // Your command logic here
    MODX_CLI::log('Hello from custom command!');
    
    return 0; // Success
}
```

### Example Configuration

```yaml
custom_commands:
  my_feature:
    functions_file: "my-feature-functions.php"
    commands:
      my:custom:command:
        function: myCustomCommand
        description: "My custom command"
        options:
          - name: example
            description: "Example option"
```

## Testing

Custom commands can be tested using PHPUnit. See `tests/Command/Package/Upgrade/CustomPackageUpgradeTest.php` for examples.

## Benefits

- **No core modifications**: Commands are loaded externally
- **YAML configuration**: Easy to read and maintain
- **Automatic loading**: No manual registration required
- **Extensible**: Easy to add new command groups
- **Testable**: Full PHPUnit test support
