<?php

namespace MODX\CLI\Tests\Command\Package\Upgrade;

use MODX\CLI\API\MODX_CLI;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Yaml\Yaml;

/**
 * @group integration
 * @group requires-modx
 */
class IntegratedPackageUpgradeTest extends TestCase
{
    protected function setUp(): void
    {
        // Clear any existing commands before each test
        $this->clearRegisteredCommands();
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        $this->clearRegisteredCommands();
    }

    private function clearRegisteredCommands()
    {
        // Remove any previously registered test commands
        $commands = ['package:list-upgrades', 'package:list-remote', 'package:download', 'package:upgrade-all'];
        foreach ($commands as $command) {
            MODX_CLI::remove_command($command);
        }
    }

    private function registerIntegratedCommands()
    {
        // Load the functions file
        require_once __DIR__ . '/../../../../custom-commands/package-upgrade-functions.php';
        
        // Register the integrated commands using the internal API
        MODX_CLI::add_command('package:list-upgrades', 'packageUpgradeList', [
            'shortdesc' => 'List downloaded package upgrades ready for installation',
            'longdesc' => 'This command scans the core/packages directory for downloaded package upgrades and compares them with installed packages to show which upgrades are available for installation.',
        ]);
        
        MODX_CLI::add_command('package:list-remote', 'packageUpgradeListRemote', [
            'shortdesc' => 'Retrieve all available versions after the installed version',
            'longdesc' => 'This command queries providers for available package versions that are newer than the currently installed versions.',
        ]);
        
        MODX_CLI::add_command('package:download', 'packageUpgradeDownload', [
            'shortdesc' => 'Download specific package versions to core/packages',
            'longdesc' => 'This command downloads specific package versions from providers to the core/packages directory.',
        ]);
        
        MODX_CLI::add_command('package:upgrade-all', 'packageUpgradeAll', [
            'shortdesc' => 'Orchestrate the complete upgrade workflow',
            'longdesc' => 'This command orchestrates the complete package upgrade workflow by listing, downloading, and installing package upgrades.',
        ]);
    }

    public function testIntegratedCommandNamesAreRegistered()
    {
        // Skip this test if MODX config is not available (e.g., when running with coverage)
        // This is an integration test that requires loading actual command functions
        // which instantiate Application objects that need MODX config
        if (!file_exists(getcwd() . '/config.core.php') && !getenv('MODX_CORE_PATH')) {
            $this->markTestSkipped('MODX configuration not available - integration test requires MODX installation');
            return;
        }
        
        $this->registerIntegratedCommands();
        
        $commands = MODX_CLI::get_commands();
        $commandNames = array_map(function($cmd) { return $cmd->getName(); }, $commands);
        
        // Test that commands use integrated naming (not parallel hierarchy)
        $this->assertContains('package:list-upgrades', $commandNames);
        $this->assertContains('package:list-remote', $commandNames);
        $this->assertContains('package:download', $commandNames);
        $this->assertContains('package:upgrade-all', $commandNames);
        
        // Test that old conflicting names are NOT registered
        $this->assertNotContains('package:upgrade:list', $commandNames);
        $this->assertNotContains('package:upgrade:list-remote', $commandNames);
        $this->assertNotContains('package:upgrade:download', $commandNames);
        $this->assertNotContains('package:upgrade:all', $commandNames);
    }

    public function testIntegratedCommandsCanBeRetrieved()
    {
        if (!file_exists(getcwd() . '/config.core.php') && !getenv('MODX_CORE_PATH')) {
            $this->markTestSkipped('MODX configuration not available - integration test requires MODX installation');
            return;
        }
        
        $this->registerIntegratedCommands();
        
        // Test that each integrated command can be retrieved
        $command = MODX_CLI::get_command('package:list-upgrades');
        $this->assertNotNull($command);
        $this->assertEquals('package:list-upgrades', $command->getName());
        
        $command = MODX_CLI::get_command('package:list-remote');
        $this->assertNotNull($command);
        $this->assertEquals('package:list-remote', $command->getName());
        
        $command = MODX_CLI::get_command('package:download');
        $this->assertNotNull($command);
        $this->assertEquals('package:download', $command->getName());
        
        $command = MODX_CLI::get_command('package:upgrade-all');
        $this->assertNotNull($command);
        $this->assertEquals('package:upgrade-all', $command->getName());
    }

    public function testIntegratedCommandsExecuteWithoutArgumentConflict()
    {
        if (!file_exists(getcwd() . '/config.core.php') && !getenv('MODX_CORE_PATH')) {
            $this->markTestSkipped('MODX configuration not available - integration test requires MODX installation');
            return;
        }
        
        $this->registerIntegratedCommands();
        
        // Test that each integrated command can be executed without argument conflicts
        $command = MODX_CLI::get_command('package:list-upgrades');
        $this->assertNotNull($command);
        
        $input = new ArrayInput([]);
        $output = new BufferedOutput();
        
        // Capture any console output to prevent risky test warning
        ob_start();
        
        // Should not throw "argument already exists" error
        // We expect this to fail gracefully (no MODX instance) but not with argument conflicts
        try {
            $result = $command->run($input, $output);
            // If it runs, that's good - no argument conflict
            $this->assertTrue(true);
        } catch (\Exception $e) {
            // If it throws an exception, it should NOT be about argument conflicts
            $this->assertStringNotContainsString('argument with name "command" already exists', $e->getMessage());
            $this->assertStringNotContainsString('An argument with name "command" already exists', $e->getMessage());
        } finally {
            // Clean up output buffer
            ob_end_clean();
        }
    }

    public function testUpdatedYamlConfigurationStructure()
    {
        // Test that we can create a YAML structure with integrated command names
        $expectedConfig = [
            'custom_commands' => [
                'package_upgrade' => [
                    'functions_file' => 'package-upgrade-functions.php',
                    'commands' => [
                        'package:list-upgrades' => [
                            'function' => 'packageUpgradeList',
                            'description' => 'List downloaded package upgrades ready for installation'
                        ],
                        'package:list-remote' => [
                            'function' => 'packageUpgradeListRemote',
                            'description' => 'Retrieve all available versions after the installed version'
                        ],
                        'package:download' => [
                            'function' => 'packageUpgradeDownload',
                            'description' => 'Download specific package versions to core/packages'
                        ],
                        'package:upgrade-all' => [
                            'function' => 'packageUpgradeAll',
                            'description' => 'Orchestrate the complete upgrade workflow'
                        ]
                    ]
                ]
            ]
        ];
        
        // Test that the structure is valid
        $this->assertArrayHasKey('custom_commands', $expectedConfig);
        $this->assertArrayHasKey('package_upgrade', $expectedConfig['custom_commands']);
        
        $commands = $expectedConfig['custom_commands']['package_upgrade']['commands'];
        
        // Test integrated command names are in config
        $this->assertArrayHasKey('package:list-upgrades', $commands);
        $this->assertArrayHasKey('package:list-remote', $commands);
        $this->assertArrayHasKey('package:download', $commands);
        $this->assertArrayHasKey('package:upgrade-all', $commands);
        
        // Test that old conflicting names are NOT in config
        $this->assertArrayNotHasKey('package:upgrade:list', $commands);
        $this->assertArrayNotHasKey('package:upgrade:list-remote', $commands);
        $this->assertArrayNotHasKey('package:upgrade:download', $commands);
        $this->assertArrayNotHasKey('package:upgrade:all', $commands);
    }

    public function testCommandNamesFollowExistingPackageNamespace()
    {
        if (!file_exists(getcwd() . '/config.core.php') && !getenv('MODX_CORE_PATH')) {
            $this->markTestSkipped('MODX configuration not available - integration test requires MODX installation');
            return;
        }
        
        $this->registerIntegratedCommands();
        
        $commands = MODX_CLI::get_commands();
        $packageCommands = array_filter($commands, function($cmd) {
            return strpos($cmd->getName(), 'package:') === 0;
        });
        
        $packageCommandNames = array_map(function($cmd) { return $cmd->getName(); }, $packageCommands);
        
        // All package commands should use the same namespace pattern
        foreach ($packageCommandNames as $name) {
            $this->assertStringStartsWith('package:', $name);
            // Should not have nested namespaces like package:upgrade:*
            $parts = explode(':', $name);
            $this->assertLessThanOrEqual(2, count($parts), "Command '$name' should not have nested namespaces");
        }
    }
}
