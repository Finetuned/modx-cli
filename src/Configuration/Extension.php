<?php namespace MODX\CLI\Configuration;

/**
 * A configuration class to handle extensions
 */
class Extension extends Base
{
    protected $file = 'extensions.json';

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

    public function formatData()
    {
        // Format the items as a PHP array string
        $formatted = var_export($this->items, true);
        return "<?php\nreturn $formatted;";
    }
}
