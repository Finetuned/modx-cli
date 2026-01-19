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
    protected $processor = 'Resource\Create';

    protected $name = 'resource:create';
    protected $description = 'Create a MODX resource';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'pagetitle',
                InputArgument::REQUIRED,
                'The page title of the resource'
            ],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'parent',
                null,
                InputOption::VALUE_REQUIRED,
                'The parent ID of the resource',
                0
            ],
            [
                'template',
                null,
                InputOption::VALUE_REQUIRED,
                'The template ID of the resource',
                0
            ],
            [
                'published',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the resource is published (1 or 0)',
                1
            ],
            [
                'hidemenu',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the resource is hidden from the menu (1 or 0)',
                0
            ],
            [
                'content',
                null,
                InputOption::VALUE_REQUIRED,
                'The content of the resource',
                ''
            ],
            [
                'alias',
                null,
                InputOption::VALUE_REQUIRED,
                'The alias of the resource',
                ''
            ],
            [
                'context_key',
                null,
                InputOption::VALUE_REQUIRED,
                'The context key of the resource',
                'web'
            ],
        ]);
    }

    /**
     * Prepare processor properties before execution.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return void
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
        // Add the pagetitle to the properties
        $properties['pagetitle'] = $this->argument('pagetitle');

        // Define default values for resource creation
        $defaults = [
            'parent' => 0,
            'template' => 0,
            'published' => 1,
            'hidemenu' => 0,
            'content' => '',
            'alias' => '',
            'context_key' => 'web'
        ];

        // Apply defaults first
        $this->applyDefaults($properties, $defaults);

        // Add options to the properties with type conversion
        $optionKeys = [
            'parent', 'template', 'content', 'alias', 'context_key'
        ];

        $typeMap = [
            'parent' => 'int',
            'template' => 'int',
            'published' => 'bool',
            'hidemenu' => 'bool'
        ];

        $this->addOptionsToProperties($properties, $optionKeys, $typeMap);

        // Handle boolean fields separately to ensure proper conversion
        if ($this->option('published') !== null) {
            $properties['published'] = (int) filter_var($this->option('published'), FILTER_VALIDATE_BOOLEAN);
        }

        if ($this->option('hidemenu') !== null) {
            $properties['hidemenu'] = (int) filter_var($this->option('hidemenu'), FILTER_VALIDATE_BOOLEAN);
        }
    }

    /**
     * Process processor response.
     *
     * @param array $response The decoded processor response.
     * @return integer
     */
    protected function processResponse(array $response = [])
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }

        if (isset($response['success']) && $response['success']) {
            $this->info('Resource created successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Resource ID: ' . $response['object']['id']);
            }
            return 0;
        } else {
            $this->error('Failed to create resource');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
