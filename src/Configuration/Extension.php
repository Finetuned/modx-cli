<?php

namespace MODX\CLI\Configuration;

/**
 * A configuration class to handle extensions
 */
class Extension extends Base
{
    protected $file = 'extensions.json';

    /**
     * Create an extension configuration manager.
     *
     * @param array   $items        Initial configuration items.
     * @param boolean $loadExisting Whether to load existing configuration.
     */
    public function __construct(array $items = [], bool $loadExisting = true)
    {
        $this->makeSureConfigPathExists();
        if ($loadExisting) {
            $this->load();
        }
        $this->items = array_merge($this->items, $items);
    }

    /**
     * Override get method to handle array values
     */
    public function get(string $key, mixed $default = null): mixed
    {
        // Check if the key exists as a normal key
        if (isset($this->items[$key])) {
            return $this->items[$key];
        }

        // Check if the value exists in the array
        if (in_array($key, $this->items)) {
            return $key;
        }

        return $default;
    }

    /**
     * Override set method to handle array values
     */
    public function set(string $key, mixed $value = null): void
    {
        // If the value is already in the array, don't add it again
        if (in_array($key, $this->items)) {
            return;
        }

        // If value is null, add the key as a value
        if ($value === null) {
            $this->items[] = $key;
            return;
        }

        // Otherwise use the parent implementation
        parent::set($key, $value);
    }

    /**
     * Override remove method to handle array values
     */
    public function remove(string $key): void
    {
        // First try to remove as a key (parent implementation)
        parent::remove($key);

        // Then check if it exists as a value in the array
        $index = array_search($key, $this->items);
        if ($index !== false) {
            unset($this->items[$index]);
            // Re-index the array to maintain sequential keys
            $this->items = array_values($this->items);
        }
    }

    /**
     * Load the configuration file
     */
    protected function load()
    {
        $file = $this->getConfigPath() . $this->file;
        if (file_exists($file)) {
            $content = file_get_contents($file);
            $this->items = json_decode($content, true) ?: [];
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
     * Format extension data as a PHP array string.
     *
     * @return string
     */
    public function formatData()
    {
        $items = $this->items;
        // Ensure we have a numerically indexed array
        $items = array_values($items);
        sort($items);
        // Format the items as a PHP array string
        $formatted = var_export($items, true);
        return "<?php\nreturn $formatted;";
    }
}
