<?php

namespace MODX\CLI\Configuration;

/**
 * A base configuration class to extend
 */
abstract class Base implements ConfigurationInterface
{
    protected $items = [];

    /**
     * Get a configuration value.
     *
     * @param string $key     The configuration key.
     * @param mixed  $default The default value.
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if (isset($this->items[$key])) {
            return $this->items[$key];
        }

        return $default;
    }

    /**
     * Set a configuration value.
     *
     * @param string $key   The configuration key.
     * @param mixed  $value The value to store.
     * @return void
     */
    public function set(string $key, mixed $value = null): void
    {
        $this->items[$key] = $value;
    }

    /**
     * Remove a configuration value.
     *
     * @param string $key The configuration key.
     * @return void
     */
    public function remove(string $key): void
    {
        if (isset($this->items[$key])) {
            unset($this->items[$key]);
        }
    }

    /**
     * Get all configuration values.
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->items;
    }

    /**
     * Get the configuration directory path.
     *
     * @return string
     */
    public function getConfigPath(): string
    {
        return getenv('HOME') . '/.modx/';
    }

    /**
     * Ensure the configuration path exists on disk.
     *
     * @return void
     */
    public function makeSureConfigPathExists(): void
    {
        $path = $this->getConfigPath();
        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }
    }
}
