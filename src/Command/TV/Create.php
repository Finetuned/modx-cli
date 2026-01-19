<?php

namespace MODX\CLI\Command\TV;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to create a MODX template variable
 */
class Create extends ProcessorCmd
{
    protected $processor = 'Element\Tv\Create';

    protected $name = 'tv:create';
    protected $description = 'Create a MODX template variable';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'name',
                InputArgument::REQUIRED,
                'The name of the template variable'
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
                'caption',
                null,
                InputOption::VALUE_REQUIRED,
                'The caption of the template variable',
                ''
            ],
            [
                'description',
                null,
                InputOption::VALUE_REQUIRED,
                'The description of the template variable',
                ''
            ],
            [
                'category',
                null,
                InputOption::VALUE_REQUIRED,
                'The category ID of the template variable',
                0
            ],
            [
                'type',
                null,
                InputOption::VALUE_REQUIRED,
                'The input type of the template variable (text, textarea, richtext, etc.)',
                'text'
            ],
            [
                'default_text',
                null,
                InputOption::VALUE_REQUIRED,
                'The default value of the template variable',
                ''
            ],
            [
                'elements',
                null,
                InputOption::VALUE_REQUIRED,
                'The possible values for the template variable (for select, radio, etc.)',
                ''
            ],
            [
                'rank',
                null,
                InputOption::VALUE_REQUIRED,
                'The rank of the template variable',
                0
            ],
            [
                'display',
                null,
                InputOption::VALUE_REQUIRED,
                'The display type of the template variable',
                'default'
            ],
            [
                'templates',
                null,
                InputOption::VALUE_REQUIRED,
                'Comma-separated list of template IDs to associate with the template variable',
                ''
            ],
            [
                'locked',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the template variable is locked (1 or 0)',
                0
            ],
            [
                'static',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the template variable is static (1 or 0)',
                0
            ],
            [
                'static_file',
                null,
                InputOption::VALUE_REQUIRED,
                'The static file path for the template variable',
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
        // Add the name to the properties
        $properties['name'] = $this->argument('name');

        // Add options to the properties
        $optionKeys = [
            'caption', 'description', 'category', 'type', 'default_text', 'elements',
            'rank', 'display', 'templates', 'locked', 'static', 'static_file'
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
            $this->info('Template variable created successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Template variable ID: ' . $response['object']['id']);
            }
            return 0;
        } else {
            $this->error('Failed to create template variable');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
