<?php

namespace MODX\CLI\Command\Resource;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to update a MODX resource
 */
class Update extends ProcessorCmd
{
    protected $processor = 'Resource\Update';
    protected $required = ['id'];

    protected $name = 'resource:update';
    protected $description = 'Update a MODX resource';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'id',
                InputArgument::REQUIRED,
                'The ID of the resource to update'
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
                'pagetitle',
                null,
                InputOption::VALUE_REQUIRED,
                'The page title of the resource'
            ],
            [
                'parent',
                null,
                InputOption::VALUE_REQUIRED,
                'The parent ID of the resource'
            ],
            [
                'template',
                null,
                InputOption::VALUE_REQUIRED,
                'The template ID of the resource'
            ],
            [
                'published',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the resource is published (1 or 0)'
            ],
            [
                'hidemenu',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the resource is hidden from the menu (1 or 0)'
            ],
            [
                'content',
                null,
                InputOption::VALUE_REQUIRED,
                'The content of the resource'
            ],
            [
                'alias',
                null,
                InputOption::VALUE_REQUIRED,
                'The alias of the resource'
            ],
            [
                'context_key',
                null,
                InputOption::VALUE_REQUIRED,
                'The context key of the resource'
            ],
        ]);
    }

    /**
     * Prepare processor properties before execution.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return boolean|null False to abort execution, otherwise null.
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
        // Get the resource ID from arguments
        $resourceId = (int) $this->argument('id');

        // Pre-populate properties with existing resource data to avoid requiring name parameter
        if (!$this->prePopulateFromExisting($properties, \MODX\Revolution\modResource::class, $resourceId)) {
            $this->error("Resource with ID {$resourceId} not found");
            return false;
        }

        // Ensure critical fields have proper defaults if not already set
        if (!isset($properties['class_key']) || empty($properties['class_key'])) {
            $properties['class_key'] = 'modDocument';
        }

        if (!isset($properties['context_key']) || empty($properties['context_key'])) {
            $properties['context_key'] = 'web';
        }

        if (!isset($properties['content_type']) || empty($properties['content_type'])) {
            $properties['content_type'] = 1;
        }

        // Add options to the properties with type conversion
        $optionKeys = [
            'pagetitle', 'parent', 'template', 'content', 'alias', 'context_key'
        ];

        $typeMap = [
            'parent' => 'int',
            'template' => 'int',
            'published' => 'bool',
            'hidemenu' => 'bool'
        ];

        $this->addOptionsToProperties($properties, $optionKeys, $typeMap);

        // Handle boolean fields separately since they need special handling
        if ($this->option('published') !== null) {
            $properties['published'] = (int) filter_var($this->option('published'), FILTER_VALIDATE_BOOLEAN);
        }

        if ($this->option('hidemenu') !== null) {
            $properties['hidemenu'] = (int) filter_var($this->option('hidemenu'), FILTER_VALIDATE_BOOLEAN);
        }
        return null;
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
            $this->info('Resource updated successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Resource ID: ' . $response['object']['id']);
            }
            return 0;
        } else {
            $this->error('Failed to update resource');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
