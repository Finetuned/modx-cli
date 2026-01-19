<?php

namespace MODX\CLI\SSH;

/**
 * Class CommandProxy
 *
 * Proxies commands to a remote server via SSH
 *
 * @package MODX\CLI\SSH
 */
class CommandProxy
{
    /**
     * @var ConnectionParser The SSH connection
     */
    protected $connection;

    /**
     * @var string The command to execute
     */
    protected $command;

    /**
     * @var array The command arguments
     */
    protected $args;

    /**
     * @var CommandExecutorInterface
     */
    protected $executor;

    /**
     * CommandProxy constructor.
     *
     * @param ConnectionParser              $connection The SSH connection.
     * @param string                        $command    The command to execute.
     * @param array                         $args       The command arguments.
     * @param CommandExecutorInterface|null $executor   The command executor.
     */
    public function __construct(
        ConnectionParser $connection,
        string $command,
        array $args = [],
        ?CommandExecutorInterface $executor = null
    ) {
        $this->connection = $connection;
        $this->command = $command;
        $this->args = $args;
        $this->executor = $executor ?: new SymfonyProcessExecutor();
    }

    /**
     * Execute the command on the remote server
     *
     * @return integer The command exit code
     */
    public function execute(): int
    {
        $sshCommand = $this->buildSSHCommand();

        return $this->executor->run($sshCommand, 3600, true, function ($type, $buffer) {
            if (\Symfony\Component\Process\Process::ERR === $type) {
                fwrite(STDERR, $buffer);
            } else {
                fwrite(STDOUT, $buffer);
            }
        });
    }

    /**
     * Build the SSH command to execute
     *
     * @return string The SSH command
     */
    protected function buildSSHCommand(): string
    {
        $user = $this->connection->getUser();
        $host = $this->connection->getHost();
        $port = $this->connection->getPort();
        $path = $this->connection->getPath();

        $sshOptions = [];
        if ($port !== 22) {
            $sshOptions[] = "-p {$port}";
        }

        $sshTarget = $user ? "{$user}@{$host}" : $host;
        $cdCommand = $path ? "cd {$path} && " : "";
        $remoteCommand = $this->buildRemoteCommand();

        return sprintf(
            'ssh %s %s "%s%s"',
            implode(' ', $sshOptions),
            escapeshellarg($sshTarget),
            $cdCommand,
            $remoteCommand
        );
    }

    /**
     * Build the command to execute on the remote server
     *
     * @return string The remote command
     */
    protected function buildRemoteCommand(): string
    {
        $command = $this->command;
        $args = array_map('escapeshellarg', $this->args);

        return "modx {$command} " . implode(' ', $args);
    }
}
