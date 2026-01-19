<?php

namespace MODX\CLI\Command\System\Locks;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to read a lock in MODX
 */
class Read extends BaseCmd
{
    public const MODX = true;

    protected $name = 'system:locks:read';
    protected $description = 'Read a lock in MODX';

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
                InputArgument::OPTIONAL,
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
                'format',
                'f',
                InputOption::VALUE_REQUIRED,
                'Output format (table, json)',
                'table'
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
        $format = $this->option('format');
        if ($this->option('json')) {
            $format = 'json';
        }

        // Get the registry
        $registry = $this->modx->getService('registry', 'registry.modRegistry');
        $registry->addRegister('locks', 'registry.modDbRegister', ['directory' => 'locks']);
        $registry->locks->connect();

        // Get the locks
        if ($key) {
            $locks = $registry->locks->read([$key]);

            if (empty($locks)) {
                $this->error("Lock with key '{$key}' not found");
                return 1;
            }

            $locks = [$key => $locks[$key]];
        } else {
            $locks = $registry->locks->read(['']);

            if (empty($locks)) {
                $this->info('No locks found');
                return 0;
            }
        }

        if ($format === 'json') {
            $this->output->writeln(json_encode($locks, JSON_PRETTY_PRINT));
            return 0;
        }

        // Default to table format
        $table = new Table($this->output);
        $table->setHeaders(['Key', 'User', 'Message', 'Timestamp']);

        foreach ($locks as $lockKey => $lockData) {
            $user = '';
            $message = '';
            $timestamp = '';

            if (is_array($lockData)) {
                if (isset($lockData['user'])) {
                    $user = $lockData['user'];
                }
                if (isset($lockData['message'])) {
                    $message = $lockData['message'];
                }
                if (isset($lockData['timestamp'])) {
                    $timestamp = date('Y-m-d H:i:s', $lockData['timestamp']);
                }
            }

            $table->addRow([$lockKey, $user, $message, $timestamp]);
        }

        $table->render();

        return 0;
    }
}
