<?php

namespace MODX\CLI\API;

use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Utility for running commands programmatically
 */
class CommandRunner
{
    /**
     * @var \MODX\CLI\Application The application instance
     */
    private $application;

    /**
     * @var HookRegistry The hook registry
     */
    private $hookRegistry;

    /**
     * Create a new command runner
     *
     * @param \MODX\CLI\Application $application The application instance
     * @param HookRegistry $hookRegistry The hook registry
     */
    public function __construct($application, HookRegistry $hookRegistry)
    {
        $this->application = $application;
        $this->hookRegistry = $hookRegistry;
    }

    /**
     * Run a command
     *
     * @param string $command The command name
     * @param array $args Command arguments
     * @param array $options {
     *     Optional. An associative array of options for command execution.
     *
     *     @type bool $return Whether to return the command result
     *     @type bool $exit_error Whether to exit on error
     *     @type bool $parse Whether to parse the command string
     * }
     * @return mixed Command result if $return is true
     * @throws \Exception If command execution fails and $exit_error is true
     */
    public function run($command, array $args = [], array $options = [])
    {
        // Parse the command if needed
        if (!empty($options['parse']) && is_string($command)) {
            list($command, $parsedArgs) = $this->parseCommand($command);
            $args = array_merge($parsedArgs, $args);
        }

        // Find the command
        try {
            $cmd = $this->application->find($command);
        } catch (\Exception $e) {
            if (!empty($options['exit_error'])) {
                throw $e;
            }

            $result = new \stdClass();
            $result->stdout = '';
            $result->stderr = $e->getMessage();
            $result->return_code = 1;

            return !empty($options['return']) ? $result : $result->return_code;
        }

        // Prepare input
        $inputArgs = $args;
        if (!isset($inputArgs['command'])) {
            $inputArgs = array_merge(['command' => $command], $inputArgs);
        }
        $input = new ArrayInput($inputArgs);

        // Prepare output
        $output = new BufferedOutput();

        // Set up result object
        $result = new \stdClass();
        $result->stdout = '';
        $result->stderr = '';
        $result->return_code = 0;

        try {
            // Run before_invoke hooks if registered
            $this->hookRegistry->run('before_invoke', [$command, $args]);
            $this->hookRegistry->run("before_invoke:$command", [$args]);

            // Execute the command
            $return_code = $cmd->run($input, $output);
            $result->stdout = $output->fetch();
            $result->return_code = $return_code;

            // Run after_invoke hooks if registered
            $this->hookRegistry->run('after_invoke', [$command, $args, $result]);
            $this->hookRegistry->run("after_invoke:$command", [$args, $result]);
        } catch (\Exception $e) {
            $result->stderr = $e->getMessage();
            $result->return_code = 1;

            if (!empty($options['exit_error'])) {
                throw $e;
            }
        }

        // Always return the result object if 'return' is true
        if (!empty($options['return'])) {
            return $result;
        }

        // Otherwise, return 0 for success (for backward compatibility with tests)
        return 0;
    }

    /**
     * Parse a command string into command name and arguments
     *
     * @param string $command The command string
     * @return array Array containing command name and arguments
     */
    private function parseCommand($command)
    {
        $args = [];
        $parts = explode(' ', $command);
        $name = array_shift($parts);

        foreach ($parts as $part) {
            if (empty($part)) {
                continue;
            }

            // Handle --option=value
            if (preg_match('/^--([^=]+)=(.*)$/', $part, $matches)) {
                $args['--' . $matches[1]] = $matches[2];
                continue;
            }

            // Handle --option
            if (preg_match('/^--(.+)$/', $part, $matches)) {
                $args['--' . $matches[1]] = true;
                continue;
            }

            // Handle -o
            if (preg_match('/^-([a-zA-Z])$/', $part, $matches)) {
                $args['-' . $matches[1]] = true;
                continue;
            }

            // Handle positional arguments
            $args[] = $part;
        }

        return [$name, $args];
    }
}
