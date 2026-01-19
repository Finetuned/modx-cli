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
    protected $required = ['id'];

    protected $name = 'chunk:remove';
    protected $description = 'Remove a MODX chunk';

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
                'The ID of the chunk to remove'
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
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force removal without confirmation'
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
