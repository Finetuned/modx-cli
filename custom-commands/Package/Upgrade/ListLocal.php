<?php

namespace MODX\CLI\Command\Package\Upgrade;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Helper\Table;

/**
 * A command to list downloaded package upgrades ready for installation
 */
class ListLocal extends BaseCmd
{
    protected $name = 'package:upgrade:list';
    protected $description = 'List downloaded package upgrades ready for installation';
    
    // This command requires MODX to be available
    const MODX = true;

    // Test helper properties for mocking
    private $packagesPath = null;
    private $installedPackages = null;
    private $downloadedPackages = null;

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'filter',
                null,
                InputOption::VALUE_REQUIRED,
                'Filter packages by name pattern'
            ],
            [
                'format',
                null,
                InputOption::VALUE_REQUIRED,
                'Output format (table, json)',
                'table'
            ]
        ]);
    }

    protected function process()
    {
        $upgrades = $this->getAvailableUpgrades();
        
        if (empty($upgrades)) {
            $this->info('No downloaded package upgrades found');
            return 0;
        }

        // Apply filter if provided
        $filter = $this->option('filter');
        if ($filter) {
            $upgrades = array_filter($upgrades, function($upgrade) use ($filter) {
                return stripos($upgrade['name'], $filter) !== false;
            });
        }

        if ($this->option('json') || $this->option('format') === 'json') {
            $this->line(json_encode(array_values($upgrades)));
        } else {
            $this->renderUpgradesTable($upgrades);
        }

        return 0;
    }

    private function getAvailableUpgrades()
    {
        $installedPackages = $this->getInstalledPackages();
        $downloadedPackages = $this->getDownloadedPackages();
        
        $upgrades = [];
        
        foreach ($installedPackages as $installed) {
            $packageName = $installed['name'];
            
            // Look for downloaded packages with higher versions
            foreach ($downloadedPackages as $downloaded) {
                if (preg_match('/^' . preg_quote($packageName) . '-(.+?)\.transport\.zip$/', $downloaded, $matches)) {
                    $availableVersion = $matches[1];
                    
                    // Simple version comparison (this could be enhanced)
                    if ($this->isNewerVersion($availableVersion, $installed['version'] . '-' . $installed['release'])) {
                        $upgrades[] = [
                            'name' => $packageName,
                            'current_version' => $installed['version'],
                            'current_release' => $installed['release'],
                            'available_version' => $this->parseVersion($availableVersion)['version'],
                            'available_release' => $this->parseVersion($availableVersion)['release'],
                            'signature' => $packageName . '-' . $availableVersion
                        ];
                        break; // Only show one upgrade per package
                    }
                }
            }
        }
        
        return $upgrades;
    }

    private function getInstalledPackages()
    {
        // For testing, use mock data if available
        if ($this->installedPackages !== null) {
            return $this->installedPackages;
        }
        
        // Query MODX for installed packages using the same processor as package:list
        if (!$this->modx) {
            return [];
        }
        
        $response = $this->modx->runProcessor('workspace/packages/getlist', array(
            'limit' => 0 // Get all packages
        ), array());
        
        if ($response->isError()) {
            return [];
        }
        
        $responseData = json_decode($response->getResponse(), true);
        if (!isset($responseData['results'])) {
            return [];
        }
        
        // Filter only installed packages
        $installedPackages = [];
        foreach ($responseData['results'] as $package) {
            if (isset($package['installed']) && $package['installed'] !== null) {
                $installedPackages[] = $package;
            }
        }
        
        return $installedPackages;
    }

    private function getDownloadedPackages()
    {
        // For testing, use mock data if available
        if ($this->downloadedPackages !== null) {
            return $this->downloadedPackages;
        }
        
        $packagesPath = $this->getPackagesPath();
        
        if (!is_dir($packagesPath)) {
            return [];
        }
        
        $files = scandir($packagesPath);
        $packages = [];
        
        foreach ($files as $file) {
            if (preg_match('/\.transport\.zip$/', $file)) {
                $packages[] = $file;
            }
        }
        
        return $packages;
    }

    private function getPackagesPath()
    {
        // For testing, use mock path if available
        if ($this->packagesPath !== null) {
            return $this->packagesPath;
        }
        
        $corePath = $this->modx->getOption('core_path');
        return $corePath . 'packages/';
    }

    private function isNewerVersion($version1, $version2)
    {
        // Simple version comparison - could be enhanced with proper semantic versioning
        return version_compare($version1, $version2, '>');
    }

    private function parseVersion($versionString)
    {
        // Parse version string like "3.0.2-pl" into version and release
        if (preg_match('/^(.+?)-(.+)$/', $versionString, $matches)) {
            return [
                'version' => $matches[1],
                'release' => $matches[2]
            ];
        }
        
        return [
            'version' => $versionString,
            'release' => 'pl'
        ];
    }

    private function renderUpgradesTable($upgrades)
    {
        $table = new Table($this->output);
        $table->setHeaders(['Package', 'Current Version', 'Available Version', 'Signature']);
        
        foreach ($upgrades as $upgrade) {
            $table->addRow([
                $upgrade['name'],
                $upgrade['current_version'] . '-' . $upgrade['current_release'],
                $upgrade['available_version'] . '-' . $upgrade['available_release'],
                $upgrade['signature']
            ]);
        }
        
        $table->render();
    }

    // Test helper methods
    public function setPackagesPath($path)
    {
        $this->packagesPath = $path;
    }

    public function setInstalledPackages($packages)
    {
        $this->installedPackages = $packages;
    }

    public function setDownloadedPackages($packages)
    {
        $this->downloadedPackages = $packages;
    }
}
