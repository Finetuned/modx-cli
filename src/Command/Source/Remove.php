<?php

namespace MODX\CLI\Command\Source;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to remove a MODX media source
 */
class Remove extends ProcessorCmd
{
    protected $processor = 'Source\Remove';

    protected $name = 'source:remove';
    protected $description = 'Remove a MODX media source';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The source ID'
            ),
        );
    }

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), array(
            array(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force removal without confirmation'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $id = $this->argument('id');
        $properties['id'] = $id;

        // Ask for confirmation unless --force is used
        if (!$this->option('force')) {
            $confirmed = $this->confirm(
                "Are you sure you want to remove the media source with ID '{$id}'?",
                false
            );

            if (!$confirmed) {
                $this->info('Media source removal cancelled');
                exit(0);
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }
        
        if (isset($response['success']) && $response['success']) {
            $this->info('Media source removed successfully');
            return 0;
        } else {
            $this->error('Failed to remove media source');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}