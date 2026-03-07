<?php

namespace MODX\CLI\Command\Session;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to remove a session in MODX
 */
class Remove extends BaseCmd
{
    public const MODX = true;

    protected $name = 'session:remove';
    protected $description = 'Remove a session in MODX';

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
                'The ID (internalKey) of the session to remove'
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
     * Execute the command.
     *
     * @return integer
     */
    protected function process(): int
    {
        $id = $this->argument('id');

        $session = $this->modx->getObject('MODX\\Revolution\\modSession', ['id' => $id]);
        if (!$session) {
            $this->error($this->trans('session.remove.not_found', ['%id%' => $id], 'commands'));
            return 1;
        }

        // Confirm removal unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm($this->trans('session.remove.confirm', ['%id%' => $id], 'commands'))) {
                $this->info($this->trans('operation_aborted', [], 'errors'));
                return 0;
            }
        }

        if ($session->remove()) {
            if ($this->option('json')) {
                $this->output->writeln(json_encode([
                    'success' => true,
                    'message' => $this->trans('session.remove.success', [], 'commands'),
                ]));
            } else {
                $this->info($this->trans('session.remove.success', [], 'commands'));
            }
            return 0;
        } else {
            if ($this->option('json')) {
                $this->output->writeln(json_encode([
                    'success' => false,
                    'message' => $this->trans('session.remove.failed', [], 'commands'),
                ]));
            } else {
                $this->error($this->trans('session.remove.failed', [], 'commands'));
            }
            return 1;
        }
    }
}
