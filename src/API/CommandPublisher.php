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
     * @param string   $command  The command to execute.
     * @param callable $callback The callback to execute after the command.
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
            $process = $this->createProcess($subscriber['command']);
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
     * Create a process for a command
     *
     * @param string $command The command to execute.
     * @return Process The process instance
     */
    protected function createProcess(string $command): Process
    {
        $cmdString = 'modx ' . $command;
        return new Process(explode(' ', $cmdString));
    }
}
