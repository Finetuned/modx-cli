<?php

namespace MODX\CLI\Configuration;

/**
 * A configuration class to handle MODX instances
 */
class Instance extends Base
{
    protected $file = 'instances.json';
    protected $current = null;

    /**
     * Create an instance configuration manager.
     *
     * @param array   $items        Initial configuration items.
     * @param boolean $loadExisting Whether to load existing configuration.
     */
    public function __construct(array $items = [], bool $loadExisting = true)
    {
        $this->makeSureConfigPathExists();
        if ($loadExisting) {
            $this->load();
        } else {
            $this->items = [];
        }
        $this->items = array_merge($this->items, $items);
    }

    /**
     * Load the configuration file
     */
    protected function load()
    {
        $file = $this->getConfigPath() . $this->file;
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $this->items = json_decode($content, true);
        }
    }

    /**
     * Save the configuration file
     */
    public function save(): bool
    {
        $file = $this->getConfigPath() . $this->file;
        file_put_contents($file, json_encode($this->items, JSON_PRETTY_PRINT));
        return true;
    }

    /**
     * Get the current instance name
     *
     * @return string|null
     */
    public function current()
    {
        if (null === $this->current) {
            $this->current = $this->findCurrent();
        }

        return $this->current;
    }

    /**
     * Try to find the current instance name
     *
     * @return string|null
     */
    protected function findCurrent()
    {
        $cwd = getcwd();
        foreach ($this->items as $name => $config) {
            if (isset($config['base_path']) && $config['base_path'] && strpos($cwd, $config['base_path']) === 0) {
                return $name;
            }
        }

        return null;
    }

    /**
     * Get a configuration value for the given instance
     *
     * @param string $instance The instance name.
     * @param string $key      The configuration key.
     * @param mixed  $default  The default value.
     *
     * @return mixed
     */
    public function getConfig(string $instance, string $key, mixed $default = null): mixed
    {
        if (isset($this->items[$instance]) && isset($this->items[$instance][$key])) {
            return $this->items[$instance][$key];
        }

        return $default;
    }

    /**
     * Get a configuration value for the current instance
     *
     * @param string|null $key     The configuration key.
     * @param mixed       $default The default value.
     *
     * @return mixed
     */
    public function getCurrentConfig(?string $key = null, mixed $default = null): mixed
    {
        $current = $this->current();
        if ($current) {
            return $this->getConfig($current, $key, $default);
        }

        return $default;
    }

    /**
     * Find the instance name for a given filesystem path.
     *
     * @param string $path The filesystem path to match.
     * @return string|null
     */
    public function findFormPath(string $path): ?string
    {
        $normalizedPath = rtrim($path, '/') . '/';

        foreach ($this->items as $instanceName => $config) {
            if (isset($config['base_path']) && strpos($normalizedPath, rtrim($config['base_path'], '/') . '/') === 0) {
                return $instanceName;
            }
        }

        return null;
    }

    /**
     * Format configuration data as INI string
     *
     * @return string
     */
    public function formatConfigurationData()
    {
        $iniString = '';

        foreach ($this->items as $instanceName => $config) {
            $iniString .= "[$instanceName]\n";
            foreach ($config as $key => $value) {
                $iniString .= "$key = \"$value\"\n";
            }
        }

        return $iniString;
    }

    /**
     * Get the configured default instance name, if any
     *
     * @return string|null
     */
    public function getDefaultInstance(): ?string
    {
        return $this->getConfig('__default__', 'class');
    }
}
