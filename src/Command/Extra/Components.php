<?php

namespace MODX\CLI\Command\Extra;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Helper\Table;

/**
 * A command to get a list of components in MODX
 */
class Components extends BaseCmd
{
    public const MODX = true;

    protected $name = 'extra:components';
    protected $description = 'Get a list of components in MODX';

    /**
     * Execute the command.
     *
     * @return integer
     */
    protected function process()
    {
        // Get all components
        $components = [];
        $json = (bool) $this->option('json');

        // Get all namespaces
        $namespaces = $this->modx->getCollection(\MODX\Revolution\modNamespace::class);

        if (empty($namespaces)) {
            if ($json) {
                $this->output->writeln(json_encode([
                    'total' => 0,
                    'results' => [],
                ], JSON_PRETTY_PRINT));
            } else {
                $this->info('No namespaces found');
            }
            return 0;
        }

        /** @var \MODX\Revolution\modNamespace $namespace */
        foreach ($namespaces as $namespace) {
            $name = $namespace->get('name');

            // Skip core namespaces
            if ($name === 'core') {
                continue;
            }

            $path = $namespace->get('path');

            // Check if the namespace has a component
            $componentPath = $path . 'controllers/index.php';
            if (file_exists($this->modx->getOption('base_path') . $componentPath)) {
                $components[] = [
                    'name' => $name,
                    'path' => $path,
                    'controller' => $componentPath,
                ];
            }
        }

        if (empty($components)) {
            if ($json) {
                $this->output->writeln(json_encode([
                    'total' => 0,
                    'results' => [],
                ], JSON_PRETTY_PRINT));
            } else {
                $this->info('No components found');
            }
            return 0;
        }

        // Sort components by name
        usort($components, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        if ($json) {
            $this->output->writeln(json_encode([
                'total' => count($components),
                'results' => $components,
            ], JSON_PRETTY_PRINT));
        } else {
            $table = new Table($this->output);
            $table->setHeaders(['Name', 'Path', 'Controller']);

            foreach ($components as $component) {
                $table->addRow([
                    $component['name'],
                    $component['path'],
                    $component['controller'],
                ]);
            }
            $table->render();
        }

        return 0;
    }
}
