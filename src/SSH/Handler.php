<?php

namespace MODX\CLI\SSH;

/**
 * Class Handler
 *
 * Handles SSH connections and command execution
 *
 * @package MODX\CLI\SSH
 */
class Handler
{
    /**
     * @var string The SSH connection string
     */
    protected $connectionString;

    /**
     * @var CommandExecutorInterface|null
     */
    protected $executor;

    /**
     * Handler constructor.
     *
     * @param string                        $connectionString The SSH connection string.
     * @param CommandExecutorInterface|null $executor         The command executor.
     */
    public function __construct(string $connectionString, ?CommandExecutorInterface $executor = null)
    {
        $this->connectionString = $connectionString;
        $this->executor = $executor;
    }

    /**
     * Execute a command on the remote server
     *
     * @param string $command The command to execute.
     * @param array  $args    The command arguments.
     * @return integer The command exit code.
     */
    public function execute(string $command, array $args = []): int
    {
        $parser = new ConnectionParser($this->connectionString);
        $proxy = new CommandProxy($parser, $command, $args, $this->executor);
        return $proxy->execute();
    }
}
