<?php

namespace MODX\CLI\Command;

use MODX\CLI\API\MODX_CLI;
use MODX\CLI\API\CommandPublisher;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Run a sequence of commands
 */
class RunSequence extends BaseCmd
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'run-sequence';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Run a sequence of commands with various execution options';

    /**
     * {@inheritdoc}
     */
    protected $help = 'This command allows you to run multiple commands in sequence with various execution options.';

    /**
     * {@inheritdoc}
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'command_sets',
                null,
                InputOption::VALUE_REQUIRED,
                'JSON string containing command sets to execute'
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array_merge(parent::getArguments(), [
            [
                'command',
                \Symfony\Component\Console\Input\InputArgument::OPTIONAL,
                'The command to execute'
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function process()
    {
        // Parse command sets from input
        $command_sets = json_decode($this->option('command_sets') ?? '{}', true);

        if (empty($command_sets)) {
            $this->error("No command sets provided. Pass them as a JSON string using --command_sets.");
            return 1;
        }

        // Iterate over each command set
        foreach ($command_sets as $set_name => $set_config) {
            $this->line("Executing command set: $set_name");

            // Extract configuration options with defaults
            $continue_after_error = $set_config['continue_after_error'] ?? true;
            $is_asynchronous = $set_config['is_asynchronous'] ?? true;
            $collates_errors = $set_config['collates_errors'] ?? true;
            $collates_data_responses = $set_config['collates_data_responses'] ?? true;
            $returns_results_as_json = $set_config['returns_results_as_json'] ?? true;
            $commands = $set_config['commands'] ?? [];

            if (empty($commands)) {
                $this->comment("No commands found in set: $set_name. Skipping...");
                continue;
            }

            // Initialize result containers
            $errors = [];
            $data_responses = [];

            if ($is_asynchronous) {
                // Use Pub/Sub pattern for asynchronous execution
                $publisher = new CommandPublisher();

                foreach ($commands as $command) {
                    $publisher->publish($command, function ($result) use (
                        &$errors,
                        &$data_responses,
                        $collates_errors,
                        $collates_data_responses,
                        $continue_after_error,
                        $command
                    ) {
                        if ($result['success']) {
                            if ($collates_data_responses) {
                                $data_responses[] = $result['data'];
                            }
                            $this->info("Command succeeded: modx $command");
                        } else {
                            if ($collates_errors) {
                                $errors[] = $result['error'];
                            }
                            $this->error("Command failed: modx $command");
                            $this->error("Error: " . $result['error']);

                            if (!$continue_after_error) {
                                $this->error("Execution stopped due to error: " . $result['error']);
                                return false;
                            }
                        }
                        return true;
                    });
                }

                $publisher->run(); // Execute all published commands
            } else {
                // Use a standard foreach loop for synchronous execution
                foreach ($commands as $command) {
                    $this->line("Running command: modx $command");

                    $result = MODX_CLI::run_command($command, [], [
                        'return' => true,
                        'exit_error' => false,
                        'parse' => true
                    ]);

                    if ($result->return_code !== 0) {
                        if ($collates_errors) {
                            $errors[] = $result->stderr;
                        }
                        $this->error("Command failed: modx $command");
                        $this->error("Error: " . $result->stderr);

                        if (!$continue_after_error) {
                            $this->error("Execution stopped due to error: " . $result->stderr);
                            return 1;
                        }
                    } else {
                        if ($collates_data_responses) {
                            $data_responses[] = $result->stdout;
                        }
                        $this->info("Command succeeded: modx $command");
                    }
                }
            }

            // Return results as JSON if configured
            if ($returns_results_as_json) {
                $this->line(json_encode([
                    'set_name' => $set_name,
                    'errors' => $errors,
                    'data_responses' => $data_responses,
                ]));
            }
        }

        $this->info("All command sets have been executed.");
        return 0;
    }
}
