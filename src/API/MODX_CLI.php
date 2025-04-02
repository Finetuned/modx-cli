<?php

namespace MODX\CLI\API;

use MODX\CLI\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * Main API class for MODX CLI
 */
class MODX_CLI
{
    /**
     * @var self The singleton instance
     */
    private static $instance;

    /**
     * @var CommandRegistry The command registry
     */
    private $commandRegistry;

    /**
     * @var HookRegistry The hook registry
     */
    private $hookRegistry;

    /**
     * @var CommandRunner The command runner
     */
    private $commandRunner;

    /**
     * @var Application The application instance
     */
    private $application;

    /**
     * Private constructor to enforce singleton pattern
     */
    private function __construct()
    {
        $this->commandRegistry = new CommandRegistry();
        $this->hookRegistry = new HookRegistry();
        $this->application = new Application();
        $this->commandRunner = new CommandRunner($this->application, $this->hookRegistry);
    }

    /**
     * Get the singleton instance
     *
     * @return self The singleton instance
     */
    private static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Register a custom command with MODX CLI
     *
     * @param string $name The name of the command (e.g., 'post:list' or 'site:empty')
     * @param callable|object|string $callable The implementation of the command
     * @param array $args {
     *     Optional. An associative array of additional parameters for the command.
     *
     *     @type callable $before_invoke Callback to execute before the command
     *     @type callable $after_invoke Callback to execute after the command
     *     @type string $shortdesc Short description of the command
     *     @type string $longdesc Long description of the command
     *     @type array $synopsis Command arguments and options definition
     *     @type string $when Hook to execute the command on
     *     @type bool $is_deferred Whether command registration is deferred
     * }
     * @return bool True on success, false if deferred
     */
    public static function add_command($name, $callable, $args = [])
    {
        return self::getInstance()->commandRegistry->register($name, $callable, $args);
    }

    /**
     * Remove a command from MODX CLI
     *
     * @param string $name The name of the command to remove
     * @return bool True if command was removed, false if it didn't exist
     */
    public static function remove_command($name)
    {
        return self::getInstance()->commandRegistry->unregister($name);
    }

    /**
     * Get a command by name
     *
     * @param string $name The name of the command
     * @return \Symfony\Component\Console\Command\Command|null The command instance or null if not found
     */
    public static function get_command($name)
    {
        return self::getInstance()->commandRegistry->get($name);
    }

    /**
     * Get all registered commands
     *
     * @return \Symfony\Component\Console\Command\Command[] Array of command instances
     */
    public static function get_commands()
    {
        return self::getInstance()->commandRegistry->getAll();
    }

    /**
     * Run a command registered with MODX CLI
     *
     * @param string $command The command to execute
     * @param array $args Command arguments
     * @param array $options {
     *     Optional. An associative array of options for command execution.
     *
     *     @type bool $return Whether to return the command result
     *     @type bool $exit_error Whether to exit on error
     *     @type bool $parse Whether to parse the command string
     * }
     * @return mixed Command result if $return is true
     */
    public static function run_command($command, $args = [], $options = [])
    {
        return self::getInstance()->commandRunner->run($command, $args, $options);
    }

    /**
     * Register a hook with MODX CLI
     *
     * @param string $hook The hook name
     * @param callable $callback The callback to execute
     * @return bool True on success
     */
    public static function register_hook($hook, $callback)
    {
        return self::getInstance()->hookRegistry->register($hook, $callback);
    }

    /**
     * Add a callback to an existing hook
     *
     * @param string $hook The hook name
     * @param callable $callback The callback to add
     * @return bool True on success
     */
    public static function add_hook($hook, $callback)
    {
        return self::getInstance()->hookRegistry->register($hook, $callback);
    }

    /**
     * Run a hook
     *
     * @param string $hook The hook name
     * @param array $args Arguments to pass to the hook
     * @return array Array of results from the hook callbacks
     */
    public static function do_hook($hook, $args = [])
    {
        return self::getInstance()->hookRegistry->run($hook, $args);
    }

    /**
     * Set a callback to run before a command is executed
     *
     * @param string $command The command name
     * @param callable $callback The callback to execute
     * @return bool True on success
     */
    public static function before_invoke($command, $callback)
    {
        return self::getInstance()->hookRegistry->register("before_invoke:{$command}", $callback);
    }

    /**
     * Set a callback to run after a command is executed
     *
     * @param string $command The command name
     * @param callable $callback The callback to execute
     * @return bool True on success
     */
    public static function after_invoke($command, $callback)
    {
        return self::getInstance()->hookRegistry->register("after_invoke:{$command}", $callback);
    }

    /**
     * Write a message to the console
     *
     * @param string $message The message to write
     * @return void
     */
    public static function log($message)
    {
        echo $message . PHP_EOL;
    }

    /**
     * Write a success message to the console
     *
     * @param string $message The message to write
     * @return void
     */
    public static function success($message)
    {
        echo "\033[32mSuccess: " . $message . "\033[0m" . PHP_EOL;
    }

    /**
     * Write a warning message to the console
     *
     * @param string $message The message to write
     * @return void
     */
    public static function warning($message)
    {
        echo "\033[33mWarning: " . $message . "\033[0m" . PHP_EOL;
    }

    /**
     * Write an error message to the console
     *
     * @param string $message The message to write
     * @return void
     */
    public static function error($message)
    {
        echo "\033[31mError: " . $message . "\033[0m" . PHP_EOL;
    }

    /**
     * Get the application instance
     *
     * @return Application The application instance
     */
    public static function get_application()
    {
        return self::getInstance()->application;
    }
}
