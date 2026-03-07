<?php

namespace MODX\CLI\Command\System\Locks;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to remove a lock in MODX
 */
class Remove extends BaseCmd
{
    public const MODX = true;

    protected $name = 'system:locks:remove';
    protected $description = 'Remove a lock in MODX';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'key',
                InputArgument::REQUIRED,
                'The key of the lock'
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
    protected function process()
    {
        $key = $this->argument('key');

        // Get the registry
        $registry = $this->modx->getService('registry', 'registry.modRegistry');
        $registry->addRegister('locks', 'registry.modDbRegister', ['directory' => 'locks']);
        $registry->locks->connect();

        // Check if the lock exists
        $locks = $registry->locks->read([$key]);

        if (empty($locks)) {
            $message = $this->trans('system.locks.remove.not_found', ['%key%' => $key], 'commands');
            if ($this->option('json')) {
                $this->output->writeln(json_encode([
                    'success' => false,
                    'message' => $message,
                    'lock' => [
                        'key' => $key,
                    ],
                ], JSON_PRETTY_PRINT));
            } else {
                $this->error($message);
            }
            return 1;
        }

        $lockData = $locks[$key];
        $lockInfo = [
            'key' => $key,
            'user' => isset($lockData['user']) ? $lockData['user'] : 'Unknown',
            'message' => isset($lockData['message']) ? $lockData['message'] : '',
            'timestamp' => isset($lockData['timestamp']) ? $lockData['timestamp'] : null,
        ];
        $lockInfo['occurred'] = $lockInfo['timestamp']
            ? date('Y-m-d H:i:s', $lockInfo['timestamp'])
            : null;

        // Confirm removal unless --force is used
        if (!$this->option('force')) {
            if (!$this->option('json')) {
                $this->info($this->trans('system.locks.remove.info', [], 'commands'));
                $this->info($this->trans('system.locks.remove.key_label', [], 'commands') . $lockInfo['key']);
                $this->info($this->trans('system.locks.remove.user_label', [], 'commands') . $lockInfo['user']);
                $this->info($this->trans('system.locks.remove.message_label', [], 'commands') . $lockInfo['message']);
                $this->info($this->trans('system.locks.remove.timestamp_label', [], 'commands') . $lockInfo['occurred']);
            }

            if (!$this->confirm($this->trans('system.locks.remove.confirm', [], 'commands'))) {
                $message = $this->trans('operation_aborted', [], 'errors');
                if ($this->option('json')) {
                    $this->output->writeln(json_encode([
                        'success' => false,
                        'message' => $message,
                        'removed' => false,
                        'lock' => $lockInfo,
                    ], JSON_PRETTY_PRINT));
                } else {
                    $this->info($message);
                }
                return 0;
            }
        }

        // Remove the lock
        $registry->locks->subscribe([$key]);
        $registry->locks->remove();

        $message = $this->trans('system.locks.remove.success', ['%key%' => $key], 'commands');
        if ($this->option('json')) {
            $this->output->writeln(json_encode([
                'success' => true,
                'message' => $message,
                'removed' => true,
                'lock' => $lockInfo,
            ], JSON_PRETTY_PRINT));
        } else {
            $this->info($message);
        }

        return 0;
    }
}
