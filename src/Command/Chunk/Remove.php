<?php

namespace MODX\CLI\Command\Chunk;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to remove a MODX chunk
 */
class Remove extends ProcessorCmd
{
    protected $processor = 'Element\Chunk\Remove';
    protected $required = array('id');

    protected $name = 'chunk:remove';
    protected $description = 'Remove a MODX chunk';

    protected function getArguments()
    {
        return array(
            array(
                'id',
                InputArgument::REQUIRED,
                'The ID of the chunk to remove'
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

        // Get the chunk to display information
        $chunk = $this->modx->getObject(\MODX\Revolution\modChunk::class, $id);
        if (!$chunk) {
            $this->error("Chunk with ID {$id} not found");
            return false;
        }

        $chunkName = $chunk->get('name');

        // Confirm removal unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to remove chunk '{$chunkName}' (ID: {$id})?")) {
                $this->info('Operation aborted');
                return false;
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }

        
        if (isset($response['success']) && $response['success']) {
            $this->info('Chunk removed successfully');
            return 0;
        } else {
            $this->error('Failed to remove chunk');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
