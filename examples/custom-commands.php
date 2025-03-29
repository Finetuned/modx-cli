<?php

/**
 * Example of how to use the MODX CLI Internal API to register custom commands
 * 
 * This file demonstrates various ways to register custom commands with the MODX CLI
 * using the Internal API. It shows how to register simple commands using closures,
 * how to register commands using classes, and how to use hooks.
 * 
 * To use this example, include it in your project or require it in your bootstrap file.
 */

use MODX\CLI\API\MODX_CLI;
use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * 1. Register a simple command using a closure
 * 
 * This is the simplest way to register a command. The closure receives the command
 * arguments and options as parameters.
 */
MODX_CLI::add_command('hello', function($args, $assoc_args) {
    $name = $assoc_args['name'] ?? 'World';
    MODX_CLI::log("Hello, $name!");
    return 0;
}, [
    'shortdesc' => 'Say hello to someone',
    'longdesc' => 'This command says hello to the specified name or "World" if no name is provided.',
]);

/**
 * 2. Register a command with hooks
 * 
 * You can register hooks to run before and after a command is executed.
 */
MODX_CLI::add_command('greet', function($args, $assoc_args) {
    $name = $assoc_args['name'] ?? 'World';
    MODX_CLI::log("Greetings, $name!");
    return 0;
}, [
    'shortdesc' => 'Greet someone',
    'longdesc' => 'This command greets the specified name or "World" if no name is provided.',
    'before_invoke' => function() {
        MODX_CLI::log('About to greet someone...');
    },
    'after_invoke' => function() {
        MODX_CLI::log('Greeting complete!');
    },
]);

/**
 * 3. Register a command using a class
 * 
 * For more complex commands, you can use a class that extends BaseCmd.
 */
class CustomCommand extends BaseCmd
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'custom:command';

    /**
     * {@inheritdoc}
     */
    protected $description = 'A custom command example';

    /**
     * {@inheritdoc}
     */
    protected $help = 'This is an example of a custom command using a class.';

    /**
     * {@inheritdoc}
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'message',
                null,
                InputOption::VALUE_REQUIRED,
                'The message to display'
            ],
            [
                'uppercase',
                'u',
                InputOption::VALUE_NONE,
                'Convert the message to uppercase'
            ]
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function process()
    {
        $message = $this->option('message') ?? 'Hello from custom command!';
        
        if ($this->option('uppercase')) {
            $message = strtoupper($message);
        }
        
        $this->line($message);
        return 0;
    }
}

// Register the class-based command
MODX_CLI::add_command('custom:command', CustomCommand::class);

/**
 * 4. Register global hooks
 * 
 * You can register hooks that run for all commands or specific events.
 */
MODX_CLI::register_hook('after_command_run', function($command, $result) {
    MODX_CLI::log("Command '$command' completed with result code: " . $result->return_code);
});

/**
 * 5. Register command-specific hooks
 * 
 * You can register hooks that run only for specific commands.
 */
MODX_CLI::before_invoke('cache:clear', function() {
    MODX_CLI::log('About to clear the cache...');
});

MODX_CLI::after_invoke('cache:clear', function($result) {
    if ($result->return_code === 0) {
        MODX_CLI::success('Cache cleared successfully!');
    } else {
        MODX_CLI::error('Failed to clear cache: ' . $result->stderr);
    }
});

/**
 * 6. Example of running commands programmatically
 * 
 * You can run commands programmatically using the run_command method.
 */
function run_example_commands()
{
    // Run a simple command
    MODX_CLI::run_command('hello', ['name' => 'John']);
    
    // Run a command and get the result
    $result = MODX_CLI::run_command('custom:command', [
        '--message' => 'This is a test message',
        '--uppercase' => true
    ], ['return' => true]);
    
    if ($result->return_code === 0) {
        MODX_CLI::success('Command executed successfully!');
        MODX_CLI::log('Output: ' . $result->stdout);
    } else {
        MODX_CLI::error('Command failed: ' . $result->stderr);
    }
}

// Uncomment this line to run the example commands
// run_example_commands();
