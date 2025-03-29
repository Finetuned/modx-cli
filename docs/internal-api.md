# MODX CLI Internal API

The MODX CLI Internal API provides a set of tools and functions to extend and customize the MODX CLI command-line interface. It allows developers to register custom commands, modify existing commands, and hook into MODX CLI's lifecycle.

## Overview

The Internal API is designed to be simple and intuitive, similar to the WP-CLI Internal API for WordPress. It provides a static class `MODX_CLI` with methods for registering commands, running commands programmatically, and hooking into the command lifecycle.

## Key Components

- **MODX_CLI**: The main static class that provides the public API
- **CommandRegistry**: Manages command registration and retrieval
- **HookRegistry**: Manages hook registration and execution
- **CommandRunner**: Handles running commands programmatically
- **CommandPublisher**: Provides asynchronous command execution
- **ClosureCommand**: Wraps closures as commands
- **HookableCommand**: Interface for commands that support hooks

## API Reference

### MODX_CLI::add_command()

Registers a custom command with MODX CLI.

```php
MODX_CLI::add_command($name, $callable, $args = [])
```

**Parameters**:
- `$name` (string): The name of the command (e.g., 'post:list' or 'site:empty')
- `$callable` (callable|object|string): The implementation of the command, which can be a class, function, or closure
- `$args` (array): An optional associative array of additional parameters for the command, including:
  - `before_invoke` (callable): Callback to execute before the command
  - `after_invoke` (callable): Callback to execute after the command
  - `shortdesc` (string): Short description of the command
  - `longdesc` (string): Long description of the command
  - `synopsis` (array): Command arguments and options definition
  - `when` (string): Hook to execute the command on
  - `is_deferred` (bool): Whether command registration is deferred

**Return Value**: Returns `true` on success, `false` if deferred

### MODX_CLI::remove_command()

Removes a command from MODX CLI.

```php
MODX_CLI::remove_command($name)
```

**Parameters**:
- `$name` (string): The name of the command to remove

**Return Value**: Returns `true` if command was removed, `false` if it didn't exist

### MODX_CLI::get_command()

Gets a command by name.

```php
MODX_CLI::get_command($name)
```

**Parameters**:
- `$name` (string): The name of the command

**Return Value**: Returns the command instance or `null` if not found

### MODX_CLI::get_commands()

Gets all registered commands.

```php
MODX_CLI::get_commands()
```

**Return Value**: Returns an array of command instances

### MODX_CLI::run_command()

Runs a command registered with MODX CLI.

```php
MODX_CLI::run_command($command, $args = [], $options = [])
```

**Parameters**:
- `$command` (string): The command to execute
- `$args` (array): Command arguments
- `$options` (array): An optional associative array of options for command execution:
  - `return` (bool): Whether to return the command result
  - `exit_error` (bool): Whether to exit on error
  - `parse` (bool): Whether to parse the command string

**Return Value**: Returns the command result if `$return` is `true`

### MODX_CLI::register_hook()

Registers a hook with MODX CLI.

```php
MODX_CLI::register_hook($hook, $callback)
```

**Parameters**:
- `$hook` (string): The hook name
- `$callback` (callable): The callback to execute

**Return Value**: Returns `true` on success

### MODX_CLI::add_hook()

Adds a callback to an existing hook.

```php
MODX_CLI::add_hook($hook, $callback)
```

**Parameters**:
- `$hook` (string): The hook name
- `$callback` (callable): The callback to add

**Return Value**: Returns `true` on success

### MODX_CLI::do_hook()

Runs a hook.

```php
MODX_CLI::do_hook($hook, $args = [])
```

**Parameters**:
- `$hook` (string): The hook name
- `$args` (array): Arguments to pass to the hook

**Return Value**: Returns an array of results from the hook callbacks

### MODX_CLI::before_invoke()

Sets a callback to run before a command is executed.

```php
MODX_CLI::before_invoke($command, $callback)
```

**Parameters**:
- `$command` (string): The command name
- `$callback` (callable): The callback to execute

**Return Value**: Returns `true` on success

### MODX_CLI::after_invoke()

Sets a callback to run after a command is executed.

```php
MODX_CLI::after_invoke($command, $callback)
```

**Parameters**:
- `$command` (string): The command name
- `$callback` (callable): The callback to execute

**Return Value**: Returns `true` on success

### MODX_CLI::log()

Writes a message to the console.

```php
MODX_CLI::log($message)
```

**Parameters**:
- `$message` (string): The message to write

### MODX_CLI::success()

Writes a success message to the console.

```php
MODX_CLI::success($message)
```

**Parameters**:
- `$message` (string): The message to write

### MODX_CLI::warning()

Writes a warning message to the console.

```php
MODX_CLI::warning($message)
```

**Parameters**:
- `$message` (string): The message to write

### MODX_CLI::error()

Writes an error message to the console.

```php
MODX_CLI::error($message)
```

**Parameters**:
- `$message` (string): The message to write

## Examples

### Registering a Simple Command

```php
MODX_CLI::add_command('hello', function($args, $assoc_args) {
    MODX_CLI::log('Hello, ' . ($assoc_args['name'] ?? 'World') . '!');
});
```

### Registering a Command with a Class

```php
class MyCommand extends \MODX\CLI\Command\BaseCmd
{
    protected $name = 'my:command';
    protected $description = 'My custom command';
    
    protected function process()
    {
        $this->line('This is my custom command!');
        return 0;
    }
}

MODX_CLI::add_command('my:command', 'MyNamespace\\MyCommand');
```

### Using Hooks

```php
// Register a hook
MODX_CLI::register_hook('after_command_run', function($command, $result) {
    MODX_CLI::log("Command '$command' completed with result: " . json_encode($result));
});

// Add a callback to a hook
MODX_CLI::add_hook('after_command_run', function($command, $result) {
    // Log to a file
    file_put_contents('command_log.txt', "Command '$command' ran at " . date('Y-m-d H:i:s') . "\n", FILE_APPEND);
});

// Set a before invoke callback for a specific command
MODX_CLI::before_invoke('cache:clear', function() {
    MODX_CLI::log('About to clear the cache...');
});

// Set an after invoke callback for a specific command
MODX_CLI::after_invoke('cache:clear', function($result) {
    MODX_CLI::log('Cache cleared successfully!');
});
```

### Running Commands Programmatically

```php
// Run a command and get the result
$result = MODX_CLI::run_command('cache:clear', [], ['return' => true]);
if ($result->return_code === 0) {
    MODX_CLI::success('Cache cleared successfully!');
} else {
    MODX_CLI::error('Failed to clear cache: ' . $result->stderr);
}

// Run a command with arguments
MODX_CLI::run_command('resource:create', [
    'title' => 'My New Resource',
    'content' => 'This is the content of my new resource.',
    'parent' => 0,
    'published' => true
]);
```

### Using the Run-Sequence Command

The `run-sequence` command allows you to run multiple commands in sequence with various execution options.

```bash
modx run-sequence --command_sets='{
    "set1": {
        "commands": ["cache:clear", "resource:list --format=table"],
        "continue_after_error": true,
        "is_asynchronous": true,
        "collates_errors": true,
        "collates_data_responses": true,
        "returns_results_as_json": true
    },
    "set2": {
        "commands": ["user:list --role=administrator", "plugin:list --status=active"],
        "is_asynchronous": false
    }
}'
```

## Third-Party Integration

Developers can integrate their own commands with MODX CLI by creating a package that uses the Internal API. Here's an example of how to structure a package:

```php
// my-package/src/MyPackage.php
namespace MyNamespace;

class MyPackage
{
    public static function register_commands()
    {
        \MODX\CLI\API\MODX_CLI::add_command('my-package:command1', function($args, $assoc_args) {
            \MODX\CLI\API\MODX_CLI::log('Running command 1...');
        });
        
        \MODX\CLI\API\MODX_CLI::add_command('my-package:command2', 'MyNamespace\\Command2');
    }
}

// Register commands when the package is loaded
MyPackage::register_commands();
```

## Best Practices

1. **Use Descriptive Command Names**: Command names should be descriptive and follow the pattern `namespace:command` or `namespace:subnamespace:command`.

2. **Provide Helpful Documentation**: Always include a description and help text for your commands to make them easier to use.

3. **Handle Errors Gracefully**: Make sure your commands handle errors gracefully and provide helpful error messages.

4. **Use Hooks Sparingly**: Hooks are powerful but can make the code harder to follow. Use them only when necessary.

5. **Follow MODX CLI Conventions**: Follow the conventions established by MODX CLI, such as using the `BaseCmd` class for commands and returning appropriate exit codes.

6. **Test Your Commands**: Make sure to test your commands thoroughly before releasing them to the public.

7. **Document Your Commands**: Provide documentation for your commands, including examples of how to use them.

## Conclusion

The MODX CLI Internal API provides a powerful way to extend and customize the MODX CLI command-line interface. By following the examples and best practices outlined in this document, you can create custom commands that integrate seamlessly with MODX CLI.
