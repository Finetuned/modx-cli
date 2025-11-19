<?php

namespace MODX\CLI\Output;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Streaming Output Handler
 *
 * Provides real-time output capabilities for long-running commands.
 * Supports buffered and unbuffered output modes, progress tracking,
 * and real-time updates.
 */
class StreamingOutput
{
    /**
     * Output interface
     */
    protected OutputInterface $output;

    /**
     * Progress bar instance
     */
    protected ?ProgressBar $progressBar = null;

    /**
     * Output buffer
     *
     * @var array<int, string>
     */
    protected array $buffer = [];

    /**
     * Whether buffering is enabled
     */
    protected bool $buffered = false;

    /**
     * Maximum buffer size (number of lines)
     */
    protected int $maxBufferSize = 1000;

    /**
     * Stream callbacks
     *
     * @var array<string, callable>
     */
    protected array $callbacks = [];

    /**
     * Statistics
     *
     * @var array{lines: int, bytes: int, start_time: float}
     */
    protected array $stats = [
        'lines' => 0,
        'bytes' => 0,
        'start_time' => 0.0
    ];

    /**
     * Constructor
     *
     * @param OutputInterface $output The output interface
     * @param bool $buffered Whether to enable buffering
     */
    public function __construct(OutputInterface $output, bool $buffered = false)
    {
        $this->output = $output;
        $this->buffered = $buffered;
        $this->stats['start_time'] = microtime(true);
    }

    /**
     * Write a line to the output stream
     *
     * @param string $message The message to write
     * @param bool $newline Whether to add a newline
     * @return void
     */
    public function write(string $message, bool $newline = true): void
    {
        // Update statistics
        $this->stats['lines']++;
        $this->stats['bytes'] += strlen($message);

        // Trigger callbacks
        $this->triggerCallback('write', ['message' => $message]);

        if ($this->buffered) {
            $this->addToBuffer($message);
        } else {
            if ($newline) {
                $this->output->writeln($message);
            } else {
                $this->output->write($message);
            }
        }
    }

    /**
     * Write multiple lines to the output stream
     *
     * @param array<int, string> $lines Array of lines to write
     * @return void
     */
    public function writeLines(array $lines): void
    {
        foreach ($lines as $line) {
            $this->write($line);
        }
    }

    /**
     * Write formatted output
     *
     * @param string $format The format string
     * @param mixed ...$args Format arguments
     * @return void
     */
    public function writef(string $format, mixed ...$args): void
    {
        $message = sprintf($format, ...$args);
        $this->write($message);
    }

    /**
     * Start a progress bar
     *
     * @param int $max Maximum progress value
     * @param string|null $message Optional message to display
     * @return ProgressBar
     */
    public function startProgress(int $max, ?string $message = null): ProgressBar
    {
        $this->progressBar = new ProgressBar($this->output, $max);

        if ($message) {
            $this->progressBar->setMessage($message);
        }

        // Customize progress bar format
        $this->progressBar->setFormat(
            '%current%/%max% [%bar%] %percent:3s%% | %elapsed:6s% | %message%'
        );

        $this->progressBar->start();
        $this->triggerCallback('progress.start', ['max' => $max]);

        return $this->progressBar;
    }

    /**
     * Advance the progress bar
     *
     * @param int $step Number of steps to advance
     * @param string|null $message Optional message to update
     * @return void
     */
    public function advanceProgress(int $step = 1, ?string $message = null): void
    {
        if (!$this->progressBar) {
            return;
        }

        if ($message !== null) {
            $this->progressBar->setMessage($message);
        }

        $this->progressBar->advance($step);
        $this->triggerCallback('progress.advance', [
            'step' => $step,
            'current' => $this->progressBar->getProgress()
        ]);
    }

    /**
     * Set progress bar to a specific value
     *
     * @param int $current The current progress value
     * @param string|null $message Optional message to update
     * @return void
     */
    public function setProgress(int $current, ?string $message = null): void
    {
        if (!$this->progressBar) {
            return;
        }

        if ($message !== null) {
            $this->progressBar->setMessage($message);
        }

        $this->progressBar->setProgress($current);
        $this->triggerCallback('progress.set', ['current' => $current]);
    }

    /**
     * Finish the progress bar
     *
     * @return void
     */
    public function finishProgress(): void
    {
        if (!$this->progressBar) {
            return;
        }

        $this->progressBar->finish();
        $this->output->writeln(''); // Add newline after progress bar
        $this->triggerCallback('progress.finish', []);
        $this->progressBar = null;
    }

    /**
     * Add a message to the buffer
     *
     * @param string $message The message to buffer
     * @return void
     */
    protected function addToBuffer(string $message): void
    {
        $this->buffer[] = $message;

        // Trim buffer if it exceeds max size
        if (count($this->buffer) > $this->maxBufferSize) {
            array_shift($this->buffer);
        }
    }

    /**
     * Flush the buffer to output
     *
     * @return void
     */
    public function flush(): void
    {
        if (empty($this->buffer)) {
            return;
        }

        foreach ($this->buffer as $line) {
            $this->output->writeln($line);
        }

        $this->buffer = [];
        $this->triggerCallback('flush', ['lines' => count($this->buffer)]);
    }

    /**
     * Get the buffer contents
     *
     * @return array<int, string>
     */
    public function getBuffer(): array
    {
        return $this->buffer;
    }

    /**
     * Clear the buffer
     *
     * @return void
     */
    public function clearBuffer(): void
    {
        $this->buffer = [];
    }

    /**
     * Register a callback for stream events
     *
     * Supported events: 'write', 'flush', 'progress.start', 'progress.advance',
     * 'progress.set', 'progress.finish'
     *
     * @param string $event The event name
     * @param callable $callback The callback function
     * @return void
     */
    public function on(string $event, callable $callback): void
    {
        if (!isset($this->callbacks[$event])) {
            $this->callbacks[$event] = [];
        }

        $this->callbacks[$event][] = $callback;
    }

    /**
     * Trigger a callback
     *
     * @param string $event The event name
     * @param array<string, mixed> $data Event data
     * @return void
     */
    protected function triggerCallback(string $event, array $data = []): void
    {
        if (!isset($this->callbacks[$event])) {
            return;
        }

        foreach ($this->callbacks[$event] as $callback) {
            try {
                call_user_func($callback, $data, $this);
            } catch (\Throwable $e) {
                // Silently ignore callback errors
            }
        }
    }

    /**
     * Get streaming statistics
     *
     * @return array{lines: int, bytes: int, duration: float, rate: float}
     */
    public function getStats(): array
    {
        $duration = microtime(true) - $this->stats['start_time'];

        return [
            'lines' => $this->stats['lines'],
            'bytes' => $this->stats['bytes'],
            'duration' => round($duration, 2),
            'rate' => $duration > 0 ? round($this->stats['lines'] / $duration, 2) : 0
        ];
    }

    /**
     * Enable buffering
     *
     * @return void
     */
    public function enableBuffering(): void
    {
        $this->buffered = true;
    }

    /**
     * Disable buffering (auto-flushes buffer)
     *
     * @return void
     */
    public function disableBuffering(): void
    {
        if ($this->buffered && !empty($this->buffer)) {
            $this->flush();
        }
        $this->buffered = false;
    }

    /**
     * Check if buffering is enabled
     *
     * @return bool
     */
    public function isBuffered(): bool
    {
        return $this->buffered;
    }

    /**
     * Set maximum buffer size
     *
     * @param int $size Maximum number of lines
     * @return void
     */
    public function setMaxBufferSize(int $size): void
    {
        $this->maxBufferSize = $size;
    }

    /**
     * Get the underlying output interface
     *
     * @return OutputInterface
     */
    public function getOutput(): OutputInterface
    {
        return $this->output;
    }

    /**
     * Create a section output for nested/hierarchical output
     *
     * Sections allow for independent output areas that can be updated separately.
     *
     * @return SectionOutput
     */
    public function section(): SectionOutput
    {
        return new SectionOutput($this->output->section());
    }
}
