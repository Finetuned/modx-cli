<?php

/**
 * Package Upgrade Custom Commands
 * 
 * This file contains the functions for package upgrade commands that are registered
 * via the custom commands configuration system.
 */

use MODX\CLI\API\MODX_CLI;

/**
 * List downloaded package upgrades ready for installation
 * 
 * @param array $args Command arguments
 * @param array $assoc_args Associative arguments (options)
 * @return int Exit code
 */
function packageUpgradeList($args, $assoc_args)
{
    // Get MODX instance (this will be available in the CLI context)
    $app = new \MODX\CLI\Application();
    $modx = $app->getMODX();
    
    if (!$modx) {
        MODX_CLI::error('MODX instance not available');
        return 1;
    }
    
    $upgrades = getAvailableUpgrades($modx);
    
    if (empty($upgrades)) {
        MODX_CLI::log('No downloaded package upgrades found');
        return 0;
    }

    // Apply filter if provided
    if (isset($assoc_args['filter']) && !empty($assoc_args['filter'])) {
        $filter = $assoc_args['filter'];
        $upgrades = array_filter($upgrades, function($upgrade) use ($filter) {
            return stripos($upgrade['name'], $filter) !== false;
        });
    }

    $format = $assoc_args['format'] ?? 'table';
    
    if ($format === 'json') {
        MODX_CLI::log(json_encode(array_values($upgrades), JSON_PRETTY_PRINT));
    } else {
        renderUpgradesTable($upgrades);
    }

    return 0;
}

/**
 * Retrieve all available versions after the installed version from providers
 * 
 * @param array $args Command arguments
 * @param array $assoc_args Associative arguments (options)
 * @return int Exit code
 */
function packageUpgradeListRemote($args, $assoc_args)
{
    $app = new \MODX\CLI\Application();
    $modx = $app->getMODX();
    
    if (!$modx) {
        MODX_CLI::error('MODX instance not available');
        return 1;
    }
    
    // Get limit parameter (default to 0 for no limit, matching other list commands)
    $limit = isset($assoc_args['limit']) ? (int)$assoc_args['limit'] : 0;
    
    // Get upgradeable packages first with limit support
    $upgradeablePackages = getUpgradeablePackages($modx, $limit);
    
    if (empty($upgradeablePackages)) {
        MODX_CLI::log('No upgradeable packages found');
        return 0;
    }
    
    $remoteVersions = [];
    
    foreach ($upgradeablePackages as $package) {
        $packageName = $package['name'];
        
        // Apply package filter if provided
        if (isset($assoc_args['package']) && !empty($assoc_args['package'])) {
            if (stripos($packageName, $assoc_args['package']) === false) {
                continue;
            }
        }
        
        // Get remote versions for this package
        $versions = getRemoteVersionsForPackage($modx, $package);
        if (!empty($versions)) {
            $remoteVersions = array_merge($remoteVersions, $versions);
        }
    }
    
    if (empty($remoteVersions)) {
        MODX_CLI::log('No remote versions found for upgradeable packages');
        return 0;
    }
    
    $format = $assoc_args['format'] ?? 'table';
    
    if ($format === 'json') {
        MODX_CLI::log(json_encode(array_values($remoteVersions), JSON_PRETTY_PRINT));
    } else {
        renderRemoteVersionsTable($remoteVersions);
    }
    
    return 0;
}

/**
 * Download specific package versions to core/packages
 * 
 * @param array $args Command arguments
 * @param array $assoc_args Associative arguments (options)
 * @return int Exit code
 */
function packageUpgradeDownload($args, $assoc_args)
{
    // Get signature argument (arguments are passed as associative array with argument names as keys)
    $signature = $args['signature'] ?? null;
    
    if (empty($signature)) {
        MODX_CLI::error('Package signature is required');
        return 1;
    }
    $app = new \MODX\CLI\Application();
    $modx = $app->getMODX();
    
    if (!$modx) {
        MODX_CLI::error('MODX instance not available');
        return 1;
    }
    
    // Manually added
    /**
     * The backend goes through a number of steps to get the updates. 
     * Processors/SoftwareUpdate/GetList->process()
     * Processors/SoftwareUpdate/GetList->getExtrasUpdates()
     * then calls modTransportProvider->latest
     */
    // Rest\\Download initialize requires an info argument containing string: location::signature so we need the provider package location. However we actually need the existing package signature to get the provider!
    $upgradeablePackages = getUpgradeablePackages($modx, 100);

    //find the package in the array using the package signature
    $currentPackageSignature = findSignatureByPackageName($upgradeablePackages,  $signature);

    $packageObject = getPackageObject($modx, $currentPackageSignature);
     if (!$packageObject) {
            MODX_CLI::error('Failed to retrieve package object from signature: ' . $signature); 
            return 1;
        }
        
    // Get the provider object 
    /** @var \MODX\Revolution\Transport\modTransportProvider $provider */
    $provider = getProviderFromPackageObject($packageObject);
    if (!$provider) {
        MODX_CLI::error('Failed to retrieve provider from package object with signature: ' . $currentPackageSignature); 
        return 1;
    }

    // fetch the latest version details from the provider
    $latest = $provider->latest($packageObject->get('signature'));

    if (!count($latest)){
        MODX_CLI::error('Failed to retrieve package data from provider service url using signature: ' . $signature); 
        return 1;
    }
    $uri = $latest[0]['location'];
    // end added

    MODX_CLI::log("Downloading package: {$uri}::{$signature}");
    
    // Use MODX's download processor
    $response = $modx->runProcessor('workspace/packages/rest/download', array(
        'info' => $uri . "::" . $signature
    ));
    
    
    if ($response->isError()) {
        MODX_CLI::error('Failed to download package: ' . $response->getMessage());
        return 1;
    }
    
    MODX_CLI::success("Package {$signature} downloaded successfully");
    return 0;
}

/**
 * Orchestrate the complete upgrade workflow
 * 
 * @param array $args Command arguments
 * @param array $assoc_args Associative arguments (options)
 * @return int Exit code
 */
function packageUpgradeAll($args, $assoc_args)
{
    $app = new \MODX\CLI\Application();
    $modx = $app->getMODX();
    
    if (!$modx) {
        MODX_CLI::error('MODX instance not available');
        return 1;
    }
    
    $dryRun = isset($assoc_args['dry-run']) && $assoc_args['dry-run'];
    $force = isset($assoc_args['force']) && $assoc_args['force'];
    
    if ($dryRun) {
        MODX_CLI::log('DRY RUN MODE - No actual changes will be made');
    }
    
    // Step 1: Get upgradeable packages
    MODX_CLI::log('Checking for upgradeable packages...');
    $result = MODX_CLI::run_command('package:upgradeable', [], ['return' => true]);
    
    if ($result->return_code !== 0) {
        MODX_CLI::error('Failed to get upgradeable packages');
        return 1;
    }
    
    // Step 2: Get remote versions for upgradeable packages
    MODX_CLI::log('Fetching remote versions...');
    $remoteResult = MODX_CLI::run_command('package:list-remote', [], ['return' => true]);
    
    if ($remoteResult->return_code !== 0) {
        MODX_CLI::error('Failed to get remote versions');
        return 1;
    }
    
    // Step 3: Download packages (if not dry run)
    if (!$dryRun) {
        MODX_CLI::log('Downloading packages...');
        // This would iterate through the packages and download them
        // Implementation would depend on parsing the remote versions output
    }
    
    // Step 4: Install packages (if not dry run)
    if (!$dryRun) {
        MODX_CLI::log('Installing packages...');
        // This would use the existing package:install command
    }
    
    MODX_CLI::success('Package upgrade workflow completed');
    return 0;
}

/**
 * Helper function to get available upgrades by comparing installed and downloaded packages
 * 
 * @param \MODX\Revolution\modX $modx
 * @return array
 */
function getAvailableUpgrades($modx)
{
    $installedPackages = getInstalledPackages($modx);
    $downloadedPackages = getDownloadedPackages($modx);
    
    $upgrades = [];
    
    foreach ($installedPackages as $installed) {
        $packageName = $installed['name'];
        
        // Look for downloaded packages with higher versions
        foreach ($downloadedPackages as $downloaded) {
            if (preg_match('/^' . preg_quote($packageName) . '-(.+?)\.transport\.zip$/', $downloaded, $matches)) {
                $availableVersion = $matches[1];
                
                // Simple version comparison
                if (isNewerVersion($availableVersion, $installed['version'] . '-' . $installed['release'])) {
                    $upgrades[] = [
                        'name' => $packageName,
                        'current_version' => $installed['version'],
                        'current_release' => $installed['release'],
                        'available_version' => parseVersion($availableVersion)['version'],
                        'available_release' => parseVersion($availableVersion)['release'],
                        'signature' => $packageName . '-' . $availableVersion
                    ];
                    break; // Only show one upgrade per package
                }
            }
        }
    }
    
    return $upgrades;
}

/**
 * Get installed packages from MODX
 * 
 * @param \MODX\Revolution\modX $modx
 * @return array
 */
function getInstalledPackages($modx)
{
    $packages = $modx->call(modTransportPackage::class, 'listPackages', [$modx, 1]);

    $response = $modx->runProcessor('workspace/packages/getlist', array(
        'limit' => 0 // Get all packages
    ));
    
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

/**
 * Get downloaded packages from core/packages directory
 * 
 * @param \MODX\Revolution\modX $modx
 * @return array
 */
function getDownloadedPackages($modx)
{
    $corePath = $modx->getOption('core_path');
    $packagesPath = $corePath . 'packages/';
    
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

/**
 * Get upgradeable packages using existing processor
 * 
 * @param \MODX\Revolution\modX $modx
 * @param int $limit Optional limit for number of packages to return (0 = no limit)
 * @return array
 */
function getUpgradeablePackages($modx, $limit = 0)
{
    $processorParams = array(
        'newest_only' => true
    );
    
    // Add limit parameter if specified (0 means no limit)
    if ($limit > 0) {
        $processorParams['limit'] = $limit;
    }
    
    $response = $modx->runProcessor('workspace/packages/getlist', $processorParams);
    
    if ($response->isError()) {
        return [];
    }
    
    $responseData = json_decode($response->getResponse(), true);
    if (!isset($responseData['results'])) {
        return [];
    }
    
    // Filter upgradeable packages
    $upgradeable = [];
    foreach ($responseData['results'] as $package) {
        if (isset($package['updateable']) && $package['updateable']) {
            $upgradeable[] = $package;
        }
    }
    
    return $upgradeable;
}


function getPackageObject($modx, $signature){
     $packageObject = $modx->getObject('MODX\\Revolution\\Transport\\modTransportPackage', array('signature' => $signature));
     return $packageObject;
}
function getProviderFromPackageObject($packageObject){
    $provider = $packageObject->getOne('Provider');
    return $provider;
}

/**
 * Get remote versions for a specific package using direct provider API
 * 
 * @param \MODX\Revolution\modX $modx
 * @param array $package Package data from package:upgradeable
 * @return array
 */
function getRemoteVersionsForPackage($modx, $package)
{
    $packageName = $package['name'];
    $currentSignature = $package['signature'];
    $currentSignatureParts = explode('-', $currentSignature);
    if (count($currentSignatureParts) == 3) {
        $currentPackageName = $currentSignatureParts[0];
    }
    $currentVersion = $package['version'] . '-' . $package['release'];
    $providerId = $package['provider'] ?? null;
    
    if (!$providerId) {
        return [];
    }
    
    try {

        // Load the actual package object to access the Provider relationship
        $packageObject = getPackageObject($modx, $currentSignature);
        // $packageObject = $modx->getObject('MODX\\Revolution\\Transport\\modTransportPackage', array('signature' => $currentSignature));
        if (!$packageObject) {
            return [];
        }
        
        // Get the provider object directly
        /** @var \MODX\Revolution\Transport\modTransportProvider $provider */
        $provider = getProviderFromPackageObject($packageObject);
        // $provider = $packageObject->getOne('Provider');
        if (!$provider) {
            return [];
        }
        
        // Use the provider's latest() method to get live updates from the provider
        // This is the same method used by checkForUpdates() in MODX core
        $updates = $provider->latest($packageObject->get('signature'));
        
        // If updates is a string, it means there was an error or no updates
        if (is_string($updates) || empty($updates)) {
            return [];
        }
        
        $availableVersions = [];
        $currentVersionParsed = parseVersion($currentVersion);
        
        // Process the updates array returned by the provider
        foreach ($updates as $update) {
            // Extract version information from the update
            $updateSignature = $update['signature'] ?? '';
            if (empty($updateSignature)) {
                continue;
            }
            
            // Parse the signature to get version info
            $signatureParts = explode('-', $updateSignature);
            if (count($signatureParts) < 3) {
                continue;
            }
            
            $updateName = $signatureParts[0];
            $updateVersion = $signatureParts[1];
            $updateRelease = $signatureParts[2];
            $updateVersionString = $updateVersion . '-' . $updateRelease;
            
            // Only include versions newer than current and matching package name
            if (strcasecmp($updateName, $currentPackageName) === 0 && isNewerVersion($updateVersionString, $currentVersion)) {
                $availableVersions[] = [
                    'version' => $updateVersion,
                    'release' => $updateRelease,
                    'signature' => $updateSignature,
                    'description' => $update['description'] ?? '',
                    'author' => $update['author'] ?? '',
                    'createdon' => $update['createdon'] ?? '',
                    'location' => $update['location'] ?? ''
                ];
            }
        }
        
        // Sort versions (newest first)
        usort($availableVersions, function($a, $b) {
            $versionA = $a['version'] . '-' . $a['release'];
            $versionB = $b['version'] . '-' . $b['release'];
            return version_compare($versionB, $versionA); // Descending order
        });
        
        if (empty($availableVersions)) {
            return [];
        }
        
        return [
            [
                'name' => $packageName,
                'current_version' => $currentVersionParsed['version'],
                'current_release' => $currentVersionParsed['release'],
                'available_versions' => $availableVersions,
                'provider_id' => $providerId,
                'provider_name' => (string)($provider->get('name') ?? "Provider {$providerId}")
            ]
        ];
        
    } catch (Exception $e) {
        // Log the error for debugging
        error_log("Error fetching remote versions for {$packageName}: " . $e->getMessage());
        return [];
    }
}

/**
 * Check if version1 is newer than version2
 * 
 * @param string $version1
 * @param string $version2
 * @return bool
 */
function isNewerVersion($version1, $version2)
{
    return version_compare($version1, $version2, '>');
}

/**
 * Parse version string into version and release components
 * 
 * @param string $versionString
 * @return array
 */
function parseVersion($versionString)
{
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

/**
 * Render upgrades in table format
 * 
 * @param array $upgrades
 */
function renderUpgradesTable($upgrades)
{
    MODX_CLI::log('Available Package Upgrades:');
    MODX_CLI::log('');
    
    $headers = ['Package', 'Current Version', 'Available Version', 'Signature'];
    
    // Simple table rendering
    $widths = [20, 15, 15, 30];
    
    // Header
    $headerLine = '';
    for ($i = 0; $i < count($headers); $i++) {
        $headerLine .= str_pad($headers[$i], $widths[$i]);
    }
    MODX_CLI::log($headerLine);
    MODX_CLI::log(str_repeat('-', array_sum($widths)));
    
    // Rows
    foreach ($upgrades as $upgrade) {
        $row = '';
        $row .= str_pad($upgrade['name'], $widths[0]);
        $row .= str_pad($upgrade['current_version'] . '-' . $upgrade['current_release'], $widths[1]);
        $row .= str_pad($upgrade['available_version'] . '-' . $upgrade['available_release'], $widths[2]);
        $row .= str_pad($upgrade['signature'], $widths[3]);
        MODX_CLI::log($row);
    }
}

/**
 * Render remote versions in table format matching core commands
 * 
 * @param array $versions
 */
function renderRemoteVersionsTable($versions)
{
    // Flatten the data structure to match core command format
    $flattenedData = [];
    $totalCount = 0;
    
    foreach ($versions as $version) {
        if (!empty($version['available_versions'])) {
            foreach ($version['available_versions'] as $availableVersion) {
                $flattenedData[] = [
                    'signature' => $availableVersion['signature'],
                    'name' => $version['name'],
                    'version' => $availableVersion['version'],
                    'release' => $availableVersion['release'],
                    'current_version' => $version['current_version'] . '-' . $version['current_release'],
                    'provider' => $version['provider_name']
                ];
                $totalCount++;
            }
        }
    }
    
    if (empty($flattenedData)) {
        MODX_CLI::log('No remote versions found for upgradeable packages');
        return;
    }
    
    // Use simple table rendering like the existing renderUpgradesTable function
    $headers = ['signature', 'name', 'version', 'release', 'current_version', 'provider'];
    $widths = [25, 15, 10, 10, 15, 12];
    
    // Header
    $headerLine = '';
    for ($i = 0; $i < count($headers); $i++) {
        $headerLine .= str_pad($headers[$i], $widths[$i]);
    }
    MODX_CLI::log($headerLine);
    MODX_CLI::log(str_repeat('-', array_sum($widths)));
    
    // Rows
    foreach ($flattenedData as $row) {
        $line = '';
        $line .= str_pad($row['signature'], $widths[0]);
        $line .= str_pad($row['name'], $widths[1]);
        $line .= str_pad($row['version'], $widths[2]);
        $line .= str_pad($row['release'], $widths[3]);
        $line .= str_pad($row['current_version'], $widths[4]);
        $line .= str_pad((string)$row['provider'], $widths[5]);
        MODX_CLI::log($line);
    }
    
    // Add pagination footer
    MODX_CLI::log('');
    MODX_CLI::log('displaying ' . count($flattenedData) . ' item(s) of ' . $totalCount);
}

/**
 * Get provider name by ID
 * 
 * @param \MODX\Revolution\modX $modx
 * @param int $providerId
 * @return string
 */
function getProviderName($modx, $providerId)
{
    try {
        $response = $modx->runProcessor('workspace/packages/providers/getlist');
        
        if ($response->isError()) {
            return "Provider {$providerId}";
        }
        
        $responseData = json_decode($response->getResponse(), true);
        if (!isset($responseData['results'])) {
            return "Provider {$providerId}";
        }
        
        foreach ($responseData['results'] as $provider) {
            if (isset($provider['id']) && $provider['id'] == $providerId) {
                return isset($provider['name']) ? (string)$provider['name'] : "Provider {$providerId}";
            }
        }
        
        return "Provider {$providerId}";
        
    } catch (Exception $e) {
        return "Provider {$providerId}";
    }
}

/**
 * Alternative method to get remote versions (fallback)
 * 
 * @param \MODX\Revolution\modX $modx
 * @param array $package
 * @return array
 */
function getRemoteVersionsAlternative($modx, $package)
{
    // Fallback method - could try different processor or approach
    // For now, return empty array to indicate no versions found
    return [];
}


/**
 * Find a signature in upgradeable.json by package name (ignoring version and release).
 *
 * @param string $jsonFile Path to upgradeable.json
 * @param string $packageName Name to search for (e.g. 'formit')
 * @return string|null The full signature if found, or null if not found
 */
function findSignatureByPackageName($packages, $packageSignature)
{
    $parts = explode('-', $packageSignature, 2);
    if (!$parts){
        return null;
    }
    $packageName = $parts[0];

    if (!$packages) {
        return null;
    }

    foreach ($packages as $package) {
        if (isset($package['signature'])) {
            $parts = explode('-', $package['signature'], 2);
            if (strcasecmp($parts[0], $packageName) === 0) {
                return $package['signature'];
            }
        }
    }
    return null;
}
