<?php

namespace MODX\CLI\SSH;

/**
 * Class ConnectionParser
 *
 * Parses SSH connection strings in the format [<user>@]<host>[:<port>][<path>]
 *
 * @package MODX\CLI\SSH
 */
class ConnectionParser
{
    /**
     * @var string The original connection string
     */
    protected $original;

    /**
     * @var string The username for SSH connection
     */
    protected $user;

    /**
     * @var string The hostname for SSH connection
     */
    protected $host;

    /**
     * @var int The port for SSH connection
     */
    protected $port = 22;

    /**
     * @var string The path on the remote server
     */
    protected $path;

    /**
     * @var string|null Custom SSH config path
     */
    protected $sshConfigPath;

    /**
     * ConnectionParser constructor.
     *
     * @param string $connectionString The SSH connection string to parse
     * @param string|null $sshConfigPath Optional SSH config path override
     */
    public function __construct($connectionString, $sshConfigPath = null)
    {
        $this->original = $connectionString;
        $this->sshConfigPath = $sshConfigPath;
        $this->parse($connectionString);
    }

    /**
     * Parse the connection string into its components
     *
     * @param string $connectionString The SSH connection string to parse
     */
    protected function parse($connectionString)
    {
        // Handle SSH config aliases
        if (!preg_match('/[@:]/', $connectionString) && $this->isSSHAlias($connectionString)) {
            $this->host = $connectionString;
            return;
        }

        // Extract user and host
        if (strpos($connectionString, '@') !== false) {
            list($this->user, $connectionString) = explode('@', $connectionString, 2);
        } else {
            // If no user is specified, use the current system user
            $this->user = function_exists('posix_getpwuid') ? posix_getpwuid(posix_geteuid())['name'] : get_current_user();
        }

        // Extract host and port
        if (strpos($connectionString, ':') !== false) {
            list($this->host, $connectionString) = explode(':', $connectionString, 2);

            // Check if the port is followed by a path
            if (preg_match('/^(\d+)(.*)$/', $connectionString, $matches)) {
                $this->port = (int) $matches[1];
                $connectionString = $matches[2];
            }
        } else {
            // Check if there's a path after the host
            if (preg_match('/^([^\\/~]+)(.*)$/', $connectionString, $matches)) {
                $this->host = $matches[1];
                $connectionString = $matches[2];
            } else {
                $this->host = $connectionString;
                $connectionString = '';
            }
        }

        // The remaining string is the path
        if (!empty($connectionString)) {
            $this->path = $connectionString;
        }
    }

    /**
     * Check if the given name is an SSH alias defined in ~/.ssh/config
     *
     * @param string $name The name to check
     * @return bool True if the name is an SSH alias, false otherwise
     */
    protected function isSSHAlias($name)
    {
        $sshConfigPath = $this->getSSHConfigPath();

        if (!file_exists($sshConfigPath)) {
            return false;
        }

        $config = file_get_contents($sshConfigPath);
        return preg_match('/^\s*Host\s+' . preg_quote($name, '/') . '\s*$/mi', $config) === 1;
    }

    /**
     * Get the SSH config path.
     *
     * @return string
     */
    protected function getSSHConfigPath()
    {
        if ($this->sshConfigPath) {
            return $this->sshConfigPath;
        }

        if (isset($_SERVER['MODX_SSH_CONFIG'])) {
            return $_SERVER['MODX_SSH_CONFIG'];
        }

        return $this->getHomeDir() . '/.ssh/config';
    }

    /**
     * Get the user's home directory
     *
     * @return string The user's home directory
     */
    protected function getHomeDir()
    {
        // Try to get the home directory from environment variables
        if (isset($_SERVER['HOME'])) {
            return $_SERVER['HOME'];
        }

        // For Windows
        if (isset($_SERVER['HOMEDRIVE']) && isset($_SERVER['HOMEPATH'])) {
            return $_SERVER['HOMEDRIVE'] . $_SERVER['HOMEPATH'];
        }

        // Fallback to the current directory
        return getcwd();
    }

    /**
     * Get the original connection string
     *
     * @return string The original connection string
     */
    public function getOriginal()
    {
        return $this->original;
    }

    /**
     * Get the username for SSH connection
     *
     * @return string The username
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Get the hostname for SSH connection
     *
     * @return string The hostname
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * Get the port for SSH connection
     *
     * @return int The port
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * Get the path on the remote server
     *
     * @return string The path
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Check if this is an SSH config alias
     *
     * @return bool True if this is an SSH config alias, false otherwise
     */
    public function isAlias()
    {
        return $this->isSSHAlias($this->host);
    }

    /**
     * Get the connection string as a string
     *
     * @return string The connection string
     */
    public function __toString()
    {
        if ($this->isAlias()) {
            return $this->host;
        }

        $result = '';

        if ($this->user) {
            $result .= $this->user . '@';
        }

        $result .= $this->host;

        if ($this->port !== 22) {
            $result .= ':' . $this->port;
        }

        if ($this->path) {
            $result .= $this->path;
        }

        return $result;
    }
}
