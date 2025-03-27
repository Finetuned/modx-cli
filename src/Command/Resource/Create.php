<?php

namespace MODX\CLI\Command\Resource;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to create a MODX resource
 */
class Create extends ProcessorCmd
{
    protected $processor = 'resource/create';

    protected $name = 'resource:create';
    protected $description = 'Create a MODX resource';

    protected function getArguments()
    {
        return array(
            array(
                'pagetitle',
                InputArgument::REQUIRED,
                'The page title of the resource'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'parent',
                null,
                InputOption::VALUE_REQUIRED,
                'The parent ID of the resource',
                0
            ),
            array(
                'template',
                null,
                InputOption::VALUE_REQUIRED,
                'The template ID of the resource',
                0
            ),
            array(
                'published',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the resource is published (1 or 0)',
                1
            ),
            array(
                'hidemenu',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the resource is hidden from the menu (1 or 0)',
                0
            ),
            array(
                'content',
                null,
                InputOption::VALUE_REQUIRED,
                'The content of the resource',
                ''
            ),
            array(
                'alias',
                null,
                InputOption::VALUE_REQUIRED,
                'The alias of the resource',
                ''
            ),
            array(
                'context_key',
                null,
                InputOption::VALUE_REQUIRED,
                'The context key of the resource',
                'web'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Add the pagetitle to the properties
        $properties['pagetitle'] = $this->argument('pagetitle');

        // Add options to the properties
        $optionKeys = array(
            'parent', 'template', 'published', 'hidemenu', 'content', 'alias', 'context_key'
        );

        foreach ($optionKeys as $key) {
            if ($this->option($key) !== null) {
                $properties[$key] = $this->option($key);
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if (isset($response['success']) && $response['success']) {
            $this->info('Resource created successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Resource ID: ' . $response['object']['id']);
            }
        } else {
            $this->error('Failed to create resource');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
        }
    }
}
