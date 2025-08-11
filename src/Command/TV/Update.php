<?php

namespace MODX\CLI\Command\TV;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to update a MODX template variable
 */
class Update extends ProcessorCmd
{
    protected $processor = 'element/tv/update';
    protected $required = array('id');

    protected $name = 'tv:update';
    protected $description = 'Update a MODX template variable';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the template variable to update'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'name',
                null,
                InputOption::VALUE_REQUIRED,
                'The name of the template variable'
            ),
            array(
                'caption',
                null,
                InputOption::VALUE_REQUIRED,
                'The caption of the template variable'
            ),
            array(
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'The description of the template variable'
            ),
            array(
                'category',
                null,
                InputOption::VALUE_REQUIRED,
                'The category ID of the template variable'
            ),
            array(
                'type',
                null,
                InputOption::VALUE_REQUIRED,
                'The input type of the template variable (text, textarea, richtext, etc.)'
            ),
            array(
                'default_text',
                null,
                InputOption::VALUE_REQUIRED,
                'The default value of the template variable'
            ),
            array(
                'elements',
                null,
                InputOption::VALUE_REQUIRED,
                'The possible values for the template variable (for select, radio, etc.)'
            ),
            array(
                'rank',
                null,
                InputOption::VALUE_REQUIRED,
                'The rank of the template variable'
            ),
            array(
                'display',
                null,
                InputOption::VALUE_REQUIRED,
                'The display type of the template variable'
            ),
            array(
                'templates',
                null,
                InputOption::VALUE_REQUIRED,
                'Comma-separated list of template IDs to associate with the template variable'
            ),
            array(
                'locked',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the template variable is locked (1 or 0)'
            ),
            array(
                'static',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the template variable is static (1 or 0)'
            ),
            array(
                'static_file',
                null,
                InputOption::VALUE_REQUIRED,
                'The static file path for the template variable'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Get the TV ID from arguments
        $tvId = $this->argument('id');
        
        // Pre-populate properties with existing TV data to avoid requiring name parameter
        if (!$this->prePopulateFromExisting($properties, 'modTemplateVar', $tvId)) {
            $this->error("Template Variable with ID {$tvId} not found");
            return false;
        }

        // Add options to the properties with type conversion
        $optionKeys = array(
            'name', 'caption', 'description', 'category', 'type', 'default_text', 'elements',
            'rank', 'display', 'templates', 'static_file'
        );
        
        $typeMap = array(
            'category' => 'int',
            'rank' => 'int',
            'locked' => 'bool',
            'static' => 'bool'
        );

        $this->addOptionsToProperties($properties, $optionKeys, $typeMap);
        
        // Handle boolean fields separately since they need special handling
        if ($this->option('locked') !== null) {
            $properties['locked'] = (int) filter_var($this->option('locked'), FILTER_VALIDATE_BOOLEAN);
        }
        
        if ($this->option('static') !== null) {
            $properties['static'] = (int) filter_var($this->option('static'), FILTER_VALIDATE_BOOLEAN);
        }
        
        // Handle templates field - convert comma-separated string to array if needed
        if ($this->option('templates') !== null) {
            $templates = $this->option('templates');
            if (is_string($templates)) {
                $properties['templates'] = array_map('trim', explode(',', $templates));
            } else {
                $properties['templates'] = $templates;
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if (isset($response['success']) && $response['success']) {
            $this->info('Template variable updated successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Template variable ID: ' . $response['object']['id']);
            }
        } else {
            $this->error('Failed to update template variable');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
        }
    }
}
