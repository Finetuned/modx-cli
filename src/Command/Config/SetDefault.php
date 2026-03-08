<?php

namespace MODX\CLI\Command\Config;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to set a MODX instance as the default
 */
class SetDefault extends BaseCmd
{
    protected $name = 'config:set-default';
    protected $description = 'Set a MODX instance as the default';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
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
                'The name of the instance to set as default'
            ],
        ];
    }

    /**
     * Execute the command.
     *
     * @return integer
     */
    /**
     * Execute the command.
     *
     * @return integer
     */
    protected function process()
    {
        $name = $this->argument('name');
        $instances = $this->getApplication()->instances;

        // Check if the instance exists
        if (!$instances->get($name)) {
            if ($this->option('json')) {
                $this->output->writeln(json_encode([
                    'success' => false,
                    'message' => $this->trans('config.setdefault.not_found', ['%name%' => $name], 'commands'),
                ], JSON_PRETTY_PRINT));
            } else {
                $this->error($this->trans('config.setdefault.not_found', ['%name%' => $name], 'commands'));
            }
            return 1;
        }

        // Set the instance as default
        $instances->set('__default__', [
            'class' => $name,
        ]);
        $instances->save();

        $message = $this->trans('config.setdefault.success', ['%name%' => $name], 'commands');
        if ($this->option('json')) {
            $this->output->writeln(json_encode([
                'success' => true,
                'message' => $message,
                'default' => [
                    'name' => $name,
                ],
            ], JSON_PRETTY_PRINT));
        } else {
            $this->info($message);
        }

        return 0;
    }
}
