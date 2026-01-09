<?php

namespace MODX\CLI\Command\Source;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to get a single MODX media source
 */
class Get extends ProcessorCmd
{
    protected $name = 'source:get';
    protected $description = 'Get a MODX media source by ID';

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

    protected function process()
    {
        $id = $this->argument('id');
        $source = $this->modx->getObject('MODX\\Revolution\\Sources\\modMediaSource', array('id' => $id));
        if (!$source) {
            $this->error("Media source with ID {$id} not found");
            return 1;
        }

        $data = $source->toArray();
        if ($this->option('json')) {
            $this->output->writeln(json_encode([
                'success' => true,
                'object' => $data
            ], JSON_PRETTY_PRINT));
            return 0;
        }

        $this->info('ID: ' . ($data['id'] ?? ''));
        $this->info('Name: ' . ($data['name'] ?? ''));
        $this->info('Description: ' . ($data['description'] ?? ''));
        $this->info('Class Key: ' . ($data['class_key'] ?? ''));
        return 0;
    }

    protected function processResponse(array $response = array())
    {
        return 0;
    }
}
