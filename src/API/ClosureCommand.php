<?php

namespace MODX\CLI\API;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command that wraps a closure
 */
class ClosureCommand extends Command implements HookableCommand
{
    /**
     * @var callable The closure to execute
     */
    private $closure;

    /**
     * @var callable|null Before invoke hook
     */
    private $beforeInvoke;

    /**
     * @var callable|null After invoke hook
     */
    private $afterInvoke;

    /**
     * @var array Arguments configuration
     */
    private $argumentsConfig = [];

    /**
     * @var array Options configuration
     */
    private $optionsConfig = [];

    /**
     * Create a new closure command
     *
     * @param string   $name    The command name.
     * @param callable $closure The closure to execute.
     * @param array    $config  Optional configuration for arguments and options.
     */
    public function __construct(string $name, callable $closure, array $config = [])
    {
        parent::__construct($name);
        $this->closure = $closure;

        // Store configuration
        if (isset($config['arguments'])) {
            $this->argumentsConfig = $config['arguments'];
        }
        if (isset($config['options'])) {
            $this->optionsConfig = $config['options'];
        }

        $this->configure();
    }

    /**
     * Configure the command
     *
     * @return void
     */
    protected function configure(): void
    {
        parent::configure();

        // Configure arguments from config
        foreach ($this->argumentsConfig as $argument) {
            $mode = InputArgument::OPTIONAL;
            if (isset($argument['required']) && $argument['required']) {
                $mode = InputArgument::REQUIRED;
            }

            $this->addArgument(
                $argument['name'],
                $mode,
                $argument['description'] ?? '',
                $argument['default'] ?? null
            );
        }

        // Configure options from config
        foreach ($this->optionsConfig as $option) {
            $mode = InputOption::VALUE_OPTIONAL;
            if (isset($option['required']) && $option['required']) {
                $mode = InputOption::VALUE_REQUIRED;
            } elseif (isset($option['flag']) && $option['flag']) {
                $mode = InputOption::VALUE_NONE;
            }

            $this->addOption(
                $option['name'],
                $option['shortcut'] ?? null,
                $mode,
                $option['description'] ?? '',
                $option['default'] ?? null
            );
        }
    }

    /**
     * Set the before invoke hook
     *
     * @param callable $callback The callback to execute before the command.
     * @return $this
     */
    public function setBeforeInvoke(callable $callback): self
    {
        $this->beforeInvoke = $callback;
        return $this;
    }

    /**
     * Set the after invoke hook
     *
     * @param callable $callback The callback to execute after the command.
     * @return $this
     */
    public function setAfterInvoke(callable $callback): self
    {
        $this->afterInvoke = $callback;
        return $this;
    }

    /**
     * Execute the command
     *
     * @param InputInterface  $input  The input interface.
     * @param OutputInterface $output The output interface.
     * @return integer The command exit code.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Run before invoke hook if set
        if ($this->beforeInvoke) {
            call_user_func($this->beforeInvoke, $input, $output);
        }

        // Get all arguments
        $args = $input->getArguments();
        unset($args['command']);

        // Get all options
        $options = $input->getOptions();

        // Execute the closure
        $result = call_user_func($this->closure, $args, $options, $input, $output);

        // Run after invoke hook if set
        if ($this->afterInvoke) {
            call_user_func($this->afterInvoke, $input, $output, $result);
        }

        // Return the result or 0 if no result
        return is_int($result) ? $result : 0;
    }
}
