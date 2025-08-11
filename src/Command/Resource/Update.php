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
    protected $processor = 'resource/update';
    protected $required = array('id');

    protected $name = 'resource:update';
    protected $description = 'Update a MODX resource';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the resource to update'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'pagetitle',
                null,
                InputOption::VALUE_REQUIRED,
                'The page title of the resource'
            ),
            array(
                'parent',
                null,
                InputOption::VALUE_REQUIRED,
                'The parent ID of the resource'
            ),
            array(
                'template',
                null,
                InputOption::VALUE_REQUIRED,
                'The template ID of the resource'
            ),
            array(
                'published',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the resource is published (1 or 0)'
            ),
            array(
                'hidemenu',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the resource is hidden from the menu (1 or 0)'
            ),
            array(
                'content',
                null,
                InputOption::VALUE_REQUIRED,
                'The content of the resource'
            ),
            array(
                'alias',
                null,
                InputOption::VALUE_REQUIRED,
                'The alias of the resource'
            ),
            array(
                'context_key',
                null,
                InputOption::VALUE_REQUIRED,
                'The context key of the resource'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Get the resource ID from arguments
        $resourceId = $this->argument('id');
        
        // Pre-populate properties with existing resource data to avoid requiring name parameter
        if (!$this->prePopulateFromExisting($properties, 'modResource', $resourceId)) {
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
        $optionKeys = array(
            'pagetitle', 'parent', 'template', 'content', 'alias', 'context_key'
        );
        
        $typeMap = array(
            'parent' => 'int',
            'template' => 'int',
            'published' => 'bool',
            'hidemenu' => 'bool'
        );

        $this->addOptionsToProperties($properties, $optionKeys, $typeMap);
        
        // Handle boolean fields separately since they need special handling
        if ($this->option('published') !== null) {
            $properties['published'] = (int) filter_var($this->option('published'), FILTER_VALIDATE_BOOLEAN);
        }
        
        if ($this->option('hidemenu') !== null) {
            $properties['hidemenu'] = (int) filter_var($this->option('hidemenu'), FILTER_VALIDATE_BOOLEAN);
        }
    }

    protected function processResponse(array $response = array())
    {
        if (isset($response['success']) && $response['success']) {
            $this->info('Resource updated successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Resource ID: ' . $response['object']['id']);
            }
        } else {
            $this->error('Failed to update resource');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
        }
    }
}
