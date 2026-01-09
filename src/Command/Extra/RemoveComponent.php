<?php

namespace MODX\CLI\Command\Extra;

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
    protected $jsonOutput = false;
    protected $actions = array();

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
        $this->jsonOutput = (bool) $this->option('json');
        $this->actions = array();
        $namespace = $this->argument('namespace');

        // Check if the namespace exists
        $ns = $this->modx->getObject(\MODX\Revolution\modNamespace::class, $namespace);
        if (!$ns) {
            $this->outputResult(false, "Namespace '{$namespace}' does not exist", array(
                'namespace' => $namespace,
            ));
            return 1;
        }

        // Get the paths
        $path = $ns->get('path');
        $assetsPath = $ns->get('assets_path');

        // Confirm removal unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to remove component '{$namespace}'?")) {
                $this->outputResult(false, 'Operation aborted', array(
                    'namespace' => $namespace,
                    'removed' => false,
                ));
                return 0;
            }
        }

        // Remove the menu
        $menu = $this->modx->getObject(\MODX\Revolution\modMenu::class, array(
            'namespace' => $namespace,
            'action' => 'index',
        ));

        if ($menu) {
            if ($menu->remove()) {
                $this->emitInfo("Removed menu for {$namespace}");
            } else {
                $this->emitError("Failed to remove menu for {$namespace}");
            }
        }

        // Remove the namespace
        if ($ns->remove()) {
            $this->emitInfo("Namespace '{$namespace}' removed successfully");

            // Remove files if requested
            if ($this->option('files')) {
                $basePath = $this->modx->getOption('base_path');

                // Remove core files
                if ($path && file_exists($basePath . $path)) {
                    if ($this->removeDirectory($basePath . $path)) {
                        $this->emitInfo("Removed directory: {$basePath}{$path}");
                    } else {
                        $this->emitError("Failed to remove directory: {$basePath}{$path}");
                    }
                }

                // Remove assets files
                if ($assetsPath && file_exists($basePath . $assetsPath)) {
                    if ($this->removeDirectory($basePath . $assetsPath)) {
                        $this->emitInfo("Removed directory: {$basePath}{$assetsPath}");
                    } else {
                        $this->emitError("Failed to remove directory: {$basePath}{$assetsPath}");
                    }
                }
            }

            $this->outputResult(true, "Component '{$namespace}' removed successfully", array(
                'namespace' => $namespace,
                'removed' => true,
                'files' => (bool) $this->option('files'),
            ));
        } else {
            $this->outputResult(false, "Failed to remove namespace '{$namespace}'", array(
                'namespace' => $namespace,
                'removed' => false,
                'files' => (bool) $this->option('files'),
            ));
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

    protected function emitInfo($message)
    {
        if ($this->jsonOutput) {
            $this->actions[] = array('success' => true, 'message' => $message);
            return;
        }

        $this->info($message);
    }

    protected function emitError($message)
    {
        if ($this->jsonOutput) {
            $this->actions[] = array('success' => false, 'message' => $message);
            return;
        }

        $this->error($message);
    }

    protected function outputResult($success, $message, array $payload = array())
    {
        if ($this->jsonOutput) {
            $this->output->writeln(json_encode(array_merge(array(
                'success' => (bool) $success,
                'message' => $message,
                'actions' => $this->actions,
            ), $payload), JSON_PRETTY_PRINT));
        } else {
            if ($success) {
                $this->info($message);
            } else {
                $this->error($message);
            }
        }
    }
}
