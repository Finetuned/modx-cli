<?php

namespace MODX\CLI\Command\Config;

use MODX\CLI\Command\BaseCmd;
use MODX\CLI\Configuration\Instance;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to list MODX instances in the configuration
 */
class GetList extends BaseCmd
{
    protected $name = 'config:list';
    protected $description = 'List MODX instances in the configuration';

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'json',
                null,
                InputOption::VALUE_NONE,
                'Output results as JSON'
            ),
        ));
    }

    protected function process()
    {
        $app = $this->getApplication();
        $instances = $app ? $app->instances : new Instance([], false);
        $all = $instances->getAll();

        // Remove the default instance from the list
        $default = null;
        if (isset($all['__default__'])) {
            $default = $all['__default__'];
            unset($all['__default__']);
        }

        if (empty($all)) {
            if ($this->option('json')) {
                $this->output->writeln(json_encode([]));
            } else {
                $this->info('No instances configured');
            }
            return 0;
        }

        // Build data array
        $data = [];
        foreach ($all as $name => $config) {
            $isDefault = ($default && isset($default['class']) && $default['class'] === $name);
            $data[] = array(
                'name' => $name,
                'base_path' => isset($config['base_path']) ? $config['base_path'] : '',
                'is_default' => $isDefault
            );
        }

        // Output JSON or table
        if ($this->option('json')) {
            $this->output->writeln(json_encode($data, JSON_PRETTY_PRINT));
        } else {
            $table = new Table($this->output);
            $table->setHeaders(array('Name', 'Base Path', 'Default'));
            foreach ($data as $row) {
                $table->addRow(array(
                    $row['name'],
                    $row['base_path'],
                    $row['is_default'] ? 'Yes' : 'No'
                ));
            }
            $table->render();
        }

        return 0;
    }
}
