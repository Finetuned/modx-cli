<?php

namespace MODX\CLI\Command\Package;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to install a package in MODX
 */
class Install extends ProcessorCmd
{
    protected $processor = 'Workspace\Packages\Install';
    protected $required = array('signature');

    protected $name = 'package:install';
    protected $description = 'Install a package in MODX';

    protected function getArguments()
    {
        return array(
            array(
                'signature',
                InputArgument::REQUIRED,
                'The signature of the package to install'
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
                'Force installation without confirmation'
            ),
            array(
                'no-download',
                null,
                InputOption::VALUE_NONE,
                'Disable auto-download if package is not found'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $signature = $this->argument('signature');

        // Get the package to display information
        $package = $this->modx->getObject(\MODX\Revolution\Transport\modTransportPackage::class, array('signature' => $signature));
        
        // If package not found and auto-download is enabled, try to download it
        if (!$package && !$this->option('no-download')) {
            $this->info("Package '{$signature}' not found locally. Attempting to download...");
            
            if ($this->downloadPackage($signature)) {
                // Try to get the package again after download
                $package = $this->modx->getObject(\MODX\Revolution\Transport\modTransportPackage::class, array('signature' => $signature));
                
                if ($package) {
                    $this->info("Package downloaded successfully");
                }
            } else {
                $this->error("Failed to download package '{$signature}'");
                return false;
            }
        }
        
        if (!$package) {
            $this->error("Package with signature '{$signature}' not found");
            if ($this->option('no-download')) {
                $this->error("Auto-download is disabled. Use 'package:download {$signature}' to download it first.");
            }
            return false;
        }

        // Check if the package is already installed
        if ($package->get('installed') !== null) {
            $this->error("Package '{$signature}' is already installed");
            return false;
        }

        // Confirm installation unless --force is used
        if (!$this->option('force')) {
            if (!$this->confirm("Are you sure you want to install package '{$signature}'?")) {
                $this->info('Operation aborted');
                return false;
            }
        }
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }

        if (isset($response['success']) && $response['success']) {
            $this->info('Package installed successfully');
            return 0;
        } else {
            $this->error('Failed to install package');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }

    /**
     * Download a package by signature
     * 
     * @param string $signature Package signature to download
     * @return bool True if download succeeded, false otherwise
     */
    protected function downloadPackage(string $signature): bool
    {
        try {
            // Get upgradeable packages to find the current package
            $upgradeablePackages = $this->getUpgradeablePackages();
            $currentPackageSignature = $this->findSignatureByPackageName($upgradeablePackages, $signature);

            if (!$currentPackageSignature) {
                return false;
            }

            // Get the package object
            $packageObject = $this->modx->getObject('MODX\\Revolution\\Transport\\modTransportPackage', array(
                'signature' => $currentPackageSignature
            ));

            if (!$packageObject) {
                return false;
            }

            // Get the provider object
            /** @var \MODX\Revolution\Transport\modTransportProvider $provider */
            $provider = $packageObject->getOne('Provider');
            if (!$provider) {
                return false;
            }

            // Fetch the latest version details from the provider
            $latest = $provider->latest($packageObject->get('signature'));

            if (!is_array($latest) || empty($latest)) {
                return false;
            }

            // Find the matching signature or use the first entry
            $uri = null;
            foreach ($latest as $update) {
                if (isset($update['signature']) && $update['signature'] === $signature) {
                    $uri = $update['location'];
                    break;
                }
            }

            // If not found, fall back to first entry
            if (!$uri) {
                $uri = $latest[0]['location'];
            }

            $providerId = $provider->get('id');

            // Use MODX's download processor
            $response = $this->modx->runProcessor('workspace/packages/rest/download', array(
                'info' => $uri . '::' . $signature,
                'provider' => $providerId,
            ));

            return !$response->isError();

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get upgradeable packages using existing processor
     * 
     * @return array<int, array<string, mixed>>
     */
    protected function getUpgradeablePackages(): array
    {
        $response = $this->modx->runProcessor('workspace/packages/getlist', array(
            'newest_only' => true,
            'limit' => 100
        ));

        if ($response->isError()) {
            return array();
        }

        $responseData = json_decode($response->getResponse(), true);
        if (!isset($responseData['results'])) {
            return array();
        }

        // Filter upgradeable packages
        $upgradeable = array();
        foreach ($responseData['results'] as $package) {
            if (isset($package['updateable']) && $package['updateable']) {
                $upgradeable[] = $package;
            }
        }

        return $upgradeable;
    }

    /**
     * Find a signature in upgradeable packages by package name (ignoring version and release)
     * 
     * @param array $packages Array of upgradeable packages
     * @param string $packageSignature Package signature to search for
     * @return string|null The full signature if found, or null if not found
     */
    protected function findSignatureByPackageName(array $packages, string $packageSignature): ?string
    {
        $parts = explode('-', $packageSignature, 2);
        if (empty($parts)) {
            return null;
        }
        $packageName = $parts[0];

        if (empty($packages)) {
            return null;
        }

        foreach ($packages as $package) {
            if (isset($package['signature'])) {
                $sigParts = explode('-', $package['signature'], 2);
                if (strcasecmp($sigParts[0], $packageName) === 0) {
                    return $package['signature'];
                }
            }
        }
        return null;
    }
}
