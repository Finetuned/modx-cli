<?php

namespace MODX\CLI\Configuration;

use MODX\CLI\Application;
use MODX\Revolution\modSystemSetting;
use MODX\Revolution\modX;

/**
 * A configuration class to handle components
 */
class Component extends Base
{
    protected $file = 'components.json';
    /**
     * @var Application
     */
    protected $app;

    /**
     * @param Application $app   The application instance.
     * @param array       $items Initial component configuration items.
     */
    public function __construct(Application $app, array $items = [])
    {
        $this->app = $app;
        $this->makeSureConfigPathExists();

        if (!empty($items)) {
            $this->items = $items;
        } else {
            $this->load();
        }
    }

    /**
     * Load the configuration from MODX or file
     */
    protected function load()
    {
        $modx = $this->app->getMODX();
        if ($modx instanceof modX) {
            $json = $modx->getOption('console_commands', null, '{}');
            $this->items = $modx->fromJSON($json);
        } else {
            // If no MODX instance is available, items should be empty
            $this->items = [];
        }
    }

    /**
     * Save the configuration to MODX and file
     *
     * @return boolean
     */
    public function save(): bool
    {
        $modx = $this->app->getMODX();
        if (!$modx instanceof modX) {
            return false;
        }

        // Save to MODX system settings
        $setting = $modx->getObject(modSystemSetting::class, ['key' => 'console_commands']);
        if (!$setting) {
            $setting = $modx->newObject(modSystemSetting::class);
            $setting->set('key', 'console_commands');
            $setting->set('namespace', 'core');
            $setting->set('area', 'system');
            $setting->set('xtype', 'textarea');
        }

        $setting->set('value', $modx->toJSON($this->items));
        if (!$setting->save()) {
            return false;
        }

        // Refresh cache
        $cache = $modx->getCacheManager();
        $cache->refresh();

        // Also save to file for backup
        $file = $this->getConfigPath() . $this->file;
        file_put_contents($file, json_encode($this->items, JSON_PRETTY_PRINT));
        return true;
    }
}
