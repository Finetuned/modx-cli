<?php

namespace MODX\CLI\API;

/**
 * Registry for hook storage and execution
 */
class HookRegistry
{
    /**
     * @var array Registered hooks
     */
    private $hooks = [];

    /**
     * Register a hook
     *
     * @param string   $name     The hook name.
     * @param callable $callback The callback to execute.
     * @return boolean True on success.
     */
    public function register(string $name, callable $callback): bool
    {
        if (!isset($this->hooks[$name])) {
            $this->hooks[$name] = [];
        }

        $this->hooks[$name][] = $callback;

        return true;
    }

    /**
     * Unregister a hook
     *
     * @param string        $name     The hook name.
     * @param callable|null $callback The callback to unregister (null to unregister all).
     * @return boolean True if hook was unregistered, false if it didn't exist.
     */
    public function unregister(string $name, ?callable $callback = null): bool
    {
        if (!isset($this->hooks[$name])) {
            return false;
        }

        if ($callback === null) {
            // Unregister all callbacks for this hook
            unset($this->hooks[$name]);
            return true;
        }

        // Find and remove the specific callback
        foreach ($this->hooks[$name] as $i => $registeredCallback) {
            if ($registeredCallback === $callback) {
                unset($this->hooks[$name][$i]);

                // Reindex the array
                $this->hooks[$name] = array_values($this->hooks[$name]);

                // Remove the hook entirely if no callbacks remain
                if (empty($this->hooks[$name])) {
                    unset($this->hooks[$name]);
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Get all callbacks for a hook
     *
     * @param string $name The hook name.
     * @return callable[] Array of callbacks.
     */
    public function get(string $name): array
    {
        return isset($this->hooks[$name]) ? $this->hooks[$name] : [];
    }

    /**
     * Check if a hook exists
     *
     * @param string $name The hook name.
     * @return boolean True if hook exists, false otherwise.
     */
    public function has(string $name): bool
    {
        return isset($this->hooks[$name]) && !empty($this->hooks[$name]);
    }

    /**
     * Get all registered hooks
     *
     * @return array Array of hook names and callbacks.
     */
    public function getAll(): array
    {
        return $this->hooks;
    }

    /**
     * Run all callbacks for a hook
     *
     * @param string $name The hook name.
     * @param array  $args Arguments to pass to the callbacks.
     * @return array Array of results from the callbacks.
     */
    public function run(string $name, array $args = []): array
    {
        $results = [];

        if (isset($this->hooks[$name])) {
            foreach ($this->hooks[$name] as $callback) {
                $results[] = call_user_func_array($callback, $args);
            }
        }

        return $results;
    }
}
