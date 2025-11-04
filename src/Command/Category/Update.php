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
    protected $required = array('id');

    protected $name = 'category:update';
    protected $description = 'Update a MODX category';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the category to update'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'category',
                null,
                InputOption::VALUE_REQUIRED,
                'The name of the category'
            ),
            array(
                'parent',
                null,
                InputOption::VALUE_REQUIRED,
                'The parent ID of the category'
            ),
            array(
                'rank',
                null,
                InputOption::VALUE_REQUIRED,
                'The rank of the category'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        // Add options to the properties
        $optionKeys = array(
            'category', 'parent', 'rank'
        );

        foreach ($optionKeys as $key) {
            if ($this->option($key) !== null) {
                $properties[$key] = $this->option($key);
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json') || $this->option('format') === 'json') {
            return parent::processResponse($response);
        }

        if (isset($response['success']) && $response['success']) {
            $this->info('Category updated successfully');

            if (isset($response['object']) && isset($response['object']['id'])) {
                $this->info('Category ID: ' . $response['object']['id']);
            }
        } else {
            $this->error('Failed to update category');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
        }
    }
}
