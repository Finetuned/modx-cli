# Enhanced Logging System

## Overview

The MODX CLI Enhanced Logging System provides comprehensive logging capabilities built on the PSR-3 Logger Interface standard. It offers flexible log levels, verbosity control, file logging with automatic rotation, and colored console output.

**Key Features:**
- PSR-3 compliant logging interface
- 8 log levels (emergency, alert, critical, error, warning, notice, info, debug)
- 5 verbosity levels (quiet, normal, verbose, very verbose, debug)
- File logging with automatic rotation
- Colored console output
- Message interpolation for parameters
- Easy integration via LoggerAwareTrait
- Automatic configuration from CLI options

**CLI Options:**
- `--log-level` sets the minimum level recorded (independent of console verbosity)
- `--log-file` writes logs to a file with automatic rotation

## Architecture

### Components

1. **Logger** (`src/Logging/Logger.php`)
   - PSR-3 compliant logger implementation
   - Handles both console and file output
   - Automatic log rotation
   - Message formatting and interpolation

2. **LoggerAwareTrait** (`src/Logging/LoggerAwareTrait.php`)
   - Provides easy logger integration for commands
   - Convenience methods for common log levels
   - Lazy logger initialization

3. **Application Integration** (`src/Application.php`)
   - Global logger instance
   - Configuration from CLI options
   - Verbosity mapping from Symfony Console

4. **BaseCmd Integration** (`src/Command/BaseCmd.php`)
   - Automatic logger injection for all commands
   - Ready-to-use logging methods

## Log Levels

The system supports 8 PSR-3 log levels, from most to least severe:

| Level     | Use Case                                           | Example                                    |
|-----------|----------------------------------------------------|--------------------------------------------|
| emergency | System is unusable                                 | Database server down                       |
| alert     | Action must be taken immediately                   | Entire website down                        |
| critical  | Critical conditions                                | Application component unavailable          |
| error     | Runtime errors that don't require immediate action | Failed to write to database                |
| warning   | Exceptional occurrences that are not errors        | Deprecated API usage                       |
| notice    | Normal but significant events                      | User logged in                             |
| info      | Interesting events                                 | Command started/completed                  |
| debug     | Detailed debug information                         | Variable values, method calls              |

## Verbosity Levels

Verbosity controls which log levels are output to the console:

| Verbosity     | Value | Symfony Flag | Displays Log Levels                        |
|---------------|-------|--------------|---------------------------------------------|
| Quiet         | 0     | `--quiet`    | None                                        |
| Normal        | 1     | (default)    | emergency, alert, critical, error, warning  |
| Verbose       | 2     | `-v`         | + notice                                    |
| Very Verbose  | 3     | `-vv`        | + info                                      |
| Debug         | 4     | `-vvv`       | + debug                                     |

## Usage

### Global CLI Options

The logging system can be configured via command-line options:

```bash
# Set log level (filters which messages are logged)
modx command --log-level=debug

# Write logs to a file
modx command --log-file=/var/log/modx-cli.log

# Control console output verbosity
modx command -v          # verbose
modx command -vv         # very verbose
modx command -vvv        # debug
modx command --quiet     # no output

# Log debug to file while silencing console output
modx command --log-level=debug --log-file=debug.log --quiet
```

### In Commands

All commands extending `BaseCmd` automatically have access to the logger:

```php
<?php
namespace MODX\CLI\Command;

class MyCommand extends BaseCmd
{
    protected $name = 'my:command';
    protected $description = 'My custom command';

    protected function process()
    {
        // Use convenience methods from LoggerAwareTrait
        $this->logInfo('Command started');
        $this->logDebug('Processing item {id}', ['id' => 123]);

        try {
            // Do something
            $this->logNotice('Operation completed successfully');
        } catch (\Exception $e) {
            $this->logError('Operation failed: {message}', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }

        return 0;
    }
}
```

### Available Logging Methods

Commands have access to these convenience methods via `LoggerAwareTrait`:

```php
// Standard PSR-3 methods (via getLogger())
$this->getLogger()->emergency($message, $context);
$this->getLogger()->alert($message, $context);
$this->getLogger()->critical($message, $context);
$this->getLogger()->error($message, $context);
$this->getLogger()->warning($message, $context);
$this->getLogger()->notice($message, $context);
$this->getLogger()->info($message, $context);
$this->getLogger()->debug($message, $context);

// Convenience methods (shorter syntax)
$this->logDebug($message, $context);
$this->logInfo($message, $context);
$this->logNotice($message, $context);
$this->logWarning($message, $context);
$this->logError($message, $context);
$this->logCritical($message, $context);
$this->logAlert($message, $context);
$this->logEmergency($message, $context);
```

### Message Interpolation

Messages support parameter interpolation using curly braces:

```php
$this->logInfo('User {username} performed {action} on {resource}', [
    'username' => 'admin',
    'action' => 'update',
    'resource' => 'Resource #42'
]);
// Output: User admin performed update on Resource #42
```

### Direct Logger Access

For non-command code, access the logger directly from the Application:

```php
$app = $this->getApplication();
$logger = $app->getLogger();

$logger->info('Processing batch job');
$logger->debug('Item count: {count}', ['count' => $items->count()]);
```

## File Logging

### Configuration

Enable file logging by specifying a log file:

```bash
modx command --log-file=/var/log/modx-cli.log
```

Or programmatically:

```php
$logger = new Logger(Logger::VERBOSITY_NORMAL);
$logger->setLogFile('/var/log/modx-cli.log');
```

### Log Format

File logs use this format:

```
[2025-01-19 14:23:45] INFO: Command started
[2025-01-19 14:23:46] DEBUG: Processing item 123
[2025-01-19 14:23:47] ERROR: Failed to save resource: Database connection lost
```

### Automatic Rotation

The logger automatically rotates log files when they exceed 10MB (configurable):

- Current log: `modx-cli.log`
- After rotation: `modx-cli.log.1`, `modx-cli.log.2`, etc.
- Maximum 5 backup files (configurable)

Configure rotation:

```php
$logger->setMaxFileSize(20 * 1024 * 1024);  // 20MB
$logger->setMaxBackupFiles(10);              // Keep 10 backups
```

## Console Output

### Color Coding

Console output uses colors to distinguish log levels:

- **Red**: error, critical, alert, emergency
- **Yellow**: warning
- **Cyan**: notice
- **White/Default**: info, debug

### Example Output

```
[2025-01-19 14:23:45] INFO: Starting backup process
[2025-01-19 14:23:46] DEBUG: Connecting to database
[2025-01-19 14:23:47] NOTICE: Backup created: backup-20250119.sql
[2025-01-19 14:23:48] WARNING: Backup size exceeds 100MB
[2025-01-19 14:23:49] INFO: Backup process completed
```

## Advanced Usage

### Custom Logger Configuration

Create a custom logger instance:

```php
use MODX\CLI\Logging\Logger;
use Psr\Log\LogLevel;

// Create logger
$logger = new Logger(Logger::VERBOSITY_DEBUG);

// Configure
$logger->setLogLevel(LogLevel::DEBUG);
$logger->setLogFile('/var/log/custom.log');
$logger->setMaxFileSize(50 * 1024 * 1024);  // 50MB
$logger->setMaxBackupFiles(20);

// Use it
$logger->info('Custom logger initialized');
```

### Filtering by Log Level

The `--log-level` option filters which messages are logged:

```bash
# Only log errors and above (error, critical, alert, emergency)
modx command --log-level=error

# Log everything
modx command --log-level=debug
```

This is independent of verbosity (which controls console output):

```bash
# Log debug messages to file, but only show warnings+ on console
modx command --log-level=debug --log-file=debug.log
```

### Using Logger in Services

For service classes or non-command code:

```php
<?php
namespace MyPackage\Services;

use MODX\CLI\Logging\LoggerAwareTrait;
use Psr\Log\LoggerInterface;

class MyService
{
    use LoggerAwareTrait;

    public function __construct(LoggerInterface $logger = null)
    {
        if ($logger) {
            $this->setLogger($logger);
        }
    }

    public function doSomething()
    {
        $this->logInfo('Service method called');

        // Your logic here

        $this->logDebug('Operation details', ['key' => 'value']);
    }
}

// Usage
$service = new MyService($app->getLogger());
$service->doSomething();
```

## Integration Guide

### Adding Logging to Existing Commands

1. **If extending BaseCmd**: Logging is already available!

```php
class MyCommand extends BaseCmd
{
    protected function process()
    {
        $this->logInfo('Already working!');
    }
}
```

2. **For standalone Symfony Commands**: Use LoggerAwareTrait

```php
use MODX\CLI\Logging\LoggerAwareTrait;
use Symfony\Component\Console\Command\Command;

class StandaloneCommand extends Command
{
    use LoggerAwareTrait;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // Get logger from application
        $logger = $this->getApplication()->getLogger();
        $this->setLogger($logger);

        // Now use it
        $this->logInfo('Command executing');
    }
}
```

### Component Integration

For MODX components providing CLI commands:

```php
<?php
namespace MyComponent\Command;

use MODX\CLI\Command\BaseCmd;

class ComponentCommand extends BaseCmd
{
    protected $name = 'mycomponent:action';

    protected function process()
    {
        $this->logInfo('Component command started');

        // Your component logic
        $result = $this->myComponent->doSomething();

        if ($result) {
            $this->logNotice('Action completed successfully');
        } else {
            $this->logError('Action failed');
            return 1;
        }

        return 0;
    }
}
```

## Best Practices

### 1. Choose Appropriate Log Levels

```php
// ✅ Good
$this->logDebug('Variable value: {value}', ['value' => $var]);
$this->logInfo('Processing started');
$this->logWarning('Deprecated method used');
$this->logError('Database query failed');

// ❌ Avoid
$this->logError('Processing started');  // Not an error
$this->logDebug('Database crashed');    // Too low severity
```

### 2. Use Message Interpolation

```php
// ✅ Good
$this->logInfo('Processed {count} items in {time}ms', [
    'count' => $count,
    'time' => $elapsed
]);

// ❌ Avoid
$this->logInfo("Processed $count items in {$elapsed}ms");  // Harder to parse
```

### 3. Include Context

```php
// ✅ Good
$this->logError('Failed to save resource', [
    'resource_id' => $resource->id,
    'error' => $e->getMessage(),
    'user_id' => $user->id
]);

// ❌ Avoid
$this->logError('Failed to save');  // Not enough detail
```

### 4. Don't Log Sensitive Data

```php
// ✅ Good
$this->logInfo('User authenticated', [
    'user_id' => $user->id,
    'username' => $user->username
]);

// ❌ Avoid
$this->logDebug('Login attempt', [
    'password' => $password,  // NEVER log passwords!
    'api_key' => $apiKey      // Or other secrets
]);
```

### 5. Use Debug Logs Liberally

```php
protected function process()
{
    $this->logDebug('Command started', ['args' => $this->argument()]);

    $items = $this->fetchItems();
    $this->logDebug('Fetched {count} items', ['count' => count($items)]);

    foreach ($items as $item) {
        $this->logDebug('Processing item {id}', ['id' => $item->id]);
        // Process item
    }

    $this->logInfo('Processing completed');
}
```

### 6. Log Command Lifecycle

```php
protected function process()
{
    $this->logInfo('{command} started', ['command' => $this->name]);

    try {
        // Your logic
        $result = $this->doWork();

        $this->logInfo('{command} completed successfully', [
            'command' => $this->name,
            'duration' => $this->getRunStats()
        ]);

        return 0;
    } catch (\Exception $e) {
        $this->logError('{command} failed: {error}', [
            'command' => $this->name,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);

        return 1;
    }
}
```

## Configuration Reference

### Logger Class Constants

```php
// Verbosity levels
Logger::VERBOSITY_QUIET         = 0;
Logger::VERBOSITY_NORMAL        = 1;
Logger::VERBOSITY_VERBOSE       = 2;
Logger::VERBOSITY_VERY_VERBOSE  = 3;
Logger::VERBOSITY_DEBUG         = 4;
```

### PSR-3 Log Levels

```php
LogLevel::EMERGENCY = 'emergency';
LogLevel::ALERT     = 'alert';
LogLevel::CRITICAL  = 'critical';
LogLevel::ERROR     = 'error';
LogLevel::WARNING   = 'warning';
LogLevel::NOTICE    = 'notice';
LogLevel::INFO      = 'info';
LogLevel::DEBUG     = 'debug';
```

### Logger Methods

```php
// Configuration
$logger->setVerbosity(int $level): void
$logger->setLogLevel(string $level): void
$logger->setLogFile(string $file): void
$logger->setConsoleOutput(OutputInterface $output): void
$logger->setMaxFileSize(int $bytes): void
$logger->setMaxBackupFiles(int $count): void

// Logging (PSR-3)
$logger->emergency(string $message, array $context = []): void
$logger->alert(string $message, array $context = []): void
$logger->critical(string $message, array $context = []): void
$logger->error(string $message, array $context = []): void
$logger->warning(string $message, array $context = []): void
$logger->notice(string $message, array $context = []): void
$logger->info(string $message, array $context = []): void
$logger->debug(string $message, array $context = []): void
$logger->log(mixed $level, string $message, array $context = []): void
```

### LoggerAwareTrait Methods

```php
// Logger management
setLogger(LoggerInterface $logger): void
getLogger(): LoggerInterface

// Convenience logging methods
logDebug(string $message, array $context = []): void
logInfo(string $message, array $context = []): void
logNotice(string $message, array $context = []): void
logWarning(string $message, array $context = []): void
logError(string $message, array $context = []): void
logCritical(string $message, array $context = []): void
logAlert(string $message, array $context = []): void
logEmergency(string $message, array $context = []): void
```

## Examples

### Example 1: Simple Command Logging

```php
class BackupCommand extends BaseCmd
{
    protected $name = 'backup:create';

    protected function process()
    {
        $this->logInfo('Starting backup process');

        $filename = $this->createBackup();

        if ($filename) {
            $this->logNotice('Backup created: {file}', ['file' => $filename]);
            return 0;
        }

        $this->logError('Backup creation failed');
        return 1;
    }
}
```

### Example 2: Debug Logging

```php
class ImportCommand extends BaseCmd
{
    protected $name = 'data:import';

    protected function process()
    {
        $file = $this->argument('file');
        $this->logDebug('Import file: {file}', ['file' => $file]);

        $data = json_decode(file_get_contents($file), true);
        $this->logDebug('Loaded {count} records', ['count' => count($data)]);

        $imported = 0;
        foreach ($data as $record) {
            $this->logDebug('Importing record {id}', ['id' => $record['id']]);

            if ($this->importRecord($record)) {
                $imported++;
            }
        }

        $this->logInfo('Import completed: {imported}/{total} records', [
            'imported' => $imported,
            'total' => count($data)
        ]);

        return 0;
    }
}
```

### Example 3: Error Handling

```php
class MigrationCommand extends BaseCmd
{
    protected $name = 'migrate:run';

    protected function process()
    {
        $this->logInfo('Running migrations');

        try {
            foreach ($this->getPendingMigrations() as $migration) {
                $this->logDebug('Running migration: {name}', ['name' => $migration]);

                $this->runMigration($migration);

                $this->logNotice('Migration completed: {name}', ['name' => $migration]);
            }

            $this->logInfo('All migrations completed successfully');
            return 0;

        } catch (\Exception $e) {
            $this->logError('Migration failed: {error}', [
                'error' => $e->getMessage(),
                'migration' => $migration ?? 'unknown',
                'trace' => $e->getTraceAsString()
            ]);

            return 1;
        }
    }
}
```

### Example 4: File Logging with Rotation

```bash
# Run command with file logging
modx backup:create --log-file=/var/log/modx-backups.log --log-level=debug -vv

# This will:
# - Write debug+ messages to /var/log/modx-backups.log
# - Show info+ messages on console (due to -vv)
# - Automatically rotate the log file when it exceeds 10MB
```

### Example 5: Service Integration

```php
class ResourceService
{
    use LoggerAwareTrait;

    private $modx;

    public function __construct($modx, LoggerInterface $logger)
    {
        $this->modx = $modx;
        $this->setLogger($logger);
    }

    public function createResource(array $data)
    {
        $this->logDebug('Creating resource', $data);

        $resource = $this->modx->newObject('modResource');
        $resource->fromArray($data);

        if ($resource->save()) {
            $this->logInfo('Resource created: {id}', ['id' => $resource->id]);
            return $resource;
        }

        $this->logError('Failed to create resource', $data);
        return null;
    }
}

// Usage in command
protected function process()
{
    $service = new ResourceService($this->modx, $this->getLogger());
    $resource = $service->createResource(['pagetitle' => 'Test']);
}
```

## Troubleshooting

### Logs Not Appearing in File

**Problem**: Messages logged but file is empty or not created

**Solutions**:
- Check file permissions (logger needs write access)
- Verify log level includes the messages (`--log-level=debug`)
- Ensure the directory exists
- Check disk space

### Too Much/Too Little Console Output

**Problem**: Console shows wrong amount of information

**Solutions**:
- Adjust verbosity: `-v`, `-vv`, `-vvv`, or `--quiet`
- Check log level of your messages match verbosity
- Use appropriate log levels (debug for detail, info for normal, error for problems)

### Log Files Growing Too Large

**Problem**: Log files consuming too much disk space

**Solutions**:
- Log rotation is automatic (default 10MB, 5 backups)
- Reduce `maxFileSize`: `$logger->setMaxFileSize(5 * 1024 * 1024);`
- Reduce `maxBackupFiles`: `$logger->setMaxBackupFiles(3);`
- Use cron to clean old logs

### Missing Logger in Command

**Problem**: `$this->logInfo()` throws error

**Solutions**:
- Ensure command extends `BaseCmd`
- If not extending BaseCmd, manually initialize logger:
  ```php
  $this->setLogger($this->getApplication()->getLogger());
  ```

## Migration Guide

### From Direct Output Calls

**Before:**
```php
$this->output->writeln('<info>Processing item ' . $id . '</info>');
$this->output->writeln('<error>Failed to process</error>');
```

**After:**
```php
$this->logInfo('Processing item {id}', ['id' => $id]);
$this->logError('Failed to process');
```

**Benefits:**
- Structured logging (parseable)
- Automatic file logging
- Consistent formatting
- Context preservation

### Adding to Existing Commands

1. Keep using `$this->info()`, `$this->error()` for user-facing messages
2. Add logging for operational details:

```php
protected function process()
{
    // User-facing output
    $this->info('Starting import...');

    // Operational logging
    $this->logDebug('Import configuration', $config);
    $this->logInfo('Import started', ['file' => $filename]);

    // ... do work ...

    // User-facing output
    $this->info('Import completed!');

    // Operational logging
    $this->logInfo('Import completed successfully', ['records' => $count]);
}
```

## Summary

The Enhanced Logging System provides:

- ✅ **Standard Interface**: PSR-3 compliant
- ✅ **Flexible**: 8 log levels, 5 verbosity settings
- ✅ **Convenient**: Automatic injection, trait-based
- ✅ **Reliable**: File logging with rotation
- ✅ **User-Friendly**: Colored console output
- ✅ **Production-Ready**: Tested and documented

Start using it in your commands today for better debugging, monitoring, and operational visibility!
