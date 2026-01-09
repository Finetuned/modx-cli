<?php

namespace MODX\CLI\Command\Extra;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Helper\Table;

/**
 * A command to get a list of extras in MODX
 */
class Extras extends BaseCmd
{
    const MODX = true;

    protected $name = 'extra:list';
    protected $description = 'Get a list of extras in MODX';

    protected function process()
    {
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

        // Get all packages first to create a lookup table
        $packagesLookup = $this->getPackagesLookup();

        $extras = array();

        /** @var \MODX\Revolution\modNamespace $namespace */
        foreach ($namespaces as $namespace) {
            $name = $namespace->get('name');

            // Skip core namespaces
            if ($name === 'core') {
                continue;
            }

            // Try to find the package using our lookup table
            $packageInfo = $this->findPackageForNamespace($name, $packagesLookup);

            $extras[] = array(
                'name' => $name,
                'path' => $namespace->get('path'),
                'version' => $packageInfo ? $packageInfo['version'] : 'Unknown',
                'installed' => $packageInfo ? $packageInfo['installed'] : 'No',
            );
        }

        if (empty($extras)) {
            if ($json) {
                $this->output->writeln(json_encode([
                    'total' => 0,
                    'results' => [],
                ], JSON_PRETTY_PRINT));
            } else {
                $this->info('No extras found');
            }
            return 0;
        }

        // Sort extras by name
        usort($extras, function ($a, $b) {
            return strcmp($a['name'], $b['name']);
        });

        if ($json) {
            $this->output->writeln(json_encode([
                'total' => count($extras),
                'results' => $extras,
            ], JSON_PRETTY_PRINT));
        } else {
            $table = new Table($this->output);
            $table->setHeaders(array('Name', 'Path', 'Version', 'Installed'));

            foreach ($extras as $extra) {
                $table->addRow(array(
                    $extra['name'],
                    $extra['path'],
                    $extra['version'],
                    $extra['installed'],
                ));
            }

            $table->render();
        }

        return 0;
    }

    /**
     * Get all packages and create a lookup table
     *
     * @return array
     */
    protected function getPackagesLookup()
    {
        $lookup = [];

        // Use the same processor as package:list
        $response = $this->modx->runProcessor('workspace/packages/getlist');
        if ($response && !$response->isError()) {
            $results = $response->getResponse();
            if (!is_array($results)) {
                $results = json_decode($results, true);
            }

            if (isset($results['results']) && is_array($results['results'])) {
                foreach ($results['results'] as $package) {
                    // Create entries for both package_name and lowercase name for better matching
                    if (isset($package['package_name'])) {
                        $lookup[strtolower($package['package_name'])] = [
                            'name' => $package['package_name'],
                            'version' => isset($package['version']) ? $package['version'] : 'Unknown',
                            'installed' => isset($package['installed']) && $package['installed']
                                ? date('Y-m-d H:i:s', strtotime($package['installed']))
                                : 'No'
                        ];
                    }

                    if (isset($package['name'])) {
                        $lookup[strtolower($package['name'])] = [
                            'name' => $package['name'],
                            'version' => isset($package['version']) ? $package['version'] : 'Unknown',
                            'installed' => isset($package['installed']) && $package['installed']
                                ? date('Y-m-d H:i:s', strtotime($package['installed']))
                                : 'No'
                        ];
                    }

                    // Also add an entry for the signature without version
                    if (isset($package['signature'])) {
                        $parts = explode('-', $package['signature']);
                        if (!empty($parts[0])) {
                            $lookup[strtolower($parts[0])] = [
                                'name' => $parts[0],
                                'version' => isset($package['version']) ? $package['version'] : 'Unknown',
                                'installed' => isset($package['installed']) && $package['installed']
                                    ? date('Y-m-d H:i:s', strtotime($package['installed']))
                                    : 'No'
                            ];
                        }
                    }
                }
            }
        }

        // Fallback: If processor fails, try direct database query
        if (empty($lookup)) {
            $packages = $this->modx->getCollection(\MODX\Revolution\Transport\modTransportPackage::class);
            if ($packages) {
                foreach ($packages as $package) {
                    $packageName = $package->get('package_name');
                    if ($packageName) {
                        $lookup[strtolower($packageName)] = [
                            'name' => $packageName,
                            'version' => $package->get('version') ?: 'Unknown',
                            'installed' => $package->get('installed')
                                ? date('Y-m-d H:i:s', strtotime($package->get('installed')))
                                : 'No'
                        ];

                        // Also add signature-based entry
                        $signature = $package->get('signature');
                        if ($signature) {
                            $parts = explode('-', $signature);
                            if (!empty($parts[0])) {
                                $lookup[strtolower($parts[0])] = [
                                    'name' => $parts[0],
                                    'version' => $package->get('version') ?: 'Unknown',
                                    'installed' => $package->get('installed')
                                        ? date('Y-m-d H:i:s', strtotime($package->get('installed')))
                                        : 'No'
                                ];
                            }
                        }
                    }
                }
            }
        }

        return $lookup;
    }

    /**
     * Find package information for a namespace
     *
     * @param string $namespaceName
     * @param array $packagesLookup
     * @return array|null
     */
    protected function findPackageForNamespace($namespaceName, array $packagesLookup)
    {
        $lowerName = strtolower($namespaceName);

        // Direct match
        if (isset($packagesLookup[$lowerName])) {
            return $packagesLookup[$lowerName];
        }

        // Try to find a package whose name contains the namespace name
        foreach ($packagesLookup as $key => $packageInfo) {
            // Check if package name contains namespace name
            if (strpos($key, $lowerName) !== false) {
                return $packageInfo;
            }

            // Check if namespace name contains package name
            if (strpos($lowerName, $key) !== false) {
                return $packageInfo;
            }
        }

        // Try direct database query as last resort
        $package = $this->modx->getObject(\MODX\Revolution\Transport\modTransportPackage::class, [
            'package_name' => $namespaceName
        ]);

        if ($package) {
            return [
                'name' => $package->get('package_name'),
                'version' => $package->get('version') ?: 'Unknown',
                'installed' => $package->get('installed')
                    ? date('Y-m-d H:i:s', strtotime($package->get('installed')))
                    : 'No'
            ];
        }

        return null;
    }
}
