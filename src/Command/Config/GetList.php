<?php

namespace MODX\CLI\Command\Config;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Helper\Table;

/**
 * A command to list MODX instances in the configuration
 */
class GetList extends BaseCmd
{
    protected $name = 'config:list';
    protected $description = 'List MODX instances in the configuration';

    protected function process()
    {
        $instances = $this->getApplication()->instances;
        $all = $instances->getAll();

        // Remove the default instance from the list
        $default = null;
        if (isset($all['__default__'])) {
            $default = $all['__default__'];
            unset($all['__default__']);
        }

        if (empty($all)) {
            $this->info('No instances configured');
            return 0;
        }

        $table = new Table($this->output);
        $table->setHeaders(array('Name', 'Base Path', 'Default'));

        foreach ($all as $name => $config) {
            $isDefault = ($default && isset($default['class']) && $default['class'] === $name);
            $table->addRow(array(
                $name,
                isset($config['base_path']) ? $config['base_path'] : '',
                $isDefault ? 'Yes' : 'No',
            ));
        }

        $table->render();

        return 0;
    }
}
