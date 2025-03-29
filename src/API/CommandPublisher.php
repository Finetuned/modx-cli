<?php

namespace MODX\CLI\API;

use Symfony\Component\Process\Process;

/**
 * Publisher for asynchronous command execution
 */
class CommandPublisher
{
    /**
     * @var array Subscribers (commands and callbacks)
     */
    private $subscribers = [];

    /**
     * Publish a command with a callback
     *
     * @param string $command The command to execute
     * @param callable $callback The callback to execute after the command
     * @return void
     */
    public function publish(string $command, callable $callback): void
    {
        $this->subscribers[] = compact('command', 'callback');
    }

    /**
     * Run all published commands asynchronously
     *
     * @return void
     */
    public function run(): void
    {
        $processes = [];
        $callbacks = [];

        // Start all processes
        foreach ($this->subscribers as $index => $subscriber) {
            $cmdString = 'modx ' . $subscriber['command'];
            $process = new Process(explode(' ', $cmdString));
            $process->start();
            $processes[$index] = $process;
            $callbacks[$index] = $subscriber['callback'];
        }

        // Wait for all processes to complete
        while (count($processes) > 0) {
            foreach ($processes as $index => $process) {
                if (!$process->isRunning()) {
                    // Process completed
                    $callback = $callbacks[$index];

                    if ($process->isSuccessful()) {
                        $callback([
                            'success' => true,
                            'data' => $process->getOutput(),
                        ]);
                    } else {
                        $callback([
                            'success' => false,
                            'error' => $process->getErrorOutput(),
                        ]);
                    }

                    unset($processes[$index]);
                    unset($callbacks[$index]);
                }
            }

            // Small delay to prevent CPU hogging
            usleep(100000); // 100ms
        }
    }

    /**
     * Execute a command asynchronously
     *
     * @param string $command The command to execute
     * @param callable $callback The callback to execute after the command
     * @return callable A function that executes the command when called
     */
    private function executeAsync(string $command, callable $callback): callable
    {
        return function() use ($command, $callback) {
            \MODX\CLI\API\MODX_CLI::log("Running command asynchronously: modx $command");

            $result = \MODX\CLI\API\MODX_CLI::run_command($command, [], [
                'return' => true,
                'exit_error' => false
            ]);

            if ($result->return_code !== 0) {
                $callback([
                    'success' => false,
                    'error' => $result->stderr,
                ]);
            } else {
                $callback([
                    'success' => true,
                    'data' => $result->stdout,
                ]);
            }
        };
    }
}
