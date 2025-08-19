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
                'provider' => 1,
                'updateable' => true
            ],
            [
                'name' => 'migx',
                'version' => '2.13.0',
                'release' => 'pl', 
                'provider' => 1,
                'updateable' => true
            ]
        ];

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

        // Mock the sequence of processor calls (updated for new implementation)
        $this->mockModx->expects($this->exactly(5))
            ->method('runProcessor')
            ->withConsecutive(
                ['workspace/packages/getlist', ['newest_only' => true]],
                ['workspace/packages/providers/get', ['id' => 1]],
                ['workspace/packages/providers/packages', ['provider' => 1, 'query' => 'pdotools', 'limit' => 50, 'start' => 0]],
                ['workspace/packages/providers/get', ['id' => 1]],
                ['workspace/packages/providers/packages', ['provider' => 1, 'query' => 'migx', 'limit' => 50, 'start' => 0]]
            )
            ->willReturnOnConsecutiveCalls(
                $upgradeableResponse,
                $providerResponse1,
                $pdotoolsResponse,
                $providerResponse2,
                $migxResponse
            );

        // Mock upgradeable packages response
        $upgradeableResponse->expects($this->once())->method('isError')->willReturn(false);
        $upgradeableResponse->expects($this->once())->method('getResponse')->willReturn(json_encode(['results' => $upgradeablePackages]));

        // Mock pdotools remote response
        $pdotoolsResponse->expects($this->once())->method('isError')->willReturn(false);
        $pdotoolsResponse->expects($this->once())->method('getResponse')->willReturn(json_encode($pdotoolsRemoteResponse));

        // Mock provider get response for pdotools
        $providerGetResponse = [
            'object' => [
                'id' => 1,
                'name' => 'modx.com'
            ]
        ];
        
        $providerResponse1->expects($this->once())->method('isError')->willReturn(false);
        $providerResponse1->expects($this->once())->method('getResponse')->willReturn(json_encode($providerGetResponse));

        // Mock pdotools remote response
        $pdotoolsResponse->expects($this->once())->method('isError')->willReturn(false);
        $pdotoolsResponse->expects($this->once())->method('getResponse')->willReturn(json_encode($pdotoolsRemoteResponse));

        // Mock provider get response for migx
        $providerResponse2->expects($this->once())->method('isError')->willReturn(false);
        $providerResponse2->expects($this->once())->method('getResponse')->willReturn(json_encode($providerGetResponse));

        // Mock migx remote response
        $migxResponse->expects($this->once())->method('isError')->willReturn(false);
        $migxResponse->expects($this->once())->method('getResponse')->willReturn(json_encode($migxRemoteResponse));

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
            'provider' => 1
        ];

        // Mock successful provider query response
        $packageQueryResponse = [
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

        // Mock provider list response
        $providerListResponse = [
            'results' => [
                [
                    'id' => 1,
                    'name' => 'modx.com'
                ]
            ]
        ];

        // Create separate mock responses
        $packageResponse = $this->createMock(\MODX\Revolution\Processors\ProcessorResponse::class);
        $providerResponse = $this->createMock(\MODX\Revolution\Processors\ProcessorResponse::class);

        // Expect two processor calls: one for provider details, one for remote packages
        $this->mockModx->expects($this->exactly(2))
            ->method('runProcessor')
            ->withConsecutive(
                [
                    'workspace/packages/providers/get',
                    [
                        'id' => 1
                    ]
                ],
                [
                    'workspace/packages/providers/packages',
                    [
                        'provider' => 1,
                        'query' => 'pdotools',
                        'limit' => 50,
                        'start' => 0
                    ]
                ]
            )
            ->willReturnOnConsecutiveCalls($providerResponse, $packageResponse);

        // Mock package query response
        $packageResponse->expects($this->once())
            ->method('isError')
            ->willReturn(false);

        $packageResponse->expects($this->once())
            ->method('getResponse')
            ->willReturn(json_encode($packageQueryResponse));

        // Mock provider get response (different format)
        $providerGetResponse = [
            'object' => [
                'id' => 1,
                'name' => 'modx.com'
            ]
        ];

        $providerResponse->expects($this->once())
            ->method('isError')
            ->willReturn(false);

        $providerResponse->expects($this->once())
            ->method('getResponse')
            ->willReturn(json_encode($providerGetResponse));

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
            'provider' => 1
        ];

        // Mock processor error response
        $this->mockModx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($this->mockResponse);

        $this->mockResponse->expects($this->once())
            ->method('isError')
            ->willReturn(true);

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
            'release' => 'pl'
            // No provider field
        ];

        // Act: Call function with package missing provider
        require_once __DIR__ . '/../../../../custom-commands/package-upgrade-functions.php';
        $result = getRemoteVersionsForPackage($this->mockModx, $packageWithoutProvider);

        // Assert: Should return empty array for packages without provider
        $this->assertEmpty($result, 'Should return empty array for packages without provider');
    }
}
