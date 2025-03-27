<?php namespace MODX\CLI\Command\Extra;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to remove a component from MODX
 */
class RemoveComponent extends BaseCmd
{
    const MODX = true;

    protected $name = 'extra:remove-component';
    protected $description = 'Remove a component from MODX';

    protected function getArguments()
    {
        return array(
            array(
                'namespace',
                InputArgument::REQUIRED,
                'The namespace of the component'
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
            array(
                'files',
                null,
                InputOption::VALUE_NONE,
                'Remove files as well'
            ),
        ));
    }

    protected function process()
    {
        $namespace = $this->argument('namespace');
        
        // Check if the namespace exists
        $ns = $this->modx->getObject('modNamespace', $namespace);
        if (!$ns) {
            $this->error("Namespace '{$namespace}' does not exist");
            return 1;
        }
        
        // Get the paths
        $path = $ns->get('path');
        $assetsPath = $ns->get('assets_path');
        
        // Confirm removal unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to remove component '{$namespace}'?")) {
                $this->info('Operation aborted');
                return 0;
            }
        }
        
        // Remove the menu
        $menu = $this->modx->getObject('modMenu', array(
            'namespace' => $namespace,
            'action' => 'index',
        ));
        
        if ($menu) {
            if ($menu->remove()) {
                $this->info("Removed menu for {$namespace}");
            } else {
                $this->error("Failed to remove menu for {$namespace}");
            }
        }
        
        // Remove the namespace
        if ($ns->remove()) {
            $this->info("Namespace '{$namespace}' removed successfully");
            
            // Remove files if requested
            if ($this->option('files')) {
                $basePath = $this->modx->getOption('base_path');
                
                // Remove core files
                if ($path && file_exists($basePath . $path)) {
                    if ($this->removeDirectory($basePath . $path)) {
                        $this->info("Removed directory: {$basePath}{$path}");
                    } else {
                        $this->error("Failed to remove directory: {$basePath}{$path}");
                    }
                }
                
                // Remove assets files
                if ($assetsPath && file_exists($basePath . $assetsPath)) {
                    if ($this->removeDirectory($basePath . $assetsPath)) {
                        $this->info("Removed directory: {$basePath}{$assetsPath}");
                    } else {
                        $this->error("Failed to remove directory: {$basePath}{$assetsPath}");
                    }
                }
            }
            
            $this->info("Component '{$namespace}' removed successfully");
        } else {
            $this->error("Failed to remove namespace '{$namespace}'");
        }
        
        return 0;
    }
    
    /**
     * Remove a directory and its contents
     *
     * @param string $dir
     *
     * @return bool
     */
    protected function removeDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }
        
        if (!is_dir($dir)) {
            return unlink($dir);
        }
        
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }
            
            if (!$this->removeDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }
        
        return rmdir($dir);
    }
}
