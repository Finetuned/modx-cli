# Command Output Streaming

## Overview

The Command Output Streaming system provides real-time output capabilities for long-running commands. It includes progress bars, buffered output, event callbacks, and section-based output for complex command scenarios.

**Key Features:**
- Real-time streaming output
- Progress bars with customizable formats
- Buffered and unbuffered modes
- Event callbacks for output monitoring
- Section-based output for hierarchical display
- Performance statistics tracking
- Easy integration via trait

## Quick Start

### Basic Streaming

```php
<?php
use MODX\CLI\Command\BaseCmd;
use MODX\CLI\Output\StreamingOutputTrait;

class MyCommand extends BaseCmd
{
    use StreamingOutputTrait;

    protected $name = 'my:command';

    protected function process()
    {
        // Stream output in real-time
        $this->stream('Processing started...');

        for ($i = 1; $i <= 100; $i++) {
            $this->stream("Processing item {$i}");
            usleep(50000); // Simulate work
        }

        $this->stream('Processing completed!');

        return 0;
    }
}
```

### Progress Bars

```php
protected function process()
{
    $items = range(1, 100);

    // Start progress bar
    $this->startProgress(count($items), 'Processing items...');

    foreach ($items as $item) {
        // Do work
        $this->processItem($item);

        // Advance progress
        $this->advanceProgress(1, "Processing item {$item}");
    }

    // Finish progress
    $this->finishProgress();

    return 0;
}
```

## Architecture

### Core Components

1. **StreamingOutput** - Main streaming output handler
2. **SectionOutput** - Independent output sections
3. **StreamingOutputTrait** - Convenience methods for commands
4. **Event System** - Callbacks for output events
5. **Statistics** - Performance tracking

### Output Modes

- **Unbuffered** (default): Output appears immediately
- **Buffered**: Output stored in memory, displayed on flush

Note: Streaming output is intended for human-readable console output. Avoid mixing streaming with `--json` responses so machine parsing remains reliable.

## StreamingOutput Class

### Creating an Instance

```php
use MODX\CLI\Output\StreamingOutput;

// Unbuffered output (immediate)
$streaming = new StreamingOutput($output);

// Buffered output
$streaming = new StreamingOutput($output, true);
```

### Writing Output

```php
// Write a line
$streaming->write('Hello world');

// Write without newline
$streaming->write('Loading...', false);

// Write multiple lines
$streaming->writeLines([
    'Line 1',
    'Line 2',
    'Line 3'
]);

// Formatted output
$streaming->writef('Processed %d items in %.2f seconds', $count, $duration);
```

### Progress Bars

```php
// Start progress
$progress = $streaming->startProgress(100, 'Processing...');

// Advance by N steps
$streaming->advanceProgress(10);

// Set to specific value
$streaming->setProgress(50);

// Update message
$streaming->advanceProgress(1, 'Processing item 51');

// Finish
$streaming->finishProgress();
```

## Buffered Output

### Enabling Buffering

```php
// Enable buffering
$streaming->enableBuffering();

// Write to buffer
$streaming->write('This goes to buffer');
$streaming->write('This too');

// Flush buffer to output
$streaming->flush();

// Disable buffering (auto-flushes)
$streaming->disableBuffering();
```

### Use Cases for Buffering

1. **Conditional Output**: Decide later whether to show output
2. **Post-Processing**: Modify output before displaying
3. **Grouped Output**: Collect and display as a unit
4. **Error Handling**: Suppress output if operation fails

### Example: Conditional Output

```php
protected function process()
{
    $this->enableBuffering();

    $this->stream('Step 1 completed');
    $this->stream('Step 2 completed');

    if ($this->processRisky()) {
        // Show output only if successful
        $this->flushOutput();
    } else {
        // Discard buffered output
        $this->getStreamingOutput()->clearBuffer();
        $this->error('Process failed - output suppressed');
    }

    $this->disableBuffering();

    return 0;
}
```

## Section Output

### Creating Sections

Sections allow independent output areas that can be updated separately:

```php
protected function process()
{
    // Create sections
    $section1 = $this->createSection();
    $section2 = $this->createSection();

    // Write to each section
    $section1->write('Section 1: Initial content');
    $section2->write('Section 2: Initial content');

    sleep(1);

    // Update sections independently
    $section1->overwrite('Section 1: Updated content');
    $section2->overwrite('Section 2: Updated content');

    return 0;
}
```

### Use Cases for Sections

1. **Multi-Step Processes**: Update each step's status independently
2. **Parallel Operations**: Show multiple operations side-by-side
3. **Live Updates**: Update specific parts without reprinting everything
4. **Status Boards**: Create dashboard-like output

### Example: Multi-Step Process

```php
protected function process()
{
    $steps = [
        'Database backup',
        'File compression',
        'Upload to S3',
        'Cleanup'
    ];

    $sections = [];
    foreach ($steps as $step) {
        $section = $this->createSection();
        $section->write("⏳ {$step}: Pending");
        $sections[] = $section;
    }

    foreach ($steps as $i => $step) {
        $sections[$i]->overwrite("⏳ {$step}: Running...");

        // Do the work
        $this->performStep($step);

        $sections[$i]->overwrite("✅ {$step}: Completed");
    }

    return 0;
}
```

## Event System

### Available Events

| Event | Data | Description |
|-------|------|-------------|
| `write` | `{message: string}` | Line written to output |
| `flush` | `{lines: int}` | Buffer flushed |
| `progress.start` | `{max: int}` | Progress bar started |
| `progress.advance` | `{step: int, current: int}` | Progress advanced |
| `progress.set` | `{current: int}` | Progress set to value |
| `progress.finish` | `{}` | Progress completed |

### Registering Callbacks

```php
protected function process()
{
    // Log all output
    $this->onStreamEvent('write', function($data) {
        file_put_contents('output.log', $data['message'] . "\n", FILE_APPEND);
    });

    // Track progress
    $this->onStreamEvent('progress.advance', function($data) {
        $this->logInfo('Progress: {current}', ['current' => $data['current']]);
    });

    // Notification on completion
    $this->onStreamEvent('progress.finish', function() {
        $this->notify('Process completed!');
    });

    // ... rest of command
}
```

### Example: Real-Time Monitoring

```php
protected function process()
{
    $stats = [
        'lines' => 0,
        'errors' => 0
    ];

    // Track output statistics
    $this->onStreamEvent('write', function($data) use (&$stats) {
        $stats['lines']++;
        if (str_contains($data['message'], 'ERROR')) {
            $stats['errors']++;
        }
    });

    // Process items
    foreach ($this->getItems() as $item) {
        try {
            $this->processItem($item);
            $this->stream("✅ Processed: {$item}");
        } catch (\Exception $e) {
            $this->stream("❌ ERROR: {$item} - {$e->getMessage()}");
        }
    }

    // Display summary
    $this->info("Total lines: {$stats['lines']}");
    $this->info("Errors: {$stats['errors']}");

    return 0;
}
```

## Statistics

### Getting Stats

```php
// Get streaming statistics
$stats = $this->getStreamingStats();

echo "Lines: " . $stats['lines'];
echo "Bytes: " . $stats['bytes'];
echo "Duration: " . $stats['duration'] . "s";
echo "Rate: " . $stats['rate'] . " lines/sec";
```

### Example: Performance Report

```php
protected function process()
{
    // Do work with streaming output
    foreach ($items as $item) {
        $this->stream("Processing {$item}");
    }

    // Get statistics
    $stats = $this->getStreamingStats();

    // Display report
    $this->info('=== Performance Report ===');
    $this->info("Lines output: {$stats['lines']}");
    $this->info("Data size: {$stats['bytes']} bytes");
    $this->info("Duration: {$stats['duration']} seconds");
    $this->info("Rate: {$stats['rate']} lines/second");

    return 0;
}
```

## StreamingOutputTrait

### Available Methods

```php
// Output methods
protected function stream(string $message, bool $newline = true): void
protected function streamLines(array $lines): void
protected function streamf(string $format, ...$args): void

// Progress methods
protected function startProgress(int $max, ?string $message = null): ProgressBar
protected function advanceProgress(int $step = 1, ?string $message = null): void
protected function setProgress(int $current, ?string $message = null): void
protected function finishProgress(): void

// Section methods
protected function createSection(): SectionOutput

// Buffering methods
protected function enableBuffering(): void
protected function disableBuffering(): void
protected function flushOutput(): void

// Event methods
protected function onStreamEvent(string $event, callable $callback): void

// Statistics
protected function getStreamingStats(): array

// Access underlying object
protected function getStreamingOutput(bool $buffered = false): StreamingOutput
```

## Examples

### Example 1: File Processing with Progress

```php
class ProcessFilesCommand extends BaseCmd
{
    use StreamingOutputTrait;

    protected $name = 'process:files';

    protected function process()
    {
        $files = glob('data/*.csv');

        if (empty($files)) {
            $this->error('No files found');
            return 1;
        }

        $this->info("Found " . count($files) . " files to process");

        // Start progress bar
        $this->startProgress(count($files), 'Processing files...');

        foreach ($files as $file) {
            $basename = basename($file);

            try {
                $rows = $this->processFile($file);
                $this->advanceProgress(1, "✅ {$basename}: {$rows} rows");
            } catch (\Exception $e) {
                $this->advanceProgress(1, "❌ {$basename}: ERROR");
                $this->logError('File processing failed', [
                    'file' => $file,
                    'error' => $e->getMessage()
                ]);
            }
        }

        $this->finishProgress();

        // Show stats
        $stats = $this->getStreamingStats();
        $this->info("Processed in {$stats['duration']} seconds");

        return 0;
    }

    protected function processFile(string $file): int
    {
        $handle = fopen($file, 'r');
        $rows = 0;

        while (($data = fgetcsv($handle)) !== false) {
            // Process row
            $rows++;
        }

        fclose($handle);
        return $rows;
    }
}
```

### Example 2: Real-Time Log Streaming

```php
class StreamLogsCommand extends BaseCmd
{
    use StreamingOutputTrait;

    protected $name = 'logs:stream';

    protected function process()
    {
        $logFile = $this->argument('file') ?: '/var/log/modx.log';

        if (!file_exists($logFile)) {
            $this->error("Log file not found: {$logFile}");
            return 1;
        }

        $this->info("Streaming logs from: {$logFile}");
        $this->info("Press Ctrl+C to stop\n");

        // Track log events
        $this->onStreamEvent('write', function($data) {
            if (str_contains($data['message'], 'ERROR')) {
                // Could send alert, increment counter, etc.
            }
        });

        // Open file and seek to end
        $handle = fopen($logFile, 'r');
        fseek($handle, 0, SEEK_END);

        // Stream new lines as they're added
        while (true) {
            $line = fgets($handle);

            if ($line !== false) {
                $this->stream(rtrim($line));
            } else {
                // No new data, wait a bit
                usleep(100000); // 100ms
                clearstatcache(false, $logFile);
            }
        }

        fclose($handle);
        return 0;
    }
}
```

### Example 3: Multi-Stage Deployment

```php
class DeployCommand extends BaseCmd
{
    use StreamingOutputTrait;

    protected $name = 'deploy:production';

    protected function process()
    {
        $stages = [
            'Pre-deployment checks',
            'Building assets',
            'Running tests',
            'Database backup',
            'Uploading files',
            'Running migrations',
            'Cache warming',
            'Post-deployment cleanup'
        ];

        $this->info('Starting deployment...');

        // Create section for each stage
        $sections = [];
        foreach ($stages as $stage) {
            $section = $this->createSection();
            $section->write("⏳ {$stage}: Pending");
            $sections[$stage] = $section;
        }

        $this->info(''); // Spacer

        foreach ($stages as $stage) {
            $sections[$stage]->overwrite("▶️  {$stage}: Running...");

            try {
                $duration = $this->runStage($stage);
                $sections[$stage]->overwrite(sprintf(
                    "✅ %s: Completed (%.2fs)",
                    $stage,
                    $duration
                ));
            } catch (\Exception $e) {
                $sections[$stage]->overwrite("❌ {$stage}: Failed");
                $this->error("\nDeployment failed at: {$stage}");
                $this->error("Error: " . $e->getMessage());
                return 1;
            }
        }

        $this->info("\n✅ Deployment completed successfully!");

        return 0;
    }

    protected function runStage(string $stage): float
    {
        $start = microtime(true);

        // Simulate stage work
        match ($stage) {
            'Pre-deployment checks' => $this->preDeploymentChecks(),
            'Building assets' => $this->buildAssets(),
            'Running tests' => $this->runTests(),
            'Database backup' => $this->backupDatabase(),
            'Uploading files' => $this->uploadFiles(),
            'Running migrations' => $this->runMigrations(),
            'Cache warming' => $this->warmCache(),
            'Post-deployment cleanup' => $this->cleanup(),
            default => sleep(1)
        };

        return microtime(true) - $start;
    }
}
```

### Example 4: Buffered Output for Testing

```php
class DataMigrationCommand extends BaseCmd
{
    use StreamingOutputTrait;

    protected $name = 'migrate:data';

    protected function process()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->enableBuffering();
            $this->info('DRY RUN MODE - No changes will be made');
        }

        $this->startProgress(100, 'Migrating data...');

        $errors = [];

        for ($i = 1; $i <= 100; $i++) {
            try {
                if (!$dryRun) {
                    $this->migrateRecord($i);
                }

                $this->stream("✅ Migrated record {$i}");
                $this->advanceProgress(1);

            } catch (\Exception $e) {
                $errors[] = "Record {$i}: " . $e->getMessage();
                $this->stream("❌ Failed record {$i}");
                $this->advanceProgress(1);
            }
        }

        $this->finishProgress();

        if ($dryRun) {
            // Show what would happen
            $buffer = $this->getStreamingOutput()->getBuffer();
            $this->info("\nDry run results ({count($buffer)} operations):");
            $this->flushOutput();
        }

        if (!empty($errors)) {
            $this->error("\nErrors encountered:");
            foreach ($errors as $error) {
                $this->error("  - {$error}");
            }
            return 1;
        }

        return 0;
    }
}
```

## Best Practices

### 1. Use Progress Bars for Long Operations

```php
// ✅ Good - Shows progress
$this->startProgress(count($items));
foreach ($items as $item) {
    $this->processItem($item);
    $this->advanceProgress();
}
$this->finishProgress();

// ❌ Avoid - No feedback
foreach ($items as $item) {
    $this->processItem($item);
}
```

### 2. Update Progress Messages

```php
// ✅ Good - Informative
$this->advanceProgress(1, "Processing user: {$user->name}");

// ❌ Avoid - Generic
$this->advanceProgress(1);
```

### 3. Use Sections for Complex Output

```php
// ✅ Good - Clear organization
$statusSection = $this->createSection();
$logSection = $this->createSection();

$statusSection->write('Status: Processing...');
$logSection->write('Processing item 1');
$logSection->write('Processing item 2');
$statusSection->overwrite('Status: Completed');

// ❌ Avoid - Mixed output
$this->stream('Status: Processing...');
$this->stream('Processing item 1');
$this->stream('Status: Completed'); // Confusing
```

### 4. Buffer for Conditional Output

```php
// ✅ Good - Show only if successful
$this->enableBuffering();
$this->stream('Verbose output...');

if ($success) {
    $this->flushOutput();
}

// ❌ Avoid - Always shows
$this->stream('Verbose output...');
if (!$success) {
    // Can't take it back
}
```

### 5. Use Events for Monitoring

```php
// ✅ Good - Track progress
$processed = 0;
$this->onStreamEvent('progress.advance', function($data) use (&$processed) {
    $processed = $data['current'];
    if ($processed % 100 === 0) {
        $this->checkpoint($processed);
    }
});

// ❌ Avoid - Manual tracking
$processed = 0;
foreach ($items as $item) {
    $this->processItem($item);
    $processed++;
    if ($processed % 100 === 0) {
        $this->checkpoint($processed);
    }
}
```

## Troubleshooting

### Progress Bar Not Showing

**Problem**: Progress bar doesn't appear

**Solutions**:
- Ensure you called `startProgress()`
- Check output verbosity isn't quiet
- Verify max value is > 0
- Call `finishProgress()` when done

### Output Appears Late

**Problem**: Output delayed or appears all at once

**Solutions**:
- Check if buffering is enabled
- Call `flush()` to force output
- Use unbuffered mode for real-time output

### Section Updates Not Working

**Problem**: Section overwrite doesn't update

**Solutions**:
- Ensure output supports sections (ConsoleSectionOutput)
- Check you're calling `overwrite()` not `write()`
- Verify terminal supports ANSI codes

## API Reference

### StreamingOutput Methods

```php
// Output
public function write(string $message, bool $newline = true): void
public function writeLines(array $lines): void
public function writef(string $format, ...$args): void

// Progress
public function startProgress(int $max, ?string $message = null): ProgressBar
public function advanceProgress(int $step = 1, ?string $message = null): void
public function setProgress(int $current, ?string $message = null): void
public function finishProgress(): void

// Buffering
public function flush(): void
public function getBuffer(): array
public function clearBuffer(): void
public function enableBuffering(): void
public function disableBuffering(): void
public function isBuffered(): bool
public function setMaxBufferSize(int $size): void

// Events
public function on(string $event, callable $callback): void

// Sections
public function section(): SectionOutput

// Statistics
public function getStats(): array

// Access
public function getOutput(): OutputInterface
```

### SectionOutput Methods

```php
public function write(string $content, bool $newline = true): void
public function overwrite(string|array $content): void
public function clear(): void
public function getLines(): array
public function getSection(): ConsoleSectionOutput
```

## Summary

The Command Output Streaming system provides:

- ✅ **Real-Time**: Immediate output for long-running commands
- ✅ **Visual**: Progress bars and formatted output
- ✅ **Flexible**: Buffered and unbuffered modes
- ✅ **Organized**: Section-based output
- ✅ **Monitored**: Event callbacks and statistics
- ✅ **Easy**: Simple trait integration

Build better user experiences for long-running CLI commands!
