<?php

namespace MODX\CLI\Command\Config;

use MODX\CLI\Command\BaseCmd;

/**
 * A command to remove the default MODX instance
 */
class RmDefault extends BaseCmd
{
    protected $name = 'config:rm-default';
    protected $description = 'Remove the default MODX instance';

    protected function process()
    {
        $instances = $this->getApplication()->instances;

        // Check if there is a default instance
        $default = $instances->get('__default__');
        if (!$default) {
            $this->info('No default instance set');
            return 0;
        }

        // Get the default instance name
        $defaultName = isset($default['class']) ? $default['class'] : null;

        // Remove the default instance
        $instances->remove('__default__');
        $instances->save();

        if ($defaultName) {
            $this->info("Default instance '{$defaultName}' removed");
        } else {
            $this->info('Default instance removed');
        }

        return 0;
    }
}
