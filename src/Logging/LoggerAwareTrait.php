<?php

namespace MODX\CLI\Logging;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Logger-aware trait
 *
 * Provides logging capabilities to any class that uses it.
 */
trait LoggerAwareTrait
{
    /**
     * @var LoggerInterface Logger instance
     */
    protected $logger;

    /**
     * Set logger instance
     *
     * @param LoggerInterface $logger
     * @return void
     */
    public function setLogger(LoggerInterface $logger): void
    {
        $this->logger = $logger;
    }

    /**
     * Get logger instance
     *
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        if (!$this->logger) {
            $this->logger = new NullLogger();
        }

        return $this->logger;
    }

    /**
     * Log a debug message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logDebug(string $message, array $context = []): void
    {
        $this->getLogger()->debug($message, $context);
    }

    /**
     * Log an info message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logInfo(string $message, array $context = []): void
    {
        $this->getLogger()->info($message, $context);
    }

    /**
     * Log a notice message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logNotice(string $message, array $context = []): void
    {
        $this->getLogger()->notice($message, $context);
    }

    /**
     * Log a warning message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logWarning(string $message, array $context = []): void
    {
        $this->getLogger()->warning($message, $context);
    }

    /**
     * Log an error message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logError(string $message, array $context = []): void
    {
        $this->getLogger()->error($message, $context);
    }

    /**
     * Log a critical message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logCritical(string $message, array $context = []): void
    {
        $this->getLogger()->critical($message, $context);
    }

    /**
     * Log an alert message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logAlert(string $message, array $context = []): void
    {
        $this->getLogger()->alert($message, $context);
    }

    /**
     * Log an emergency message
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    protected function logEmergency(string $message, array $context = []): void
    {
        $this->getLogger()->emergency($message, $context);
    }
}
