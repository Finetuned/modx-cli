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
    protected $required = ['id'];

    protected $name = 'template:update';
    protected $description = 'Update a MODX template';

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
                'The ID of the template to update'
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
                'templatename',
                null,
                InputOption::VALUE_REQUIRED,
                'The name of the template'
            ],
            [
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'The description of the template'
            ],
            [
                'category',
                null,
                InputOption::VALUE_REQUIRED,
                'The category ID of the template'
            ],
            [
                'content',
                null,
                InputOption::VALUE_REQUIRED,
                'The HTML content of the template'
            ],
            [
                'locked',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the template is locked (1 or 0)'
            ],
            [
                'static',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the template is static (1 or 0)'
            ],
            [
                'static_file',
                null,
                InputOption::VALUE_REQUIRED,
                'The static file path for the template'
            ],
            [
                'icon',
                null,
                InputOption::VALUE_REQUIRED,
                'The icon for the template'
            ],
        ]);
    }

    /**
     * Prepare properties before running the processor.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return boolean|null Return false to abort.
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
        // Get the template ID from arguments
        $templateId = (int) $this->argument('id');

        // Pre-populate properties with existing template data to avoid requiring name parameter
        if (!$this->prePopulateFromExisting($properties, \MODX\Revolution\modTemplate::class, $templateId)) {
            $this->error("Template with ID {$templateId} not found");
            return false;
        }

        // Add options to the properties with type conversion
        $optionKeys = [
            'templatename', 'description', 'category', 'content', 'static_file', 'icon'
        ];

        $typeMap = [
            'category' => 'int',
            'locked' => 'bool',
            'static' => 'bool'
        ];

        $this->addOptionsToProperties($properties, $optionKeys, $typeMap);

        // Handle locked and static separately since they need special handling
        if ($this->option('locked') !== null) {
            $properties['locked'] = (int) filter_var($this->option('locked'), FILTER_VALIDATE_BOOLEAN);
        }

        if ($this->option('static') !== null) {
            $properties['static'] = (int) filter_var($this->option('static'), FILTER_VALIDATE_BOOLEAN);
        }
        return null;
    }

    /**
     * Handle the processor response.
     *
     * @param array $response The processor response.
     * @return integer
     */
    protected function processResponse(array $response = [])
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }

        if (isset($response['success']) && $response['success']) {
            $this->info('Template updated successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Template ID: ' . $response['object']['id']);
            }
            return 0;
        } else {
            $this->error('Failed to update template');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
