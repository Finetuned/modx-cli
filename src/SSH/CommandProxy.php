<?php namespace MODX\CLI\SSH;

use Symfony\Component\Process\Process;

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
     * CommandProxy constructor.
     * 
     * @param ConnectionParser $connection The SSH connection
     * @param string $command The command to execute
     * @param array $args The command arguments
     */
    public function __construct(ConnectionParser $connection, $command, array $args = [])
    {
        $this->connection = $connection;
        $this->command = $command;
        $this->args = $args;
    }

    /**
     * Execute the command on the remote server
     * 
     * @return int The command exit code
     */
    public function execute()
    {
        $sshCommand = $this->buildSSHCommand();
        
        $process = Process::fromShellCommandline($sshCommand);
        $process->setTimeout(3600); // 1 hour timeout
        $process->setTty(true); // Use TTY for interactive commands
        
        return $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
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
    protected function buildSSHCommand()
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
    protected function buildRemoteCommand()
    {
        $command = $this->command;
        $args = array_map('escapeshellarg', $this->args);
        
        return "modx {$command} " . implode(' ', $args);
    }
}
