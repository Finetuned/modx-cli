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
                'The source ID'
            ],
        ];
    }

    /**
     * Execute the command.
     *
     * @return integer
     */
    protected function process()
    {
        $id = $this->argument('id');
        $source = $this->modx->getObject('MODX\\Revolution\\Sources\\modMediaSource', ['id' => $id]);
        if (!$source) {
            $this->error($this->trans('source.get.not_found', ['%id%' => $id], 'commands'));
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

        $this->info($this->trans('source.get.id_label', [], 'commands') . ($data['id'] ?? ''));
        $this->info($this->trans('source.get.name_label', [], 'commands') . ($data['name'] ?? ''));
        $this->info($this->trans('source.get.description_label', [], 'commands') . ($data['description'] ?? ''));
        $this->info($this->trans('source.get.class_key_label', [], 'commands') . ($data['class_key'] ?? ''));
        return 0;
    }

    /**
     * Handle the processor response.
     *
     * @param array $response The processor response.
     * @return integer
     */
    protected function processResponse(array $response = [])
    {
        return 0;
    }
}
