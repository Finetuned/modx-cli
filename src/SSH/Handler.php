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
     * Handler constructor.
     *
     * @param string $connectionString The SSH connection string
     */
    public function __construct($connectionString)
    {
        $this->connectionString = $connectionString;
    }

    /**
     * Execute a command on the remote server
     *
     * @param string $command The command to execute
     * @param array $args The command arguments
     * @return int The command exit code
     */
    public function execute($command, array $args = [])
    {
        $parser = new ConnectionParser($this->connectionString);
        $proxy = new CommandProxy($parser, $command, $args);
        return $proxy->execute();
    }
}
