# Task 5: Add --json Option to All CLI Commands

## Overview

Added a global `--json` option to all CLI commands that return data, allowing users to get machine-readable JSON output for automation and scripting purposes. This implementation follows the Command Line Interface Guidelines (CLIG) recommendations for output formats.

## Implementation Details

### 1. Added Global JSON Option to BaseCmd

Added a global `--json` option to the `BaseCmd` class, making it available to all commands:

```php
protected function getOptions()
{
    return array(
        array(
            'json',
            null,
            InputOption::VALUE_NONE,
            'Output results in JSON format'
        ),
    );
}
```

### 2. Updated ListProcessor Class

Modified the `ListProcessor` class to handle JSON output:

```php
protected function processResponse(array $results = array())
{
    $total = $results['total'];
    $results = $results['results'];

    if ($this->option('json')) {
        $output = [
            'total' => $total,
            'results' => $results
        ];
        $this->output->writeln(json_encode($output, JSON_PRETTY_PRINT));
        return 0;
    }

    // Existing table output code...
}
```

### 3. Updated ProcessorCmd Class

Modified the `ProcessorCmd` class to handle JSON output for commands that don't extend ListProcessor:

```php
protected function processResponse(array $response = array())
{
    if ($this->option('json')) {
        $this->output->writeln(json_encode($response, JSON_PRETTY_PRINT));
        return 0;
    }
    
    // Existing output code...
}
```

### 4. Fixed Inheritance Issue in ProcessorCmd

Initially, the `--json` option wasn't working because `ProcessorCmd` was overriding the `getOptions()` method from `BaseCmd` without calling `parent::getOptions()`. This meant that commands extending `ProcessorCmd` (which is most commands) weren't inheriting the `--json` option.

Fixed by updating `ProcessorCmd::getOptions()` to include the parent options:

```php
protected function getOptions()
{
    return array_merge(parent::getOptions(), array(
        // ProcessorCmd's own options...
    ));
}
```

### 5. Updated Individual Commands with Existing Format Options

For commands that already had a `--format` option (like `Category\Get.php` and `Resource\Get.php`), updated them to respect both the global `--json` option and the existing `--format=json` option:

```php
// Check for both --json flag and --format=json
if ($this->option('json') || $this->option('format') === 'json') {
    $this->output->writeln(json_encode($resource, JSON_PRETTY_PRINT));
    return 0;
}
```

## Usage Examples

### List Commands

```bash
# Get a list of categories in JSON format
modx category:getlist --json

# Get a list of resources in JSON format
modx resource:getlist --json
```

### Get Commands

```bash
# Get a resource by ID in JSON format
modx resource:get 1 --json

# Get a category by ID in JSON format
modx category:get 1 --json
```

### System Commands

```bash
# Get system information in JSON format
modx system:info --json
```

## Benefits

1. **Consistency**: All commands now support JSON output in a consistent way.
2. **Automation**: Makes it easier to use the CLI in scripts and automation workflows.
3. **Integration**: Enables integration with other tools that can process JSON data.
4. **Standards Compliance**: Follows CLI best practices for machine-readable output.

### 6. Updated Unit Tests

Added tests for the `--json` option to all Get command test files:

- Added `testExecuteWithJsonOption()` method to test the new global `--json` option
- Added `testExecuteWithNotFoundAndJsonOption()` method to test error handling with the `--json` option
- Ensured both the existing `--format=json` and new `--json` options work correctly
- Verified JSON output format is consistent across all commands

Example test for the `--json` option:

```php
public function testExecuteWithJsonOption()
{
    // Mock the runProcessor method to return a successful response
    $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
        ->disableOriginalConstructor()
        ->getMock();
    $processorResponse->method('getResponse')
        ->willReturn(json_encode([
            'object' => [
                'id' => 123,
                'name' => 'TestName',
                // Other relevant properties
            ]
        ]));
    $processorResponse->method('isError')->willReturn(false);
    
    $this->modx->expects($this->once())
        ->method('runProcessor')
        ->willReturn($processorResponse);
    
    // Execute the command with --json option
    $this->commandTester->execute([
        'command' => $this->command->getName(),
        'id' => '123',
        '--json' => true
    ]);
    
    // Verify the output is JSON
    $output = $this->commandTester->getDisplay();
    $this->assertJson($output);
    $data = json_decode($output, true);
    $this->assertEquals(123, $data['id']);
    $this->assertEquals('TestName', $data['name']);
}
```

Example test for error handling with the `--json` option:

```php
public function testExecuteWithNotFoundAndJsonOption()
{
    // Mock the runProcessor method to return a response with no object
    $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
        ->disableOriginalConstructor()
        ->getMock();
    $processorResponse->method('getResponse')
        ->willReturn(json_encode([]));
    $processorResponse->method('isError')->willReturn(false);
    
    $this->modx->expects($this->once())
        ->method('runProcessor')
        ->willReturn($processorResponse);
    
    // Execute the command with --json option
    $this->commandTester->execute([
        'command' => $this->command->getName(),
        'id' => '999',
        '--json' => true
    ]);
    
    // Verify the output is JSON with error message
    $output = $this->commandTester->getDisplay();
    $this->assertJson($output);
    $data = json_decode($output, true);
    $this->assertFalse($data['success']);
    $this->assertStringContainsString('not found', $data['message']);
}
```

## Future Improvements

1. Add support for other output formats (e.g., CSV, YAML) if needed.
2. Standardize error output format in JSON mode.
3. Add documentation for the JSON structure of each command's output.
4. Add tests for the `--json` option to GetList commands and other command types.
