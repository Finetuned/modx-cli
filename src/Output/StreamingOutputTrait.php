<?php

namespace MODX\CLI\Output;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Streaming Output Trait
 *
 * Provides convenience methods for commands to use streaming output capabilities.
 */
trait StreamingOutputTrait
{
    /**
     * Streaming output instance
     */
    protected ?StreamingOutput $streamingOutput = null;

    /**
     * Get or create streaming output instance
     *
     * @param bool $buffered Whether to enable buffering
     * @return StreamingOutput
     */
    protected function getStreamingOutput(bool $buffered = false): StreamingOutput
    {
        if ($this->streamingOutput === null) {
            $this->streamingOutput = new StreamingOutput($this->output, $buffered);
        }

        return $this->streamingOutput;
    }

    /**
     * Stream a line of output
     *
     * @param string $message The message to stream
     * @param bool $newline Whether to add a newline
     * @return void
     */
    protected function stream(string $message, bool $newline = true): void
    {
        $this->getStreamingOutput()->write($message, $newline);
    }

    /**
     * Stream multiple lines
     *
     * @param array<int, string> $lines Lines to stream
     * @return void
     */
    protected function streamLines(array $lines): void
    {
        $this->getStreamingOutput()->writeLines($lines);
    }

    /**
     * Stream formatted output
     *
     * @param string $format Format string
     * @param mixed ...$args Format arguments
     * @return void
     */
    protected function streamf(string $format, mixed ...$args): void
    {
        $this->getStreamingOutput()->writef($format, ...$args);
    }

    /**
     * Start a progress bar
     *
     * @param int $max Maximum progress value
     * @param string|null $message Optional message
     * @return ProgressBar
     */
    protected function startProgress(int $max, ?string $message = null): ProgressBar
    {
        return $this->getStreamingOutput()->startProgress($max, $message);
    }

    /**
     * Advance progress bar
     *
     * @param int $step Steps to advance
     * @param string|null $message Optional message update
     * @return void
     */
    protected function advanceProgress(int $step = 1, ?string $message = null): void
    {
        $this->getStreamingOutput()->advanceProgress($step, $message);
    }

    /**
     * Set progress to specific value
     *
     * @param int $current Current progress value
     * @param string|null $message Optional message update
     * @return void
     */
    protected function setProgress(int $current, ?string $message = null): void
    {
        $this->getStreamingOutput()->setProgress($current, $message);
    }

    /**
     * Finish progress bar
     *
     * @return void
     */
    protected function finishProgress(): void
    {
        $this->getStreamingOutput()->finishProgress();
    }

    /**
     * Create an output section
     *
     * @return OutputInterface
     */
    protected function createSection(): OutputInterface
    {
        return $this->getStreamingOutput()->section();
    }

    /**
     * Enable output buffering
     *
     * @return void
     */
    protected function enableBuffering(): void
    {
        $this->getStreamingOutput()->enableBuffering();
    }

    /**
     * Disable output buffering and flush
     *
     * @return void
     */
    protected function disableBuffering(): void
    {
        $this->getStreamingOutput()->disableBuffering();
    }

    /**
     * Flush buffered output
     *
     * @return void
     */
    protected function flushOutput(): void
    {
        $this->getStreamingOutput()->flush();
    }

    /**
     * Get streaming statistics
     *
     * @return array{lines: int, bytes: int, duration: float, rate: float}
     */
    protected function getStreamingStats(): array
    {
        return $this->getStreamingOutput()->getStats();
    }

    /**
     * Register a stream event callback
     *
     * @param string $event Event name
     * @param callable $callback Callback function
     * @return void
     */
    protected function onStreamEvent(string $event, callable $callback): void
    {
        $this->getStreamingOutput()->on($event, $callback);
    }
}
