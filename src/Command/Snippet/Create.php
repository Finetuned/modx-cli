<?php

namespace MODX\CLI\Command\Snippet;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to create a MODX snippet
 */
class Create extends ProcessorCmd
{
    protected $processor = 'Element\Snippet\Create';

    protected $name = 'snippet:create';
    protected $description = 'Create a MODX snippet';

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
                'The name of the snippet'
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
                'The description of the snippet',
                ''
            ],
            [
                'category',
                null,
                InputOption::VALUE_REQUIRED,
                'The category ID of the snippet',
                0
            ],
            [
                'snippet',
                null,
                InputOption::VALUE_REQUIRED,
                'The PHP code of the snippet',
                ''
            ],
            [
                'locked',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the snippet is locked (1 or 0)',
                0
            ],
            [
                'static',
                null,
                InputOption::VALUE_REQUIRED,
                'Whether the snippet is static (1 or 0)',
                0
            ],
            [
                'static_file',
                null,
                InputOption::VALUE_REQUIRED,
                'The static file path for the snippet',
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
            'description', 'category', 'snippet', 'locked', 'properties', 'static', 'static_file'
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
            $this->info('Snippet created successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Snippet ID: ' . $response['object']['id']);
            }
            return 0;
        } else {
            $this->error('Failed to create snippet');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
