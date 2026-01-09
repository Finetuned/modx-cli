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
    const MODX = true;

    protected $name = 'system:locks:remove';
    protected $description = 'Remove a lock in MODX';

    protected function getArguments()
    {
        return array(
            array(
                'key',
                InputArgument::REQUIRED,
                'The key of the lock'
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

    protected function process()
    {
        $key = $this->argument('key');

        // Get the registry
        $registry = $this->modx->getService('registry', 'registry.modRegistry');
        $registry->addRegister('locks', 'registry.modDbRegister', array('directory' => 'locks'));
        $registry->locks->connect();

        // Check if the lock exists
        $locks = $registry->locks->read(array($key));

        if (empty($locks)) {
            $message = "Lock with key '{$key}' not found";
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
                $this->info('Lock information:');
                $this->info("Key: {$lockInfo['key']}");
                $this->info("User: {$lockInfo['user']}");
                $this->info("Message: {$lockInfo['message']}");
                $this->info("Timestamp: {$lockInfo['occurred']}");
            }

            if (!$this->confirm("Are you sure you want to remove this lock?")) {
                $message = 'Operation aborted';
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
        $registry->locks->subscribe(array($key));
        $registry->locks->remove();

        $message = "Lock with key '{$key}' removed successfully";
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
