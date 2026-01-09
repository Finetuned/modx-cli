<?php

namespace MODX\CLI\Command\Context\Setting;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to create a context setting
 */
class Create extends ProcessorCmd
{
    protected $processor = 'Context\Setting\Create';

    protected $name = 'context:setting:create';
    protected $description = 'Create a context setting';

    protected function getArguments()
    {
        return array(
            array(
                'context',
                InputArgument::REQUIRED,
                'The context key'
            ),
            array(
                'key',
                InputArgument::REQUIRED,
                'The setting key'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'value',
                null,
                InputOption::VALUE_REQUIRED,
                'The setting value'
            ),
            array(
                'area',
                null,
                InputOption::VALUE_REQUIRED,
                'The setting area/category'
            ),
            array(
                'namespace',
                null,
                InputOption::VALUE_REQUIRED,
                'The setting namespace',
                'core'
            ),
            array(
                'xtype',
                null,
                InputOption::VALUE_REQUIRED,
                'The setting xtype',
                'textfield'
            ),
            array(
                'name',
                null,
                InputOption::VALUE_REQUIRED,
                'The setting name'
            ),
            array(
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'The setting description'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $properties['fk'] = $this->argument('context');
        $properties['context_key'] = $this->argument('context');
        $properties['key'] = $this->argument('key');

        $optionKeys = array('value', 'area', 'namespace', 'xtype', 'name', 'description');
        foreach ($optionKeys as $key) {
            if ($this->option($key) !== null) {
                $properties[$key] = $this->option($key);
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }

        if (isset($response['success']) && $response['success']) {
            $this->info('Context setting created successfully');
            return 0;
        }

        $this->error('Failed to create context setting');
        if (isset($response['message'])) {
            $this->error($response['message']);
        }
        return 1;
    }
}
