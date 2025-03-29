<?php

namespace MODX\CLI\API;

use Symfony\Component\Console\Command\Command;

/**
 * Registry for command storage and retrieval
 */
class CommandRegistry
{
    /**
     * @var array Registered commands
     */
    private $commands = [];

    /**
     * Register a command
     *
     * @param string $name The command name
     * @param callable|object|string $callable The command implementation
     * @param array $args Additional arguments for the command
     * @return bool True on success, false if deferred
     * @throws \Exception If registration fails
     */
    public function register($name, $callable, array $args = [])
    {
        // Check if command is deferred
        if (!empty($args['is_deferred'])) {
            $this->commands[$name] = [
                'callable' => $callable,
                'args' => $args,
                'deferred' => true
            ];
            return false;
        }

        // Create command instance
        $command = $this->createCommand($name, $callable, $args);
        
        // Store command
        $this->commands[$name] = [
            'callable' => $callable,
            'args' => $args,
            'instance' => $command,
            'deferred' => false
        ];

        return true;
    }

    /**
     * Unregister a command
     *
     * @param string $name The command name
     * @return bool True if command was unregistered, false if it didn't exist
     */
    public function unregister($name)
    {
        if (isset($this->commands[$name])) {
            unset($this->commands[$name]);
            return true;
        }
        return false;
    }

    /**
     * Get a command by name
     *
     * @param string $name The command name
     * @return Command|null The command instance or null if not found
     */
    public function get($name)
    {
        if (isset($this->commands[$name])) {
            $command = $this->commands[$name];
            
            // If command is deferred, create it now
            if ($command['deferred']) {
                $instance = $this->createCommand($name, $command['callable'], $command['args']);
                $this->commands[$name]['instance'] = $instance;
                $this->commands[$name]['deferred'] = false;
                return $instance;
            }
            
            return $command['instance'];
        }
        return null;
    }

    /**
     * Check if a command exists
     *
     * @param string $name The command name
     * @return bool True if command exists, false otherwise
     */
    public function has($name)
    {
        return isset($this->commands[$name]);
    }

    /**
     * Get all registered commands
     *
     * @return Command[] Array of command instances
     */
    public function getAll()
    {
        $commands = [];
        
        foreach ($this->commands as $name => $command) {
            // If command is deferred, create it now
            if ($command['deferred']) {
                $instance = $this->createCommand($name, $command['callable'], $command['args']);
                $this->commands[$name]['instance'] = $instance;
                $this->commands[$name]['deferred'] = false;
                $commands[] = $instance;
            } else {
                $commands[] = $command['instance'];
            }
        }
        
        return $commands;
    }

    /**
     * Create a command instance
     *
     * @param string $name The command name
     * @param callable|object|string $callable The command implementation
     * @param array $args Additional arguments for the command
     * @return Command The command instance
     * @throws \Exception If command creation fails
     */
    private function createCommand($name, $callable, array $args = [])
    {
        // Handle different types of callables
        if (is_string($callable) && class_exists($callable)) {
            // Class name
            $command = new $callable();
        } elseif (is_object($callable) && $callable instanceof Command) {
            // Command instance
            $command = $callable;
        } elseif (is_callable($callable)) {
            // Closure or callable
            $command = new ClosureCommand($name, $callable);
        } else {
            throw new \Exception("Invalid command implementation for '$name'");
        }

        // Set command properties
        if ($command instanceof Command) {
            // Set description
            if (!empty($args['shortdesc'])) {
                $command->setDescription($args['shortdesc']);
            }
            
            // Set help text
            if (!empty($args['longdesc'])) {
                $command->setHelp($args['longdesc']);
            }
            
            // Set hooks
            if ($command instanceof HookableCommand) {
                if (!empty($args['before_invoke'])) {
                    $command->setBeforeInvoke($args['before_invoke']);
                }
                
                if (!empty($args['after_invoke'])) {
                    $command->setAfterInvoke($args['after_invoke']);
                }
            }
            
            return $command;
        }
        
        throw new \Exception("Failed to create command instance for '$name'");
    }
}
