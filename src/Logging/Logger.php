<?php

namespace MODX\CLI\Logging;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * Enhanced PSR-3 compliant logger for MODX CLI
 *
 * Supports multiple log levels, file and console output, and log rotation.
 */
class Logger extends AbstractLogger
{
    /**
     * @var int Current verbosity level
     */
    private $verbosity = self::VERBOSITY_NORMAL;

    /**
     * @var string|null Log file path
     */
    private $logFile = null;

    /**
     * @var resource|null File handle
     */
    private $fileHandle = null;

    /**
     * @var bool Whether to output to console
     */
    private $consoleOutput = true;

    /**
     * @var callable|null Output callback for console
     */
    private $outputCallback = null;

    /**
     * @var int Maximum log file size in bytes (default 10MB)
     */
    private $maxFileSize = 10485760;

    /**
     * @var int Number of rotated files to keep
     */
    private $maxFiles = 5;

    // Verbosity levels
    const VERBOSITY_QUIET = 0;
    const VERBOSITY_NORMAL = 1;
    const VERBOSITY_VERBOSE = 2;
    const VERBOSITY_VERY_VERBOSE = 3;
    const VERBOSITY_DEBUG = 4;

    /**
     * @var array Map log levels to verbosity requirements
     */
    private static $levelVerbosity = [
        LogLevel::EMERGENCY => self::VERBOSITY_QUIET,
        LogLevel::ALERT => self::VERBOSITY_QUIET,
        LogLevel::CRITICAL => self::VERBOSITY_QUIET,
        LogLevel::ERROR => self::VERBOSITY_QUIET,
        LogLevel::WARNING => self::VERBOSITY_NORMAL,
        LogLevel::NOTICE => self::VERBOSITY_NORMAL,
        LogLevel::INFO => self::VERBOSITY_VERBOSE,
        LogLevel::DEBUG => self::VERBOSITY_DEBUG,
    ];

    /**
     * @var array Console color codes for log levels
     */
    private static $levelColors = [
        LogLevel::EMERGENCY => "\033[1;37;41m", // White on red background
        LogLevel::ALERT => "\033[1;31m",        // Bold red
        LogLevel::CRITICAL => "\033[0;31m",     // Red
        LogLevel::ERROR => "\033[0;31m",        // Red
        LogLevel::WARNING => "\033[0;33m",      // Yellow
        LogLevel::NOTICE => "\033[0;36m",       // Cyan
        LogLevel::INFO => "\033[0;32m",         // Green
        LogLevel::DEBUG => "\033[0;37m",        // Gray
    ];

    /**
     * Constructor
     *
     * @param int $verbosity Verbosity level
     * @param string|null $logFile Log file path
     * @param callable|null $outputCallback Output callback
     */
    public function __construct(int $verbosity = self::VERBOSITY_NORMAL, ?string $logFile = null, ?callable $outputCallback = null)
    {
        $this->verbosity = $verbosity;
        $this->outputCallback = $outputCallback;

        if ($logFile) {
            $this->setLogFile($logFile);
        }
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed $level
     * @param string $message
     * @param array $context
     * @return void
     */
    public function log($level, $message, array $context = array()): void
    {
        // Check if this level should be logged at current verbosity
        if (!$this->shouldLog($level)) {
            return;
        }

        // Interpolate context into message
        $message = $this->interpolate($message, $context);

        // Format the log entry
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = sprintf('[%s] %s: %s', $timestamp, strtoupper($level), $message);

        // Write to file if configured
        if ($this->logFile) {
            $this->writeToFile($formattedMessage . PHP_EOL);
        }

        // Output to console if enabled
        if ($this->consoleOutput) {
            $this->writeToConsole($level, $formattedMessage);
        }
    }

    /**
     * Set verbosity level
     *
     * @param int $verbosity Verbosity level
     * @return void
     */
    public function setVerbosity(int $verbosity): void
    {
        $this->verbosity = $verbosity;
    }

    /**
     * Get verbosity level
     *
     * @return int
     */
    public function getVerbosity(): int
    {
        return $this->verbosity;
    }

    /**
     * Set log file
     *
     * @param string $logFile Log file path
     * @return void
     */
    public function setLogFile(string $logFile): void
    {
        // Close existing file handle
        if ($this->fileHandle) {
            fclose($this->fileHandle);
        }

        $this->logFile = $logFile;

        // Check if file needs rotation
        $this->rotateIfNeeded();

        // Open file for appending
        $dir = dirname($logFile);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->fileHandle = fopen($logFile, 'a');
    }

    /**
     * Get log file path
     *
     * @return string|null
     */
    public function getLogFile(): ?string
    {
        return $this->logFile;
    }

    /**
     * Enable/disable console output
     *
     * @param bool $enabled
     * @return void
     */
    public function setConsoleOutput(bool $enabled): void
    {
        $this->consoleOutput = $enabled;
    }

    /**
     * Set output callback
     *
     * @param callable $callback
     * @return void
     */
    public function setOutputCallback(callable $callback): void
    {
        $this->outputCallback = $callback;
    }

    /**
     * Set maximum file size for rotation
     *
     * @param int $size Size in bytes
     * @return void
     */
    public function setMaxFileSize(int $size): void
    {
        $this->maxFileSize = $size;
    }

    /**
     * Set maximum number of rotated files to keep
     *
     * @param int $count Number of files
     * @return void
     */
    public function setMaxFiles(int $count): void
    {
        $this->maxFiles = $count;
    }

    /**
     * Check if a log level should be logged at current verbosity
     *
     * @param string $level Log level
     * @return bool
     */
    private function shouldLog(string $level): bool
    {
        $required = self::$levelVerbosity[$level] ?? self::VERBOSITY_NORMAL;
        return $this->verbosity >= $required;
    }

    /**
     * Interpolate context values into message placeholders
     *
     * @param string $message Message with placeholders
     * @param array $context Context values
     * @return string
     */
    private function interpolate(string $message, array $context = []): string
    {
        // Build replacement array with braces around keys
        $replace = [];
        foreach ($context as $key => $val) {
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        // Interpolate replacement values into message
        return strtr($message, $replace);
    }

    /**
     * Write message to file
     *
     * @param string $message Formatted message
     * @return void
     */
    private function writeToFile(string $message): void
    {
        if ($this->fileHandle) {
            fwrite($this->fileHandle, $message);
            fflush($this->fileHandle);

            // Check if rotation is needed after write
            $this->rotateIfNeeded();
        }
    }

    /**
     * Write message to console
     *
     * @param string $level Log level
     * @param string $message Formatted message
     * @return void
     */
    private function writeToConsole(string $level, string $message): void
    {
        // Add color if terminal supports it
        $color = self::$levelColors[$level] ?? '';
        $reset = "\033[0m";

        $coloredMessage = $color . $message . $reset;

        if ($this->outputCallback) {
            call_user_func($this->outputCallback, $coloredMessage);
        } else {
            echo $coloredMessage . PHP_EOL;
        }
    }

    /**
     * Rotate log file if it exceeds maximum size
     *
     * @return void
     */
    private function rotateIfNeeded(): void
    {
        if (!$this->logFile || !file_exists($this->logFile)) {
            return;
        }

        $size = filesize($this->logFile);
        if ($size < $this->maxFileSize) {
            return;
        }

        // Close current file handle
        if ($this->fileHandle) {
            fclose($this->fileHandle);
            $this->fileHandle = null;
        }

        // Rotate files
        for ($i = $this->maxFiles - 1; $i >= 1; $i--) {
            $old = $this->logFile . '.' . $i;
            $new = $this->logFile . '.' . ($i + 1);

            if (file_exists($old)) {
                if ($i == $this->maxFiles - 1) {
                    unlink($old); // Delete oldest file
                } else {
                    rename($old, $new);
                }
            }
        }

        // Rename current log file
        if (file_exists($this->logFile)) {
            rename($this->logFile, $this->logFile . '.1');
        }

        // Re-open new log file
        $this->fileHandle = fopen($this->logFile, 'a');
    }

    /**
     * Destructor - close file handle
     */
    public function __destruct()
    {
        if ($this->fileHandle) {
            fclose($this->fileHandle);
        }
    }
}
