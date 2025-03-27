<?php

namespace MODX\CLI\Configuration\Yaml;

use Symfony\Component\Yaml\Yaml;

/**
 * Class YamlConfig
 *
 * Handles YAML configuration files for the MODX CLI
 *
 * @package MODX\CLI\Configuration\Yaml
 */
class YamlConfig
{
    /**
     * @var array The configuration data
     */
    protected $config = [];

    /**
     * YamlConfig constructor.
     */
    public function __construct()
    {
        $this->loadGlobalConfig();
        $this->loadProjectConfig();
    }

    /**
     * Load the global configuration file
     */
    protected function loadGlobalConfig()
    {
        $globalConfigPath = $this->getHomeDir() . '/.modx/config.yml';
        if (file_exists($globalConfigPath)) {
            $this->mergeConfig($this->parseYaml($globalConfigPath));
        }
    }

    /**
     * Load the project configuration file
     */
    protected function loadProjectConfig()
    {
        $projectConfigPath = getcwd() . '/modx-cli.yml';
        if (file_exists($projectConfigPath)) {
            $this->mergeConfig($this->parseYaml($projectConfigPath));
        }
    }

    /**
     * Parse a YAML file
     *
     * @param string $path The path to the YAML file
     * @return array The parsed YAML data
     */
    protected function parseYaml($path)
    {
        return Yaml::parseFile($path);
    }

    /**
     * Merge new configuration data with existing data
     *
     * @param array $newConfig The new configuration data
     */
    protected function mergeConfig(array $newConfig)
    {
        $this->config = array_merge($this->config, $newConfig);
    }

    /**
     * Get all aliases defined in the configuration
     *
     * @return array The aliases
     */
    public function getAliases()
    {
        $aliases = [];

        foreach ($this->config as $key => $value) {
            if (strpos($key, '@') === 0) {
                $aliases[substr($key, 1)] = $value;
            }
        }

        return $aliases;
    }

    /**
     * Get a specific alias definition
     *
     * @param string $name The alias name
     * @return array|null The alias definition, or null if not found
     */
    public function getAlias($name)
    {
        $key = '@' . $name;

        return isset($this->config[$key]) ? $this->config[$key] : null;
    }

    /**
     * Get all configuration data
     *
     * @return array The configuration data
     */
    public function getAll()
    {
        return $this->config;
    }

    /**
     * Get a specific configuration value
     *
     * @param string $key The configuration key
     * @param mixed $default The default value to return if the key is not found
     * @return mixed The configuration value
     */
    public function get($key, $default = null)
    {
        return isset($this->config[$key]) ? $this->config[$key] : $default;
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
}
