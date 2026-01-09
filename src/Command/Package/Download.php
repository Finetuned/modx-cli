<?php

namespace MODX\CLI\Command\Package;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to download a package from the provider to MODX
 */
class Download extends ProcessorCmd
{
    protected $processor = 'Workspace\\Packages\\Rest\\Download';

    protected $name = 'package:download';
    protected $description = 'Download a package from the provider to MODX';

    protected function getArguments()
    {
        return array(
            array(
                'signature',
                InputArgument::REQUIRED,
                'The signature of the package to download'
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
                'Force download without confirmation'
            ),
        ));
    }

    protected function beforeRun(array &$properties = array(), array &$options = array())
    {
        $signature = $this->argument('signature');

        // Get upgradeable packages to find the current package
        $upgradeablePackages = $this->getUpgradeablePackages();
        $currentPackageSignature = $this->findSignatureByPackageName($upgradeablePackages, $signature);

        if (!$currentPackageSignature) {
            $this->error("Package not found in upgradeable packages: {$signature}");
            return false;
        }

        // Get the package object
        $packageObject = $this->modx->getObject('MODX\\Revolution\\Transport\\modTransportPackage', array(
            'signature' => $currentPackageSignature
        ));

        if (!$packageObject) {
            $this->error("Failed to retrieve package object for: {$currentPackageSignature}");
            return false;
        }

        // Get the provider object
        /** @var \MODX\Revolution\Transport\modTransportProvider $provider */
        $provider = $packageObject->getOne('Provider');
        if (!$provider) {
            $this->error("Failed to retrieve provider from package object");
            return false;
        }

        // Fetch the latest version details from the provider
        $latest = $provider->latest($packageObject->get('signature'));

        if (!is_array($latest) || empty($latest)) {
            $this->error("Failed to retrieve package data from provider service using signature: {$signature}");
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
        $providerName = $provider->get('name');

        // Confirm download unless --force is used
        if (!$this->option('force')) {
            $message = "Download package '{$signature}' from provider '{$providerName}'?";
            if (!$this->confirm($message)) {
                $this->info('Operation aborted');
                return false;
            }
        }

        // Set the properties for the processor
        $properties['info'] = $uri . '::' . $signature;
        $properties['provider'] = $providerId;

        return true;
    }

    protected function processResponse(array $response = array())
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }

        if (isset($response['success']) && $response['success']) {
            $signature = $this->argument('signature');
            $this->info("Package {$signature} downloaded successfully");
            return 0;
        } else {
            $this->error('Failed to download package');

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }

    /**
     * Get upgradeable packages using existing processor
     * 
     * @return array
     */
    protected function getUpgradeablePackages()
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
