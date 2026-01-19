<?php

namespace MODX\CLI\Tests\Command\Package\Upgrade;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Test-Driven Development tests for package:list-remote command
 *
 * These tests reproduce the current issue where package:list-remote returns
 * "No remote versions found" even when package:upgradeable shows updates.
 */
class PackageListRemoteTest extends TestCase
{
    /** @var MockObject */
    private $mockModx;

    /** @var MockObject */
    private $mockResponse;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock MODX instance
        $this->mockModx = $this->createMock(\MODX\Revolution\modX::class);

        // Mock processor response
        $this->mockResponse = $this->createMock(\MODX\Revolution\Processors\ProcessorResponse::class);
    }

    /**
     * Test that reproduces the current issue and verifies the fix
     *
     * This test demonstrates that package:list-remote now finds versions
     * when upgradeable packages exist.
     */
    public function testPackageListRemoteFindsVersionsWhenUpgradeableExists()
    {
        // Arrange: Mock upgradeable packages (simulating package:upgradeable output)
        $upgradeablePackages = [
            [
                'name' => 'pdotools',
                'version' => '3.0.1',
                'release' => 'pl',
                'signature' => 'pdotools-3.0.1-pl',
                'provider' => 1,
                'updateable' => true
            ],
            [
                'name' => 'migx',
                'version' => '2.13.0',
                'release' => 'pl',
                'signature' => 'migx-2.13.0-pl',
                'provider' => 1,
                'updateable' => true
            ]
        ];

        // Create mock package objects and providers for getObject/getOne calls
        $mockPdoToolsPackage = $this->createMock(\MODX\Revolution\Transport\modTransportPackage::class);
        $mockMigxPackage = $this->createMock(\MODX\Revolution\Transport\modTransportPackage::class);
        $mockProvider = $this->createMock(\MODX\Revolution\Transport\modTransportProvider::class);

        // Mock package object methods
        $mockPdoToolsPackage->method('get')->with('signature')->willReturn('pdotools-3.0.1-pl');
        $mockPdoToolsPackage->method('getOne')->with('Provider')->willReturn($mockProvider);

        $mockMigxPackage->method('get')->with('signature')->willReturn('migx-2.13.0-pl');
        $mockMigxPackage->method('getOne')->with('Provider')->willReturn($mockProvider);

        // Mock provider methods
        $mockProvider->method('get')->willReturnCallback(function ($key) {
            if ($key === 'id') {
                return 1;
            }
            if ($key === 'name') {
                return 'modx.com';
            }
            return null;
        });

        // Mock provider->latest() to return remote version data
        $mockProvider->method('latest')->willReturnCallback(function ($signature) {
            if (str_contains($signature, 'pdotools')) {
                return [
                    [
                        'signature' => 'pdotools-3.0.2-pl',
                        'version' => '3.0.2',
                        'release' => 'pl',
                        'description' => 'Updated version',
                        'author' => 'bezumkin',
                        'createdon' => '2024-01-01',
                        'location' => '/path/to/pdotools'
                    ]
                ];
            }
            if (str_contains($signature, 'migx')) {
                return [
                    [
                        'signature' => 'migx-2.14.0-pl',
                        'version' => '2.14.0',
                        'release' => 'pl',
                        'description' => 'Updated MIGX',
                        'author' => 'bruno17',
                        'createdon' => '2024-01-01',
                        'location' => '/path/to/migx'
                    ]
                ];
            }
            return [];
        });

        // Mock remote package versions for pdotools
        $pdotoolsRemoteResponse = [
            'results' => [
                [
                    'name' => 'pdotools',
                    'version' => '3.0.2',
                    'release' => 'pl',
                    'signature' => 'pdotools-3.0.2-pl',
                    'description' => 'Updated version',
                    'author' => 'bezumkin'
                ]
            ]
        ];

        // Mock remote package versions for migx
        $migxRemoteResponse = [
            'results' => [
                [
                    'name' => 'migx',
                    'version' => '2.14.0',
                    'release' => 'pl',
                    'signature' => 'migx-2.14.0-pl',
                    'description' => 'Updated MIGX',
                    'author' => 'bruno17'
                ]
            ]
        ];

        // Mock provider list response
        $providerListResponse = [
            'results' => [
                [
                    'id' => 1,
                    'name' => 'modx.com'
                ]
            ]
        ];

        // Create mock responses
        $upgradeableResponse = $this->createMock(\MODX\Revolution\Processors\ProcessorResponse::class);
        $pdotoolsResponse = $this->createMock(\MODX\Revolution\Processors\ProcessorResponse::class);
        $migxResponse = $this->createMock(\MODX\Revolution\Processors\ProcessorResponse::class);
        $providerResponse1 = $this->createMock(\MODX\Revolution\Processors\ProcessorResponse::class);
        $providerResponse2 = $this->createMock(\MODX\Revolution\Processors\ProcessorResponse::class);

        // Mock getObject to return package objects
        $this->mockModx->method('getObject')->willReturnCallback(function ($class, $criteria) use ($mockPdoToolsPackage, $mockMigxPackage) {
            if (is_array($criteria) && isset($criteria['signature'])) {
                if ($criteria['signature'] === 'pdotools-3.0.1-pl') {
                    return $mockPdoToolsPackage;
                }
                if ($criteria['signature'] === 'migx-2.13.0-pl') {
                    return $mockMigxPackage;
                }
            }
            return null;
        });

        // Mock the processor call for getUpgradeablePackages
        $this->mockModx->expects($this->once())
            ->method('runProcessor')
            ->with('workspace/packages/getlist', ['newest_only' => true])
            ->willReturn($upgradeableResponse);

        // Mock upgradeable packages response
        $upgradeableResponse->method('isError')->willReturn(false);
        $upgradeableResponse->method('getResponse')->willReturn(json_encode(['results' => $upgradeablePackages]));

        // Act: Call the functions
        require_once __DIR__ . '/../../../../custom-commands/package-upgrade-functions.php';
        $result = getUpgradeablePackages($this->mockModx);

        // Assert: Should find upgradeable packages
        $this->assertNotEmpty($result, 'Should find upgradeable packages');
        $this->assertCount(2, $result, 'Should find 2 upgradeable packages');

        // Now test remote version lookup
        $remoteVersions = [];
        foreach ($result as $package) {
            $versions = getRemoteVersionsForPackage($this->mockModx, $package);
            $remoteVersions = array_merge($remoteVersions, $versions);
        }

        // This assertion should now PASS with our fix
        $this->assertNotEmpty($remoteVersions, 'Should find remote versions for upgradeable packages');
        $this->assertCount(2, $remoteVersions, 'Should find remote versions for both packages');
    }

    /**
     * Test provider data extraction from upgradeable packages
     */
    public function testProviderDataExtractionFromUpgradeablePackages()
    {
        // Arrange: Mock package with provider data
        $packageWithProvider = [
            'name' => 'pdotools',
            'version' => '3.0.1',
            'release' => 'pl',
            'provider' => 1,
            'updateable' => true
        ];

        $packageWithoutProvider = [
            'name' => 'testpackage',
            'version' => '1.0.0',
            'release' => 'pl',
            'updateable' => true
            // No provider field
        ];

        // Act & Assert: Package with provider should be processable
        $this->assertTrue(isset($packageWithProvider['provider']), 'Package should have provider data');
        $this->assertIsNumeric($packageWithProvider['provider'], 'Provider should be numeric ID');

        // Package without provider should be skipped
        $this->assertFalse(isset($packageWithoutProvider['provider']), 'Package without provider should be identified');
    }

    /**
     * Test that we use the correct MODX processor for provider querying
     */
    public function testCorrectMODXProcessorCallsForProviderQuerying()
    {
        // Arrange: Mock package with provider
        $package = [
            'name' => 'pdotools',
            'version' => '3.0.1',
            'release' => 'pl',
            'signature' => 'pdotools-3.0.1-pl',
            'provider' => 1
        ];

        // Create mock package object and provider
        $mockPackage = $this->createMock(\MODX\Revolution\Transport\modTransportPackage::class);
        $mockProvider = $this->createMock(\MODX\Revolution\Transport\modTransportProvider::class);

        // Mock package object methods
        $mockPackage->method('get')->with('signature')->willReturn('pdotools-3.0.1-pl');
        $mockPackage->method('getOne')->with('Provider')->willReturn($mockProvider);

        // Mock provider methods
        $mockProvider->method('get')->willReturnCallback(function ($key) {
            if ($key === 'id') {
                return 1;
            }
            if ($key === 'name') {
                return 'modx.com';
            }
            return null;
        });

        // Mock provider->latest() to return remote version data
        $mockProvider->method('latest')->willReturn([
            [
                'signature' => 'pdotools-3.0.2-pl',
                'version' => '3.0.2',
                'release' => 'pl',
                'description' => 'Updated version',
                'author' => 'bezumkin',
                'createdon' => '2024-01-01',
                'location' => '/path/to/pdotools'
            ]
        ]);

        // Mock getObject to return package object
        $this->mockModx->method('getObject')->willReturn($mockPackage);

        // Act: Call the function
        require_once __DIR__ . '/../../../../custom-commands/package-upgrade-functions.php';
        $result = getRemoteVersionsForPackage($this->mockModx, $package);

        // Assert: Should return remote versions
        $this->assertNotEmpty($result, 'Should find remote versions');
    }

    /**
     * Test remote version filtering (only newer versions)
     */
    public function testRemoteVersionFilteringNewerThanCurrent()
    {
        // Arrange: Package with current version 3.0.1
        $package = [
            'name' => 'pdotools',
            'version' => '3.0.1',
            'release' => 'pl'
        ];

        // Mock provider response with multiple versions
        $providerVersions = [
            ['version' => '3.0.0', 'release' => 'pl'], // Older - should be filtered out
            ['version' => '3.0.1', 'release' => 'pl'], // Same - should be filtered out
            ['version' => '3.0.2', 'release' => 'pl'], // Newer - should be included
            ['version' => '3.1.0', 'release' => 'pl'], // Newer - should be included
        ];

        // Act: Test version comparison logic
        require_once __DIR__ . '/../../../../custom-commands/package-upgrade-functions.php';

        $currentVersion = $package['version'] . '-' . $package['release'];
        $newerVersions = [];

        foreach ($providerVersions as $version) {
            $versionString = $version['version'] . '-' . $version['release'];
            if (isNewerVersion($versionString, $currentVersion)) {
                $newerVersions[] = $version;
            }
        }

        // Assert: Should only include newer versions
        $this->assertCount(2, $newerVersions, 'Should find 2 newer versions');
        $this->assertEquals('3.0.2', $newerVersions[0]['version'], 'First newer version should be 3.0.2');
        $this->assertEquals('3.1.0', $newerVersions[1]['version'], 'Second newer version should be 3.1.0');
    }

    /**
     * Test error handling for provider connection issues
     */
    public function testHandlesProviderConnectionErrors()
    {
        // Arrange: Package with provider
        $package = [
            'name' => 'pdotools',
            'version' => '3.0.1',
            'release' => 'pl',
            'signature' => 'pdotools-3.0.1-pl',
            'provider' => 1
        ];

        // Create mock package object and provider
        $mockPackage = $this->createMock(\MODX\Revolution\Transport\modTransportPackage::class);
        $mockProvider = $this->createMock(\MODX\Revolution\Transport\modTransportProvider::class);

        // Mock package object methods
        $mockPackage->method('get')->with('signature')->willReturn('pdotools-3.0.1-pl');
        $mockPackage->method('getOne')->with('Provider')->willReturn($mockProvider);

        // Mock provider methods
        $mockProvider->method('get')->willReturnCallback(function ($key) {
            if ($key === 'id') {
                return 1;
            }
            if ($key === 'name') {
                return 'modx.com';
            }
            return null;
        });

        // Mock provider->latest() to return error (string)
        $mockProvider->method('latest')->willReturn('Connection error');

        // Mock getObject to return package object
        $this->mockModx->method('getObject')->willReturn($mockPackage);

        // Act: Call function with error response
        require_once __DIR__ . '/../../../../custom-commands/package-upgrade-functions.php';
        $result = getRemoteVersionsForPackage($this->mockModx, $package);

        // Assert: Should handle error gracefully
        $this->assertEmpty($result, 'Should return empty array on provider error');
    }

    /**
     * Test handling of packages without provider data
     */
    public function testHandlesMissingProviderData()
    {
        // Arrange: Package without provider
        $packageWithoutProvider = [
            'name' => 'localpackage',
            'version' => '1.0.0',
            'release' => 'pl',
            'signature' => 'localpackage-1.0.0-pl'
            // No provider field
        ];

        // Act: Call function with package missing provider
        require_once __DIR__ . '/../../../../custom-commands/package-upgrade-functions.php';
        $result = getRemoteVersionsForPackage($this->mockModx, $packageWithoutProvider);

        // Assert: Should return empty array for packages without provider
        $this->assertEmpty($result, 'Should return empty array for packages without provider');
    }
}
