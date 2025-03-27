<?php namespace MODX\CLI\Configuration;

/**
 * A configuration class to handle MODX instances
 */
class Instance extends Base
{
    protected $file = 'instances.json';
    protected $current = null;

    public function __construct()
    {
        $this->makeSureConfigPathExists();
        $this->load();
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
    public function save()
    {
        $file = $this->getConfigPath() . $this->file;
        file_put_contents($file, json_encode($this->items, JSON_PRETTY_PRINT));
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
     * @param string $instance
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getConfig($instance, $key, $default = null)
    {
        if (isset($this->items[$instance]) && isset($this->items[$instance][$key])) {
            return $this->items[$instance][$key];
        }

        return $default;
    }

    /**
     * Get a configuration value for the current instance
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getCurrentConfig($key = null, $default = null)
    {
        $current = $this->current();
        if ($current) {
            return $this->getConfig($current, $key, $default);
        }

        return $default;
    }

    public function findFormPath($path)
    {
        $normalizedPath = rtrim($path, '/') . '/';

        foreach ($this->items as $instanceName => $config) {
            if (isset($config['base_path']) && strpos($normalizedPath, rtrim($config['base_path'], '/') . '/') === 0) {
                return $instanceName;
            }
        }

        return null;
    }

    protected function formatConfigurationData()
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
}
