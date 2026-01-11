# Plugin Architecture

## Overview

The MODX CLI Plugin Architecture provides a powerful and flexible system for extending the CLI with custom functionality. Plugins can register commands, listen to hooks (events), and integrate seamlessly with the core application.

**Key Features:**
- Automatic plugin discovery and loading
- Hook system for event-driven extensions
- Configuration management per plugin
- Enable/disable functionality
- Version requirement checking
- PSR-compliant interfaces

## Quick Start

### Creating a Plugin

Create a plugin by implementing the `PluginInterface` or extending `AbstractPlugin`:

```php
<?php
// plugins/example-plugin/Plugin.php

namespace MyVendor\ExamplePlugin;

use MODX\CLI\Plugin\AbstractPlugin;
use MODX\CLI\Application;

class Plugin extends AbstractPlugin
{
    public function getName(): string
    {
        return 'example-plugin';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getDescription(): string
    {
        return 'An example plugin demonstrating MODX CLI plugin capabilities';
    }

    public function getAuthor(): string
    {
        return 'Your Name';
    }

    public function getCommands(): array
    {
        return [
            \MyVendor\ExamplePlugin\Command\ExampleCommand::class
        ];
    }

    public function getHooks(): array
    {
        return [
            'command.before' => [$this, 'onCommandBefore'],
            'command.after' => [$this, 'onCommandAfter']
        ];
    }

    public function onCommandBefore(array $context): array
    {
        // Do something before command execution
        $this->getApplication()->getLogger()->info('Command starting: {command}', [
            'command' => $context['command'] ?? 'unknown'
        ]);

        return $context;
    }

    public function onCommandAfter(array $context): array
    {
        // Do something after command execution
        return $context;
    }
}
```

### Plugin Directory Structure

```
plugins/
  └── example-plugin/
      ├── Plugin.php           # Main plugin class (required)
      ├── Command/             # Plugin commands
      │   └── ExampleCommand.php
      └── composer.json        # Optional: dependencies
```

### Installing a Plugin

1. **Project-local plugins**: Place in `.modx-cli/plugins/` directory (from your project root)
2. **Global plugins**: Place in CLI installation `plugins/` directory
3. **Composer packages**: Install as dependencies and register the plugin path

Plugins are discovered and loaded automatically when the CLI starts.

## Architecture

### Core Components

1. **PluginInterface** - Defines the contract all plugins must implement
2. **AbstractPlugin** - Base class providing common functionality
3. **PluginManager** - Handles plugin discovery, loading, and lifecycle
4. **HookManager** - Manages event hooks and execution
5. **Application Integration** - Seamless integration with the CLI app

### Plugin Lifecycle

1. **Discovery** - PluginManager scans plugin directories
2. **Loading** - Plugin classes are instantiated and registered
3. **Validation** - Requirements (PHP/CLI version) are checked
4. **Configuration** - Plugin config is loaded and applied
5. **Initialization** - Plugin's `initialize()` method is called
6. **Registration** - Commands and hooks are registered
7. **Execution** - Plugin is ready and responds to events

## Plugin Interface

### Required Methods

```php
interface PluginInterface
{
    // Identity
    public function getName(): string;
    public function getVersion(): string;
    public function getDescription(): string;
    public function getAuthor(): string;

    // Requirements
    public function getMinPhpVersion(): string;
    public function getMinCliVersion(): string;

    // Lifecycle
    public function initialize(Application $app): void;

    // State
    public function isEnabled(): bool;
    public function enable(): void;
    public function disable(): void;

    // Configuration
    public function getConfig(): array;
    public function setConfig(array $config): void;

    // Extensions
    public function getCommands(): array;
    public function getHooks(): array;
}
```

### AbstractPlugin Methods

When extending `AbstractPlugin`, you get these additional helpers:

```php
protected function getConfigValue(string $key, mixed $default = null): mixed
protected function setConfigValue(string $key, mixed $value): void
protected function getApplication(): ?Application
```

## Hook System

### Available Hooks

The CLI fires hooks at key points in the application lifecycle:

| Hook Name | Context | Description |
|-----------|---------|-------------|
| `app.bootstrap` | `['app' => Application]` | Application bootstrapped |
| `command.before` | `['command' => string, 'input' => InputInterface]` | Before command execution |
| `command.after` | `['command' => string, 'result' => int]` | After command execution |
| `command.error` | `['command' => string, 'error' => Throwable]` | Command threw an exception |

### Registering Hooks

Return hook registrations from your plugin's `getHooks()` method:

```php
public function getHooks(): array
{
    return [
        'command.before' => [$this, 'onCommandBefore'],
        'command.after' => function($context) {
            // Anonymous function handler
            return $context;
        }
    ];
}
```

### Hook Handlers

Hook handlers receive a context array and can modify it:

```php
public function onCommandBefore(array $context): array
{
    // Read from context
    $commandName = $context['command'];

    // Modify context (changes passed to next handler)
    $context['start_time'] = microtime(true);

    return $context;
}
```

### Hook Priorities

Register hooks with priority (higher = executed first):

```php
$app->getHookManager()->register('command.before', $handler, 20); // High priority
$app->getHookManager()->register('command.before', $handler2, 10); // Default priority
$app->getHookManager()->register('command.before', $handler3, 5);  // Low priority
```

## Plugin Configuration

### Default Configuration

Define default configuration in your plugin:

```php
class Plugin extends AbstractPlugin
{
    protected array $config = [
        'enabled' => true,
        'option1' => 'default_value',
        'option2' => 100
    ];

    public function getConfig(): array
    {
        return $this->config;
    }
}
```

### User Configuration

Users can configure plugins via `.modx-cli/plugins.yaml` (project-local):

```yaml
example-plugin:
  enabled: true
  option1: custom_value
  option2: 200
```

### Accessing Configuration

```php
// In your plugin
$value = $this->getConfigValue('option1', 'fallback');
$this->setConfigValue('option2', 300);
```

## Adding Commands

### Registering Commands

Return command class names from `getCommands()`:

```php
public function getCommands(): array
{
    return [
        \MyVendor\ExamplePlugin\Command\ExampleCommand::class,
        \MyVendor\ExamplePlugin\Command\AnotherCommand::class
    ];
}
```

### Command Example

```php
<?php
namespace MyVendor\ExamplePlugin\Command;

use MODX\CLI\Command\BaseCmd;

class ExampleCommand extends BaseCmd
{
    protected $name = 'plugin:example';
    protected $description = 'Example plugin command';

    protected function process()
    {
        $this->info('Hello from the example plugin!');

        // Access logger
        $this->logInfo('Command executed');

        // Access hook manager
        $hooks = $this->getApplication()->getHookManager();

        return 0;
    }
}
```

## Examples

### Example 1: Logging Plugin

Track all command executions:

```php
class LoggingPlugin extends AbstractPlugin
{
    public function getName(): string
    {
        return 'command-logger';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getDescription(): string
    {
        return 'Logs all command executions to a file';
    }

    public function getHooks(): array
    {
        return [
            'command.before' => [$this, 'logCommandStart'],
            'command.after' => [$this, 'logCommandEnd'],
            'command.error' => [$this, 'logCommandError']
        ];
    }

    public function logCommandStart(array $context): array
    {
        $logFile = $this->getConfigValue('log_file', 'command.log');

        file_put_contents($logFile, sprintf(
            "[%s] START: %s\n",
            date('Y-m-d H:i:s'),
            $context['command']
        ), FILE_APPEND);

        $context['start_time'] = microtime(true);

        return $context;
    }

    public function logCommandEnd(array $context): array
    {
        $duration = microtime(true) - ($context['start_time'] ?? 0);
        $logFile = $this->getConfigValue('log_file', 'command.log');

        file_put_contents($logFile, sprintf(
            "[%s] END: %s (%.2fs, exit: %d)\n",
            date('Y-m-d H:i:s'),
            $context['command'],
            $duration,
            $context['result'] ?? 0
        ), FILE_APPEND);

        return $context;
    }

    public function logCommandError(array $context): array
    {
        $logFile = $this->getConfigValue('log_file', 'command.log');

        file_put_contents($logFile, sprintf(
            "[%s] ERROR: %s - %s\n",
            date('Y-m-d H:i:s'),
            $context['command'],
            $context['error']->getMessage()
        ), FILE_APPEND);

        return $context;
    }
}
```

### Example 2: Notification Plugin

Send notifications when commands complete:

```php
class NotificationPlugin extends AbstractPlugin
{
    public function getName(): string
    {
        return 'notifications';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getDescription(): string
    {
        return 'Sends notifications on command completion';
    }

    public function getHooks(): array
    {
        return [
            'command.after' => [$this, 'sendNotification']
        ];
    }

    public function sendNotification(array $context): array
    {
        $command = $context['command'];
        $result = $context['result'] ?? 0;

        // Only notify for long-running commands
        $duration = microtime(true) - ($context['start_time'] ?? microtime(true));
        if ($duration < 10) {
            return $context;
        }

        $message = $result === 0
            ? "✅ Command completed: {$command}"
            : "❌ Command failed: {$command}";

        // Send notification (example using a webhook)
        $webhook = $this->getConfigValue('webhook_url');
        if ($webhook) {
            file_get_contents($webhook . '?' . http_build_query([
                'message' => $message,
                'duration' => round($duration, 2)
            ]));
        }

        return $context;
    }
}
```

### Example 3: Custom Command Plugin

Add project-specific commands:

```php
class ProjectCommandsPlugin extends AbstractPlugin
{
    public function getName(): string
    {
        return 'project-commands';
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }

    public function getDescription(): string
    {
        return 'Project-specific custom commands';
    }

    public function getCommands(): array
    {
        return [
            \MyProject\Command\DeployCommand::class,
            \MyProject\Command\BackupCommand::class,
            \MyProject\Command\SyncCommand::class
        ];
    }
}
```

## Plugin Management

### Listing Plugins

```php
$pluginManager = $app->getPluginManager();

// Get all plugins
$allPlugins = $pluginManager->getPlugins();

// Get enabled plugins only
$enabledPlugins = $pluginManager->getEnabledPlugins();

// Get specific plugin
$plugin = $pluginManager->getPlugin('example-plugin');
```

### Enabling/Disabling Plugins

```php
// Enable a plugin
$pluginManager->enablePlugin('example-plugin');

// Disable a plugin
$pluginManager->disablePlugin('example-plugin');

// Check if enabled
if ($plugin->isEnabled()) {
    // Plugin is active
}
```

### Plugin Information

```php
$plugin = $pluginManager->getPlugin('example-plugin');

echo $plugin->getName();        // "example-plugin"
echo $plugin->getVersion();     // "1.0.0"
echo $plugin->getDescription(); // "An example plugin..."
echo $plugin->getAuthor();      // "Your Name"
```

## Best Practices

### 1. Use Descriptive Names

```php
// ✅ Good
public function getName(): string
{
    return 'backup-automation';
}

// ❌ Avoid
public function getName(): string
{
    return 'plugin1';
}
```

### 2. Specify Version Requirements

```php
public function getMinPhpVersion(): string
{
    return '8.0';  // Require PHP 8.0+
}

public function getMinCliVersion(): string
{
    return '1.0.0';  // Require CLI 1.0.0+
}
```

### 3. Use Semantic Versioning

```php
public function getVersion(): string
{
    return '1.2.3';  // MAJOR.MINOR.PATCH
}
```

### 4. Handle Errors Gracefully

```php
public function onCommandError(array $context): array
{
    try {
        // Your error handling
        $this->notifyAdmin($context['error']);
    } catch (\Throwable $e) {
        // Don't break the command - just log
        $this->getApplication()->getLogger()->warning(
            'Plugin error handler failed: {error}',
            ['error' => $e->getMessage()]
        );
    }

    return $context;
}
```

### 5. Use Configuration

```php
// Allow users to configure your plugin
protected array $config = [
    'enabled' => true,
    'api_key' => '',
    'timeout' => 30
];

public function initialize(Application $app): void
{
    parent::initialize($app);

    if (!$this->getConfigValue('api_key')) {
        $app->getLogger()->warning(
            'Plugin {name} is missing api_key configuration',
            ['name' => $this->getName()]
        );
    }
}
```

### 6. Document Your Plugin

Create a README.md in your plugin directory:

```markdown
# Example Plugin

## Installation

Place in `.modx-cli/plugins/example-plugin/` (project root)

## Configuration

Edit `.modx-cli/plugins.yaml` (project root):

\`\`\`yaml
example-plugin:
  enabled: true
  option1: value
\`\`\`

## Usage

\`\`\`bash
modx plugin:example
\`\`\`
```

## Troubleshooting

### Plugin Not Loading

**Problem**: Plugin doesn't appear in the CLI

**Solutions**:
- Ensure `Plugin.php` exists in the plugin directory
- Check plugin namespace and class name
- Verify the class implements `PluginInterface`
- Check logs for loading errors

### Version Requirement Errors

**Problem**: Plugin disabled due to version requirements

**Solutions**:
- Update PHP version: `php -v`
- Update MODX CLI: `composer global update finetuned/modx-cli`
- Lower plugin requirements if appropriate

### Hook Not Firing

**Problem**: Hook handlers not being called

**Solutions**:
- Verify hook name spelling
- Check plugin is enabled
- Ensure `initialize()` was called
- Use `getHookManager()->getHookNames()` to see registered hooks

### Command Not Registered

**Problem**: Plugin command not available

**Solutions**:
- Verify command class exists
- Check namespace and class name
- Ensure command extends `BaseCmd`
- Clear cache: `modx cache:clear` (if implemented)

## API Reference

### PluginManager Methods

```php
// Plugin discovery and loading
public function loadPlugins(): void
public function addPluginDirectory(string $directory): void

// Plugin access
public function getPlugin(string $name): ?PluginInterface
public function getPlugins(): array
public function getEnabledPlugins(): array

// Plugin management
public function registerPlugin(PluginInterface $plugin): void
public function enablePlugin(string $name): bool
public function disablePlugin(string $name): bool

// Configuration
public function setLogger(LoggerInterface $logger): void
```

### HookManager Methods

```php
// Hook registration
public function register(string $hookName, callable $handler, int $priority = 10): void
public function unregister(string $hookName, ?callable $handler = null): void

// Hook execution
public function execute(string $hookName, array $context = []): array

// Hook inspection
public function hasHook(string $hookName): bool
public function getHookNames(): array
public function getHandlerCount(string $hookName): int

// Statistics
public function getStats(): array

// Management
public function clear(): void
public function setLogger(LoggerInterface $logger): void
```

## Summary

The Plugin Architecture provides:

- ✅ **Extensible**: Easy to add custom functionality
- ✅ **Flexible**: Hooks at key application points
- ✅ **Configurable**: Per-plugin configuration support
- ✅ **Manageable**: Enable/disable plugins easily
- ✅ **Safe**: Version checking and error handling
- ✅ **Standard**: PSR-compliant interfaces

Build powerful extensions for MODX CLI without modifying core code!
