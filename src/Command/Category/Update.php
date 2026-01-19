<?php

namespace MODX\CLI\Command\Category;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to update a MODX category
 */
class Update extends ProcessorCmd
{
    protected $processor = 'Element\Category\Update';
    protected $required = ['id'];

    protected $name = 'category:update';
    protected $description = 'Update a MODX category';

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
                'The ID of the category to update'
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
                'category',
                null,
                InputOption::VALUE_REQUIRED,
                'The name of the category'
            ],
            [
                'parent',
                null,
                InputOption::VALUE_REQUIRED,
                'The parent ID of the category'
            ],
            [
                'rank',
                null,
                InputOption::VALUE_REQUIRED,
                'The rank of the category'
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
        // Add options to the properties
        $optionKeys = [
            'category', 'parent', 'rank'
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
            $this->info('Category updated successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Category ID: ' . $response['object']['id']);
            }
            return 0;
        } else {
            $this->error('Failed to update category');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
