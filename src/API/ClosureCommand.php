<?php

namespace MODX\CLI\API;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
     * Create a new closure command
     *
     * @param string $name The command name
     * @param callable $closure The closure to execute
     */
    public function __construct($name, callable $closure)
    {
        parent::__construct($name);
        $this->closure = $closure;
    }

    /**
     * Set the before invoke hook
     *
     * @param callable $callback The callback to execute before the command
     * @return $this
     */
    public function setBeforeInvoke(callable $callback)
    {
        $this->beforeInvoke = $callback;
        return $this;
    }

    /**
     * Set the after invoke hook
     *
     * @param callable $callback The callback to execute after the command
     * @return $this
     */
    public function setAfterInvoke(callable $callback)
    {
        $this->afterInvoke = $callback;
        return $this;
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input The input interface
     * @param OutputInterface $output The output interface
     * @return int The command exit code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
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
