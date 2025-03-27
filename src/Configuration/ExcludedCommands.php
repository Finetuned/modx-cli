<?php

namespace MODX\CLI\Configuration;

/**
 * A configuration class to handle excluded commands
 */
class ExcludedCommands extends Base
{
    protected $file = 'excluded_commands.json';

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

    public function getAll()
    {
        return [];
    }
}
