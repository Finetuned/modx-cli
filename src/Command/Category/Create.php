<?php

namespace MODX\CLI\Command\Category;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to create a MODX category
 */
class Create extends ProcessorCmd
{
    protected $processor = 'Element\Category\Create';

    protected $name = 'category:create';
    protected $description = 'Create a MODX category';

    protected function getArguments()
    {
        return array(
            array(
                'category',
                InputArgument::REQUIRED,
                'The name of the category'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'parent',
                null,
                InputOption::VALUE_REQUIRED,
                'The parent ID of the category',
                0
            ),
            array(
                'rank',
                null,
                InputOption::VALUE_REQUIRED,
                'The rank of the category',
                0
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Add the category name to the properties
        $properties['category'] = $this->argument('category');

        // Add options to the properties
        $optionKeys = array(
            'parent', 'rank'
        );

        foreach ($optionKeys as $key) {
            if ($this->option($key) !== null) {
                $properties[$key] = $this->option($key);
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }

        if (isset($response['success']) && $response['success']) {
            $this->info('Category created successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Category ID: ' . $response['object']['id']);
            }
            return 0;
        } else {
            $this->error('Failed to create category');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
