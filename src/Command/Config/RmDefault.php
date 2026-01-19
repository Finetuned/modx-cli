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

    /**
     * Execute the command.
     *
     * @return integer
     */
    /**
     * Execute the command.
     *
     * @return integer
     */
    protected function process()
    {
        $instances = $this->getApplication()->instances;

        // Check if there is a default instance
        $default = $instances->get('__default__');
        if (!$default) {
            $message = 'No default instance set';
            if ($this->option('json')) {
                $this->output->writeln(json_encode([
                    'success' => true,
                    'message' => $message,
                    'removed' => false,
                ], JSON_PRETTY_PRINT));
            } else {
                $this->info($message);
            }
            return 0;
        }

        // Get the default instance name
        $defaultName = isset($default['class']) ? $default['class'] : null;

        // Remove the default instance
        $instances->remove('__default__');
        $instances->save();

        if ($defaultName) {
            $message = "Default instance '{$defaultName}' removed";
        } else {
            $message = 'Default instance removed';
        }

        if ($this->option('json')) {
            $this->output->writeln(json_encode([
                'success' => true,
                'message' => $message,
                'removed' => true,
                'default' => [
                    'name' => $defaultName,
                ],
            ], JSON_PRETTY_PRINT));
        } else {
            $this->info($message);
        }

        return 0;
    }
}
