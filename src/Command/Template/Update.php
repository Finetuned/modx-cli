<?php

namespace MODX\CLI\Command\Template;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to update a MODX template
 */
class Update extends ProcessorCmd
{
    protected $processor = 'Element\Template\Update';
    protected $required = array('id');

    protected $name = 'template:update';
    protected $description = 'Update a MODX template';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the template to update'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'templatename',
                null,
                InputOption::VALUE_REQUIRED,
                'The name of the template'
            ),
            array(
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'The description of the template'
            ),
            array(
                'category',
                null,
                InputOption::VALUE_REQUIRED,
                'The category ID of the template'
            ),
            array(
                'content',
                null,
                InputOption::VALUE_REQUIRED,
                'The HTML content of the template'
            ),
            array(
                'locked',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the template is locked (1 or 0)'
            ),
            array(
                'static',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the template is static (1 or 0)'
            ),
            array(
                'static_file',
                null,
                InputOption::VALUE_REQUIRED,
                'The static file path for the template'
            ),
            array(
                'icon',
                null,
                InputOption::VALUE_REQUIRED,
                'The icon for the template'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Get the template ID from arguments
        $templateId = $this->argument('id');
        
        // Pre-populate properties with existing template data to avoid requiring name parameter
        if (!$this->prePopulateFromExisting($properties, 'modTemplate', $templateId)) {
            $this->error("Template with ID {$templateId} not found");
            return false;
        }

        // Add options to the properties with type conversion
        $optionKeys = array(
            'templatename', 'description', 'category', 'content', 'static_file', 'icon'
        );
        
        $typeMap = array(
            'category' => 'int',
            'locked' => 'bool',
            'static' => 'bool'
        );

        $this->addOptionsToProperties($properties, $optionKeys, $typeMap);
        
        // Handle locked and static separately since they need special handling
        if ($this->option('locked') !== null) {
            $properties['locked'] = (int) filter_var($this->option('locked'), FILTER_VALIDATE_BOOLEAN);
        }
        
        if ($this->option('static') !== null) {
            $properties['static'] = (int) filter_var($this->option('static'), FILTER_VALIDATE_BOOLEAN);
        }
    }

    protected function processResponse(array $response = array())
    {
        if (isset($response['success']) && $response['success']) {
            $this->info('Template updated successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Template ID: ' . $response['object']['id']);
            }
        } else {
            $this->error('Failed to update template');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
        }
    }
}
