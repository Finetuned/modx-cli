<?php

namespace MODX\CLI\Configuration;

/**
 * A configuration interface to implement
 */
interface ConfigurationInterface
{
    /**
     * Get a configuration value.
     *
     * @param string $key     The configuration key.
     * @param mixed  $default The default value.
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Set a configuration value.
     *
     * @param string $key   The configuration key.
     * @param mixed  $value The value to store.
     * @return void
     */
    public function set(string $key, mixed $value = null): void;

    /**
     * Remove a configuration value.
     *
     * @param string $key The configuration key.
     * @return void
     */
    public function remove(string $key): void;

    /**
     * Get all configuration values.
     *
     * @return array
     */
    public function getAll(): array;

    /**
     * Persist the configuration changes.
     *
     * @return boolean
     */
    public function save(): bool;
}
