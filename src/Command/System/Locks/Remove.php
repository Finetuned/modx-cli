<?php namespace MODX\CLI\Command\System\Locks;

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
            $this->error("Lock with key '{$key}' not found");
            return 1;
        }
        
        $lockData = $locks[$key];
        
        // Confirm removal unless --force is used
        if (!$this->option('force')) {
            $user = isset($lockData['user']) ? $lockData['user'] : 'Unknown';
            $message = isset($lockData['message']) ? $lockData['message'] : '';
            $timestamp = isset($lockData['timestamp']) ? date('Y-m-d H:i:s', $lockData['timestamp']) : '';
            
            $this->info("Lock information:");
            $this->info("Key: {$key}");
            $this->info("User: {$user}");
            $this->info("Message: {$message}");
            $this->info("Timestamp: {$timestamp}");
            
            if (!$this->confirm("Are you sure you want to remove this lock?")) {
                $this->info('Operation aborted');
                return 0;
            }
        }
        
        // Remove the lock
        $registry->locks->subscribe(array($key));
        $registry->locks->remove();
        
        $this->info("Lock with key '{$key}' removed successfully");
        
        return 0;
    }
}
