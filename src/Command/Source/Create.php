<?php

namespace MODX\CLI\Command\Source;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to create a MODX media source
 */
class Create extends ProcessorCmd
{
    protected $processor = 'Source\Create';

    protected $name = 'source:create';
    protected $description = 'Create a MODX media source';

    protected function getArguments()
    {
        return array(
            array(
                'name',
                InputArgument::REQUIRED,
                'The name of the media source'
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
                'The description of the media source',
                ''
            ),
            array(
                'class_key',
                null,
                InputOption::VALUE_REQUIRED,
                'The class key of the media source',
                'MODX\\Revolution\\Sources\\modFileMediaSource'
            ),
            array(
                'source-properties',
                null,
                InputOption::VALUE_REQUIRED,
                'The properties of the media source (JSON format)',
                ''
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Add the name to the properties
        $properties['name'] = $this->argument('name');

        // Add options to the properties
        $optionKeys = array('description', 'class_key');

        foreach ($optionKeys as $key) {
            if ($this->option($key) !== null) {
                $properties[$key] = $this->option($key);
            }
        }

        // Handle source-properties separately (maps to 'properties' in MODX)
        if ($this->option('source-properties') !== null) {
            $properties['properties'] = $this->option('source-properties');
        }
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }
        
        if (isset($response['success']) && $response['success']) {
            $this->info('Media source created successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Source ID: ' . $response['object']['id']);
            }
            return 0;
        } else {
            $this->error('Failed to create media source');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}