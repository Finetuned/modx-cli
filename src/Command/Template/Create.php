<?php

namespace MODX\CLI\Command\Template;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to create a MODX template
 */
class Create extends ProcessorCmd
{
    protected $processor = 'Element\Template\Create';

    protected $name = 'template:create';
    protected $description = 'Create a MODX template';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'templatename',
                InputArgument::REQUIRED,
                'The name of the template'
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
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'The description of the template',
                ''
            ],
            [
                'category',
                null,
                InputOption::VALUE_REQUIRED,
                'The category ID of the template',
                0
            ],
            [
                'content',
                null,
                InputOption::VALUE_REQUIRED,
                'The HTML content of the template',
                ''
            ],
            [
                'locked',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the template is locked (1 or 0)',
                0
            ],
            [
                'static',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the template is static (1 or 0)',
                0
            ],
            [
                'static_file',
                null,
                InputOption::VALUE_REQUIRED,
                'The static file path for the template',
                ''
            ],
            [
                'icon',
                null,
                InputOption::VALUE_REQUIRED,
                'The icon for the template',
                ''
            ],
        ]);
    }

    /**
     * Prepare properties before running the processor.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return void
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
        // Add the templatename to the properties
        $properties['templatename'] = $this->argument('templatename');

        // Add options to the properties
        $optionKeys = [
            'description', 'category', 'content', 'locked', 'static', 'static_file', 'icon'
        ];

        foreach ($optionKeys as $key) {
            if ($this->option($key) !== null) {
                $properties[$key] = $this->option($key);
            }
        }
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
            $this->info('Template created successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Template ID: ' . $response['object']['id']);
            }
            return 0;
        } else {
            $this->error('Failed to create template');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
