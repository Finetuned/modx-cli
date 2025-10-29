<?php

namespace MODX\CLI\Tests\Mock;

use MODX\CLI\Mock\PackageManager;
use PHPUnit\Framework\TestCase;

class PackageManagerTest extends TestCase
{
    protected $packageManager;

    protected function setUp(): void
    {
        $this->packageManager = new PackageManager();
    }

    public function testListUpgradeablePackagesReturnsEmptyArray()
    {
        // Capture output
        ob_start();
        $result = $this->packageManager->listUpgradeablePackages();
        $output = ob_get_clean();

        $this->assertIsArray($result);
        $this->assertEmpty($result);
        $this->assertStringContainsString('Mock: Listing upgradeable packages.', $output);
    }

    public function testListUpgradeablePackagesProducesOutput()
    {
        ob_start();
        $this->packageManager->listUpgradeablePackages();
        $output = ob_get_clean();

        $this->assertNotEmpty($output);
        $this->assertStringContainsString('upgradeable packages', $output);
    }

    public function testAddProviderWithValidParameters()
    {
        ob_start();
        $this->packageManager->addProvider('test-provider', 'https://example.com/provider');
        $output = ob_get_clean();

        $this->assertStringContainsString('Mock: Adding package provider', $output);
        $this->assertStringContainsString('test-provider', $output);
        $this->assertStringContainsString('https://example.com/provider', $output);
    }

    public function testAddProviderWithEmptyName()
    {
        ob_start();
        $this->packageManager->addProvider('', 'https://example.com/provider');
        $output = ob_get_clean();

        $this->assertStringContainsString('Mock: Adding package provider', $output);
        $this->assertStringContainsString("''", $output);
    }

    public function testAddProviderWithEmptyUrl()
    {
        ob_start();
        $this->packageManager->addProvider('test-provider', '');
        $output = ob_get_clean();

        $this->assertStringContainsString('Mock: Adding package provider', $output);
        $this->assertStringContainsString('test-provider', $output);
    }

    public function testAddProviderWithSpecialCharacters()
    {
        ob_start();
        $this->packageManager->addProvider('provider-with-dashes', 'https://example.com/path?query=value&key=123');
        $output = ob_get_clean();

        $this->assertStringContainsString('provider-with-dashes', $output);
        $this->assertStringContainsString('https://example.com/path?query=value&key=123', $output);
    }

    public function testAddProviderDoesNotReturnValue()
    {
        ob_start();
        $result = $this->packageManager->addProvider('test', 'https://example.com');
        ob_end_clean();

        $this->assertNull($result);
    }

    public function testMultipleListUpgradeablePackagesCalls()
    {
        ob_start();
        $result1 = $this->packageManager->listUpgradeablePackages();
        $result2 = $this->packageManager->listUpgradeablePackages();
        $output = ob_get_clean();

        $this->assertIsArray($result1);
        $this->assertIsArray($result2);
        $this->assertEmpty($result1);
        $this->assertEmpty($result2);

        // Should have output twice
        $this->assertEquals(2, substr_count($output, 'Mock: Listing upgradeable packages.'));
    }

    public function testMultipleAddProviderCalls()
    {
        ob_start();
        $this->packageManager->addProvider('provider1', 'https://example.com/1');
        $this->packageManager->addProvider('provider2', 'https://example.com/2');
        $output = ob_get_clean();

        $this->assertStringContainsString('provider1', $output);
        $this->assertStringContainsString('provider2', $output);
        $this->assertStringContainsString('https://example.com/1', $output);
        $this->assertStringContainsString('https://example.com/2', $output);
    }

    public function testPackageManagerCanBeInstantiated()
    {
        $manager = new PackageManager();

        $this->assertInstanceOf(PackageManager::class, $manager);
    }

    public function testListUpgradeablePackagesConsistentBehavior()
    {
        ob_start();
        $result1 = $this->packageManager->listUpgradeablePackages();
        ob_end_clean();

        ob_start();
        $result2 = $this->packageManager->listUpgradeablePackages();
        ob_end_clean();

        // Both calls should return the same structure
        $this->assertEquals($result1, $result2);
    }

    public function testAddProviderWithLongUrl()
    {
        $longUrl = 'https://example.com/' . str_repeat('path/', 50) . 'endpoint';

        ob_start();
        $this->packageManager->addProvider('test', $longUrl);
        $output = ob_get_clean();

        $this->assertStringContainsString('test', $output);
        $this->assertStringContainsString($longUrl, $output);
    }

    public function testAddProviderWithNumericName()
    {
        ob_start();
        $this->packageManager->addProvider('12345', 'https://example.com');
        $output = ob_get_clean();

        $this->assertStringContainsString('12345', $output);
    }

    public function testOutputFormatIsConsistent()
    {
        ob_start();
        $this->packageManager->listUpgradeablePackages();
        $output1 = ob_get_clean();

        ob_start();
        $this->packageManager->listUpgradeablePackages();
        $output2 = ob_get_clean();

        $this->assertEquals($output1, $output2);
    }
}
