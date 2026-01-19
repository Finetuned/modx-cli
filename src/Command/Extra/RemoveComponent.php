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
    public const MODX = true;

    protected $name = 'extra:remove-component';
    protected $description = 'Remove a component from MODX';
    protected $jsonOutput = false;
    protected $actions = [];

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'namespace',
                InputArgument::REQUIRED,
                'The namespace of the component'
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
            [
                'files',
                null,
                InputOption::VALUE_NONE,
                'Remove files as well'
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
        $this->jsonOutput = (bool) $this->option('json');
        $this->actions = [];
        $namespace = $this->argument('namespace');

        // Check if the namespace exists
        $ns = $this->modx->getObject(\MODX\Revolution\modNamespace::class, $namespace);
        if (!$ns) {
            $this->outputResult(false, "Namespace '{$namespace}' does not exist", [
                'namespace' => $namespace,
            ]);
            return 1;
        }

        // Get the paths
        $path = $ns->get('path');
        $assetsPath = $ns->get('assets_path');

        // Confirm removal unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to remove component '{$namespace}'?")) {
                $this->outputResult(false, 'Operation aborted', [
                    'namespace' => $namespace,
                    'removed' => false,
                ]);
                return 0;
            }
        }

        // Remove the menu
        $menu = $this->modx->getObject(\MODX\Revolution\modMenu::class, [
            'namespace' => $namespace,
            'action' => 'index',
        ]);

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

            $this->outputResult(true, "Component '{$namespace}' removed successfully", [
                'namespace' => $namespace,
                'removed' => true,
                'files' => (bool) $this->option('files'),
            ]);
        } else {
            $this->outputResult(false, "Failed to remove namespace '{$namespace}'", [
                'namespace' => $namespace,
                'removed' => false,
                'files' => (bool) $this->option('files'),
            ]);
        }

        return 0;
    }

    /**
     * Remove a directory and its contents.
     *
     * @param string $dir The directory path.
     *
     * @return boolean
     */
    protected function removeDirectory(string $dir)
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

    /**
     * Emit an informational message or action.
     *
     * @param string $message The message to emit.
     * @return void
     */
    protected function emitInfo(string $message): void
    {
        if ($this->jsonOutput) {
            $this->actions[] = ['success' => true, 'message' => $message];
            return;
        }

        $this->info($message);
    }

    /**
     * Emit an error message or action.
     *
     * @param string $message The message to emit.
     * @return void
     */
    protected function emitError(string $message): void
    {
        if ($this->jsonOutput) {
            $this->actions[] = ['success' => false, 'message' => $message];
            return;
        }

        $this->error($message);
    }

    /**
     * Output the final result payload.
     *
     * @param boolean $success Whether the operation succeeded.
     * @param string  $message The message to display.
     * @param array   $payload Additional payload data.
     * @return void
     */
    protected function outputResult(bool $success, string $message, array $payload = []): void
    {
        if ($this->jsonOutput) {
            $this->output->writeln(json_encode(array_merge([
                'success' => (bool) $success,
                'message' => $message,
                'actions' => $this->actions,
            ], $payload), JSON_PRETTY_PRINT));
        } else {
            if ($success) {
                $this->info($message);
            } else {
                $this->error($message);
            }
        }
    }
}
