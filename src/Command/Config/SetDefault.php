<?php

namespace MODX\CLI\Command\Config;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to set a MODX instance as the default
 */
class SetDefault extends BaseCmd
{
    protected $name = 'config:set-default';
    protected $description = 'Set a MODX instance as the default';

    protected function getArguments()
    {
        return array(
            array(
                'name',
                InputArgument::REQUIRED,
                'The name of the instance to set as default'
            ),
        );
    }

    protected function process()
    {
        $name = $this->argument('name');
        $instances = $this->getApplication()->instances;

        // Check if the instance exists
        if (!$instances->get($name)) {
            $this->error("Instance '{$name}' does not exist");
            return 1;
        }

        // Set the instance as default
        $instances->set('__default__', array(
            'class' => $name,
        ));
        $instances->save();

        $this->info("Instance '{$name}' set as default");

        return 0;
    }
}
