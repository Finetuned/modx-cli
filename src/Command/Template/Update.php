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
    protected $processor = 'element/template/update';
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
        // Add options to the properties
        $optionKeys = array(
            'templatename', 'description', 'category', 'content', 'locked', 'static', 'static_file', 'icon'
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
