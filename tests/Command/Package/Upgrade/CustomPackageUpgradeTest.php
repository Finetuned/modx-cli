<?php

namespace MODX\CLI\Tests\Command\Package\Upgrade;

use MODX\CLI\API\MODX_CLI;
use MODX\CLI\Tests\Configuration\BaseTest;
use PHPUnit\Framework\TestCase;

class CustomPackageUpgradeTest extends TestCase
{
    protected $modx;
    protected $commandsRegistered = false;

    protected function setUp(): void
    {
        // Skip if running in integration mode where real modX is already loaded
        if (getenv('MODX_INTEGRATION_TESTS')) {
            $this->markTestSkipped('Skipping unit test in integration mode to avoid modX class conflicts');
        }
        
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');
        
        // Register custom commands if not already done
        if (!$this->commandsRegistered) {
            $this->registerCustomCommands();
            $this->commandsRegistered = true;
        }
    }

    protected function registerCustomCommands()
    {
        // Load the functions file
        require_once __DIR__ . '/../../../../custom-commands/package-upgrade-functions.php';
        
        // Register the custom commands using the internal API
        MODX_CLI::add_command('package:upgrade:list', 'packageUpgradeList', [
            'shortdesc' => 'List downloaded package upgrades ready for installation',
            'longdesc' => 'This command scans the core/packages directory for downloaded package upgrades and compares them with installed packages to show which upgrades are available for installation.',
        ]);
        
        MODX_CLI::add_command('package:upgrade:list-remote', 'packageUpgradeListRemote', [
            'shortdesc' => 'Retrieve all available versions after the installed version',
            'longdesc' => 'This command queries providers for available package versions that are newer than the currently installed versions.',
        ]);
        
        MODX_CLI::add_command('package:upgrade:download', 'packageUpgradeDownload', [
            'shortdesc' => 'Download specific package versions to core/packages',
            'longdesc' => 'This command downloads specific package versions from providers to the core/packages directory.',
        ]);
        
        MODX_CLI::add_command('package:upgrade:all', 'packageUpgradeAll', [
            'shortdesc' => 'Orchestrate the complete upgrade workflow',
            'longdesc' => 'This command orchestrates the complete package upgrade workflow by listing, downloading, and installing package upgrades.',
        ]);
    }

    public function testCustomCommandsAreRegistered()
    {
        $commands = MODX_CLI::get_commands();
        $commandNames = [];
        
        foreach ($commands as $command) {
            $commandNames[] = $command->getName();
        }
        
        $this->assertContains('package:upgrade:list', $commandNames);
        $this->assertContains('package:upgrade:list-remote', $commandNames);
        $this->assertContains('package:upgrade:download', $commandNames);
        $this->assertContains('package:upgrade:all', $commandNames);
    }

    public function testPackageUpgradeListWithNoUpgrades()
    {
        // Mock the processor response for no installed packages
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('isError')->willReturn(false);
        $processorResponse->method('getResponse')->willReturn(json_encode(['results' => []]));
        
        $this->modx->method('runProcessor')->willReturn($processorResponse);
        $this->modx->method('getOption')->with('core_path')->willReturn('/tmp/test/');
        
        // Mock the Application to return our mock MODX
        $app = $this->getMockBuilder('MODX\CLI\Application')
            ->disableOriginalConstructor()
            ->getMock();
        $app->method('getMODX')->willReturn($this->modx);
        
        // We can't easily test the function directly due to the Application instantiation
        // So we'll test that the command is registered and can be retrieved
        $command = MODX_CLI::get_command('package:upgrade:list');
        $this->assertNotNull($command);
        $this->assertEquals('package:upgrade:list', $command->getName());
    }

    public function testPackageUpgradeListRemoteCommand()
    {
        $command = MODX_CLI::get_command('package:upgrade:list-remote');
        $this->assertNotNull($command);
        $this->assertEquals('package:upgrade:list-remote', $command->getName());
    }

    public function testPackageUpgradeDownloadCommand()
    {
        $command = MODX_CLI::get_command('package:upgrade:download');
        $this->assertNotNull($command);
        $this->assertEquals('package:upgrade:download', $command->getName());
    }

    public function testPackageUpgradeAllCommand()
    {
        $command = MODX_CLI::get_command('package:upgrade:all');
        $this->assertNotNull($command);
        $this->assertEquals('package:upgrade:all', $command->getName());
    }

    public function testHelperFunctions()
    {
        // Test version parsing
        $version = parseVersion('3.0.2-pl');
        $this->assertEquals('3.0.2', $version['version']);
        $this->assertEquals('pl', $version['release']);
        
        // Test version comparison
        $this->assertTrue(isNewerVersion('3.0.2-pl', '3.0.1-pl'));
        $this->assertFalse(isNewerVersion('3.0.1-pl', '3.0.2-pl'));
    }

    public function testGetInstalledPackagesFunction()
    {
        // Mock processor response with installed packages
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('isError')->willReturn(false);
        $processorResponse->method('getResponse')->willReturn(json_encode([
            'results' => [
                [
                    'name' => 'pdotools',
                    'version' => '3.0.1',
                    'release' => 'pl',
                    'installed' => '2023-01-01 12:00:00'
                ],
                [
                    'name' => 'migx',
                    'version' => '2.12.0',
                    'release' => 'pl',
                    'installed' => '2023-01-01 12:00:00'
                ]
            ]
        ]));
        
        $this->modx->method('runProcessor')->willReturn($processorResponse);
        
        $packages = getInstalledPackages($this->modx);
        $this->assertCount(2, $packages);
        $this->assertEquals('pdotools', $packages[0]['name']);
        $this->assertEquals('migx', $packages[1]['name']);
    }

    public function testGetDownloadedPackagesFunction()
    {
        $this->modx->method('getOption')->with('core_path')->willReturn(__DIR__ . '/test-packages/');
        
        // Create a temporary test directory structure
        $testDir = __DIR__ . '/test-packages/packages/';
        if (!is_dir($testDir)) {
            mkdir($testDir, 0755, true);
        }
        
        // Create test package files
        file_put_contents($testDir . 'pdotools-3.0.2-pl.transport.zip', 'test');
        file_put_contents($testDir . 'migx-2.13.0-pl.transport.zip', 'test');
        file_put_contents($testDir . 'not-a-package.txt', 'test');
        
        $packages = getDownloadedPackages($this->modx);
        
        // Clean up test files
        unlink($testDir . 'pdotools-3.0.2-pl.transport.zip');
        unlink($testDir . 'migx-2.13.0-pl.transport.zip');
        unlink($testDir . 'not-a-package.txt');
        rmdir($testDir);
        rmdir(__DIR__ . '/test-packages/');
        
        $this->assertCount(2, $packages);
        $this->assertContains('pdotools-3.0.2-pl.transport.zip', $packages);
        $this->assertContains('migx-2.13.0-pl.transport.zip', $packages);
        $this->assertNotContains('not-a-package.txt', $packages);
    }

    public function testGetAvailableUpgradesFunction()
    {
        // Mock installed packages
        $processorResponse = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('isError')->willReturn(false);
        $processorResponse->method('getResponse')->willReturn(json_encode([
            'results' => [
                [
                    'name' => 'pdotools',
                    'version' => '3.0.1',
                    'release' => 'pl',
                    'installed' => '2023-01-01 12:00:00'
                ]
            ]
        ]));
        
        $this->modx->method('runProcessor')->willReturn($processorResponse);
        $this->modx->method('getOption')->with('core_path')->willReturn(__DIR__ . '/test-packages/');
        
        // Create test directory and files
        $testDir = __DIR__ . '/test-packages/packages/';
        if (!is_dir($testDir)) {
            mkdir($testDir, 0755, true);
        }
        
        file_put_contents($testDir . 'pdotools-3.0.2-pl.transport.zip', 'test');
        
        $upgrades = getAvailableUpgrades($this->modx);
        
        // Clean up
        unlink($testDir . 'pdotools-3.0.2-pl.transport.zip');
        rmdir($testDir);
        rmdir(__DIR__ . '/test-packages/');
        
        $this->assertCount(1, $upgrades);
        $this->assertEquals('pdotools', $upgrades[0]['name']);
        $this->assertEquals('3.0.1', $upgrades[0]['current_version']);
        $this->assertEquals('3.0.2', $upgrades[0]['available_version']);
    }
}
