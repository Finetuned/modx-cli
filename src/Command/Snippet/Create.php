<?php

namespace MODX\CLI\Command\Snippet;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to create a MODX snippet
 */
class Create extends ProcessorCmd
{
    protected $processor = 'Element\Snippet\Create';

    protected $name = 'snippet:create';
    protected $description = 'Create a MODX snippet';

    protected function getArguments()
    {
        return array(
            array(
                'name',
                InputArgument::REQUIRED,
                'The name of the snippet'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'The description of the snippet',
                ''
            ),
            array(
                'category',
                null,
                InputOption::VALUE_REQUIRED,
                'The category ID of the snippet',
                0
            ),
            array(
                'snippet',
                null,
                InputOption::VALUE_REQUIRED,
                'The PHP code of the snippet',
                ''
            ),
            array(
                'locked',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the snippet is locked (1 or 0)',
                0
            ),
            array(
                'static',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the snippet is static (1 or 0)',
                0
            ),
            array(
                'static_file',
                null,
                InputOption::VALUE_REQUIRED,
                'The static file path for the snippet',
                ''
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Add the name to the properties
        $properties['name'] = $this->argument('name');

        // Add options to the properties
        $optionKeys = array(
            'description', 'category', 'snippet', 'locked', 'properties', 'static', 'static_file'
        );

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
            $this->info('Snippet created successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Snippet ID: ' . $response['object']['id']);
            }
        } else {
            $this->error('Failed to create snippet');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
        }
    }
}
