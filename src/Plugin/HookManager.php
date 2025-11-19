<?php

namespace MODX\CLI\Plugin;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Hook Manager
 *
 * Manages the registration and execution of hooks (event listeners)
 * throughout the MODX CLI application lifecycle.
 */
class HookManager
{
    /**
     * Registered hooks
     *
     * @var array<string, array<int, callable>>
     */
    protected array $hooks = [];

    /**
     * Hook priorities
     *
     * @var array<string, array<callable, int>>
     */
    protected array $priorities = [];

    /**
     * Logger instance
     */
    protected LoggerInterface $logger;

    /**
     * Hook execution statistics
     *
     * @var array<string, array{count: int, total_time: float}>
     */
    protected array $stats = [];

    /**
     * Constructor
     *
     * @param LoggerInterface|null $logger Optional logger instance
     */
    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    /**
     * Register a hook
     *
     * @param string $hookName The hook name (e.g., 'command.before', 'command.after')
     * @param callable $handler The hook handler callable
     * @param int $priority Priority (higher = executed first), default 10
     * @return void
     */
    public function register(string $hookName, callable $handler, int $priority = 10): void
    {
        if (!isset($this->hooks[$hookName])) {
            $this->hooks[$hookName] = [];
            $this->priorities[$hookName] = [];
        }

        $this->hooks[$hookName][] = $handler;
        $this->priorities[$hookName][spl_object_hash((object)$handler)] = $priority;

        // Sort hooks by priority (descending)
        $this->sortHooks($hookName);

        $this->logger->debug('Hook registered: {hook}', [
            'hook' => $hookName,
            'priority' => $priority,
            'handler_count' => count($this->hooks[$hookName])
        ]);
    }

    /**
     * Unregister a hook
     *
     * @param string $hookName The hook name
     * @param callable|null $handler Specific handler to remove, or null to remove all
     * @return void
     */
    public function unregister(string $hookName, ?callable $handler = null): void
    {
        if (!isset($this->hooks[$hookName])) {
            return;
        }

        if ($handler === null) {
            // Remove all handlers for this hook
            unset($this->hooks[$hookName]);
            unset($this->priorities[$hookName]);
            $this->logger->debug('All handlers unregistered for hook: {hook}', ['hook' => $hookName]);
        } else {
            // Remove specific handler
            $key = array_search($handler, $this->hooks[$hookName], true);
            if ($key !== false) {
                unset($this->hooks[$hookName][$key]);
                $hash = spl_object_hash((object)$handler);
                unset($this->priorities[$hookName][$hash]);
                $this->logger->debug('Handler unregistered for hook: {hook}', ['hook' => $hookName]);
            }
        }
    }

    /**
     * Execute a hook
     *
     * Calls all registered handlers for the given hook name.
     * Handlers receive the context array and can modify it.
     *
     * @param string $hookName The hook name
     * @param array<string, mixed> $context Context data passed to handlers
     * @return array<string, mixed> Modified context after all handlers have run
     */
    public function execute(string $hookName, array $context = []): array
    {
        if (!isset($this->hooks[$hookName]) || empty($this->hooks[$hookName])) {
            return $context;
        }

        $this->logger->debug('Executing hook: {hook}', [
            'hook' => $hookName,
            'handler_count' => count($this->hooks[$hookName])
        ]);

        $startTime = microtime(true);
        $executedCount = 0;

        foreach ($this->hooks[$hookName] as $handler) {
            try {
                $handlerStart = microtime(true);

                // Execute handler
                $result = call_user_func($handler, $context);

                // If handler returns an array, merge it with context
                if (is_array($result)) {
                    $context = array_merge($context, $result);
                }

                $handlerTime = microtime(true) - $handlerStart;
                $executedCount++;

                $this->logger->debug('Hook handler executed: {hook}', [
                    'hook' => $hookName,
                    'execution_time' => round($handlerTime * 1000, 2) . 'ms'
                ]);

            } catch (\Throwable $e) {
                $this->logger->error('Hook handler failed: {hook}', [
                    'hook' => $hookName,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                // Store error in context
                if (!isset($context['_errors'])) {
                    $context['_errors'] = [];
                }
                $context['_errors'][] = [
                    'hook' => $hookName,
                    'error' => $e->getMessage()
                ];
            }
        }

        $totalTime = microtime(true) - $startTime;

        // Update statistics
        if (!isset($this->stats[$hookName])) {
            $this->stats[$hookName] = ['count' => 0, 'total_time' => 0.0];
        }
        $this->stats[$hookName]['count']++;
        $this->stats[$hookName]['total_time'] += $totalTime;

        $this->logger->debug('Hook execution completed: {hook}', [
            'hook' => $hookName,
            'executed' => $executedCount,
            'total_time' => round($totalTime * 1000, 2) . 'ms'
        ]);

        return $context;
    }

    /**
     * Check if a hook has registered handlers
     *
     * @param string $hookName The hook name
     * @return bool True if hook has handlers
     */
    public function hasHook(string $hookName): bool
    {
        return isset($this->hooks[$hookName]) && !empty($this->hooks[$hookName]);
    }

    /**
     * Get all registered hook names
     *
     * @return array<int, string> Array of hook names
     */
    public function getHookNames(): array
    {
        return array_keys($this->hooks);
    }

    /**
     * Get handler count for a specific hook
     *
     * @param string $hookName The hook name
     * @return int Number of registered handlers
     */
    public function getHandlerCount(string $hookName): int
    {
        return isset($this->hooks[$hookName]) ? count($this->hooks[$hookName]) : 0;
    }

    /**
     * Get execution statistics
     *
     * @return array<string, array{count: int, total_time: float, avg_time: float}>
     */
    public function getStats(): array
    {
        $stats = [];
        foreach ($this->stats as $hookName => $data) {
            $stats[$hookName] = [
                'count' => $data['count'],
                'total_time' => round($data['total_time'] * 1000, 2),
                'avg_time' => round(($data['total_time'] / $data['count']) * 1000, 2)
            ];
        }
        return $stats;
    }

    /**
     * Clear all hooks
     *
     * @return void
     */
    public function clear(): void
    {
        $this->hooks = [];
        $this->priorities = [];
        $this->logger->debug('All hooks cleared');
    }

    /**
     * Sort hooks by priority
     *
     * @param string $hookName The hook name
     * @return void
     */
    protected function sortHooks(string $hookName): void
    {
        if (!isset($this->hooks[$hookName])) {
            return;
        }

        $priorities = $this->priorities[$hookName];

        usort($this->hooks[$hookName], function ($a, $b) use ($priorities) {
            $hashA = spl_object_hash((object)$a);
            $hashB = spl_object_hash((object)$b);

            $priorityA = $priorities[$hashA] ?? 10;
            $priorityB = $priorities[$hashB] ?? 10;

            // Higher priority first
            return $priorityB <=> $priorityA;
        });
    }

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
}
