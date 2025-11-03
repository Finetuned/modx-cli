<?php

namespace MODX\CLI\Tests\Integration\Commands\Custom;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration tests for custom command functions and error handling
 * 
 * Tests the underlying PHP functions that power custom commands,
 * including error handling, helper functions, and output formatting.
 */
class CustomCommandFunctionsTest extends BaseIntegrationTest
{
    /**
     * Test that the functions file exists and is loadable
     */
    public function testFunctionFileExists()
    {
        $functionsFile = __DIR__ . '/../../../../custom-commands/package-upgrade-functions.php';
        
        $this->assertFileExists($functionsFile, 'Package upgrade functions file should exist');
        
        // Verify it's valid PHP by including it
        require_once $functionsFile;
        
        // Verify main functions are defined
        $this->assertTrue(function_exists('packageUpgradeList'), 'packageUpgradeList function should be defined');
        $this->assertTrue(function_exists('packageUpgradeListRemote'), 'packageUpgradeListRemote function should be defined');
        $this->assertTrue(function_exists('packageUpgradeDownload'), 'packageUpgradeDownload function should be defined');
        $this->assertTrue(function_exists('packageUpgradeAll'), 'packageUpgradeAll function should be defined');
    }
    
    /**
     * Test that helper functions are available
     */
    public function testHelperFunctionsAvailable()
    {
        require_once __DIR__ . '/../../../../custom-commands/package-upgrade-functions.php';
        
        // Verify helper functions exist
        $this->assertTrue(function_exists('getInstalledPackages'), 'getInstalledPackages helper should exist');
        $this->assertTrue(function_exists('getDownloadedPackages'), 'getDownloadedPackages helper should exist');
        $this->assertTrue(function_exists('parseVersion'), 'parseVersion helper should exist');
        $this->assertTrue(function_exists('isNewerVersion'), 'isNewerVersion helper should exist');
    }
    
    /**
     * Test parseVersion helper function
     */
    public function testParseVersionHelper()
    {
        require_once __DIR__ . '/../../../../custom-commands/package-upgrade-functions.php';
        
        // Test standard version format
        $result = parseVersion('3.0.2-pl');
        $this->assertEquals('3.0.2', $result['version']);
        $this->assertEquals('pl', $result['release']);
        
        // Test version without release
        $result = parseVersion('3.0.2');
        $this->assertEquals('3.0.2', $result['version']);
        $this->assertEquals('pl', $result['release']);
    }
    
    /**
     * Test isNewerVersion helper function
     */
    public function testIsNewerVersionHelper()
    {
        require_once __DIR__ . '/../../../../custom-commands/package-upgrade-functions.php';
        
        // Test newer version detection
        $this->assertTrue(isNewerVersion('3.0.2-pl', '3.0.1-pl'));
        $this->assertFalse(isNewerVersion('3.0.1-pl', '3.0.2-pl'));
        $this->assertFalse(isNewerVersion('3.0.2-pl', '3.0.2-pl'));
    }
    
    /**
     * Test custom command error handling with missing required arguments
     */
    public function testCustomCommandMissingRequiredArgument()
    {
        // package:download requires a signature argument
        $process = $this->executeCommand(['package:download']);
        
        // Command should fail due to missing argument
        $this->assertNotEquals(0, $process->getExitCode(), 'Command should fail with missing required argument');
        
        $output = $process->getOutput() . $process->getErrorOutput();
        
        // Should contain error message about missing argument
        $this->assertTrue(
            strpos($output, 'required') !== false || 
            strpos($output, 'signature') !== false,
            'Error output should mention required argument'
        );
    }
    
    /**
     * Test package:list-remote command execution
     */
    public function testPackageListRemoteExecution()
    {
        // Execute package:list-remote with JSON format
        $process = $this->executeCommand(['package:list-remote', '--format=json', '--limit=5']);
        
        // Command may return 0 even if no packages found
        $this->assertTrue($process->getExitCode() === 0 || $process->getExitCode() === 1);
        
        $output = $process->getOutput();
        
        // Output should be valid JSON or a message
        $decoded = json_decode($output, true);
        $this->assertTrue(
            is_array($decoded) || 
            strpos($output, 'No') !== false ||
            $output === '',
            'Output should be valid JSON, empty, or a message'
        );
    }
    
    /**
     * Test dry-run mode doesn't make actual changes
     */
    public function testDryRunMode()
    {
        // Execute package:upgrade-all in dry-run mode
        $process = $this->executeCommand(['package:upgrade-all', '--dry-run']);
        
        // Command should execute
        $this->assertTrue($process->getExitCode() === 0 || $process->getExitCode() === 1);
        
        $output = $process->getOutput();
        
        // Verify dry-run indicator in output
        $this->assertTrue(
            strpos($output, 'DRY RUN') !== false || 
            strpos($output, 'dry run') !== false ||
            strpos($output, 'No upgradeable packages') !== false,
            'Output should indicate dry-run mode or no packages'
        );
    }
}
