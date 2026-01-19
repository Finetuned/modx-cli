<?php

namespace MODX\CLI\Command\Package;

use MODX\CLI\Command\ListProcessor;

/**
 * A command to get a list of upgradeable packages in MODX
 */
class Upgradeable extends ListProcessor
{
    protected $processor = 'Workspace\\Packages\\GetList';
    protected $headers = [
        'signature', 'name', 'version', 'release', 'upgrade_signature', 'installed', 'provider'
    ];

    protected $name = 'package:upgradeable';
    protected $description = 'Get a list of upgradeable packages in MODX';
    protected $defaultsProperties = [
        'newest_only' => true
    ];

    /**
     * Format raw values for output.
     *
     * @param mixed  $value  The raw column value.
     * @param string $column The column name.
     * @return mixed
     */
    protected function parseValue(mixed $value, string $column)
    {
        if ($column === 'installed') {
            return $value ? date('Y-m-d H:i:s', strtotime($value)) : 'Not installed';
        }

        if ($column === 'provider') {
            return $this->renderObject('transport.modTransportProvider', $value, 'name');
        }

        if ($column === 'upgrade_signature') {
            return $value ?: 'N/A';
        }

        return parent::parseValue($value, $column);
    }

    /**
     * Handle the processor response.
     *
     * @param array $results The processor response.
     * @return integer
     */
    protected function processResponse(array $results = [])
    {
        $total = $results['total'];
        $results = $results['results'];

        // Filter out packages that are not upgradeable and add upgrade signatures
        $upgradeable = [];
        foreach ($results as $package) {
            if (isset($package['updateable']) && $package['updateable']) {
                // Fetch upgrade signature from provider
                $upgradeSignature = $this->getUpgradeSignatureForPackage($package);
                $package['upgrade_signature'] = $upgradeSignature;
                $upgradeable[] = $package;
            }
        }

        if (empty($upgradeable)) {
            $this->info('No upgradeable packages found');
            return 0;
        }

        // Handle JSON output option
        if ($this->option('json')) {
            $output = [
                'total' => count($upgradeable),
                'results' => $upgradeable
            ];
            $this->output->writeln(json_encode($output, JSON_PRETTY_PRINT));
            return 0;
        }

        $this->renderBody($upgradeable);
        if ($this->showPagination) {
            $this->renderPagination($upgradeable, count($upgradeable));
        }
        return 0;
    }

    /**
     * Get the upgrade signature for a package by querying the provider
     *
     * @param array $package Package data from processor.
     * @return string Upgrade signature or empty string if unavailable.
     */
    protected function getUpgradeSignatureForPackage(array $package): string
    {
        $currentSignature = $package['signature'] ?? '';
        $currentVersion = $package['version'] . '-' . $package['release'];

        if (empty($currentSignature)) {
            return '';
        }

        try {
            // Get package object from MODX
            $packageObject = $this->modx->getObject('MODX\\Revolution\\Transport\\modTransportPackage', [
                'signature' => $currentSignature
            ]);

            if (!$packageObject) {
                return '';
            }

            // Get provider object
            /** @var \MODX\Revolution\Transport\modTransportProvider $provider */
            $provider = $packageObject->getOne('Provider');
            if (!$provider) {
                return '';
            }

            // Get latest updates from provider
            $updates = $provider->latest($packageObject->get('signature'));

            // If updates is a string or empty, no updates available
            if (is_string($updates) || empty($updates)) {
                return '';
            }

            // Extract package name from signature for matching
            $signatureParts = explode('-', $currentSignature);
            if (count($signatureParts) < 3) {
                return '';
            }
            $currentPackageName = $signatureParts[0];

            // Find newer versions
            $newerVersions = [];
            foreach ($updates as $update) {
                $updateSignature = $update['signature'] ?? '';
                if (empty($updateSignature)) {
                    continue;
                }

                // Parse update signature
                $updateParts = explode('-', $updateSignature);
                if (count($updateParts) < 3) {
                    continue;
                }

                $updateName = $updateParts[0];
                $updateVersion = $updateParts[1];
                $updateRelease = $updateParts[2];
                $updateVersionString = $updateVersion . '-' . $updateRelease;

                // Only include versions newer than current and matching package name
                if (
                    strcasecmp($updateName, $currentPackageName) === 0 &&
                    version_compare($updateVersionString, $currentVersion, '>')
                ) {
                    $newerVersions[] = [
                        'signature' => $updateSignature,
                        'version_string' => $updateVersionString
                    ];
                }
            }

            // Sort versions (newest first)
            usort($newerVersions, function ($a, $b) {
                return version_compare($b['version_string'], $a['version_string']);
            });

            // Return the newest upgrade signature
            if (!empty($newerVersions)) {
                return $newerVersions[0]['signature'];
            }

            return '';
        } catch (\Exception $e) {
            // Silently fail and return empty string
            return '';
        }
    }
}
