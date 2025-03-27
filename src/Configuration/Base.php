<?php namespace MODX\CLI\Configuration;

/**
 * A base configuration class to extend
 */
abstract class Base implements ConfigurationInterface
{
    protected $items = array();

    public function get($key, $default = null)
    {
        if (isset($this->items[$key])) {
            return $this->items[$key];
        }

        return $default;
    }

    public function set($key, $value = null)
    {
        $this->items[$key] = $value;
    }

    public function remove($key)
    {
        if (isset($this->items[$key])) {
            unset($this->items[$key]);
        }
    }

    public function getAll()
    {
        return $this->items;
    }

    public function getConfigPath()
    {
        return getenv('HOME') . '/.modx/';
    }

    public function makeSureConfigPathExists()
    {
        $path = $this->getConfigPath();
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
    }
}
