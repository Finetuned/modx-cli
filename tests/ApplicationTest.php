<?php namespace MODX\CLI\Tests;

use MODX\CLI\Application;
use MODX\CLI\Configuration\Instance;
use MODX\CLI\Configuration\Extension;
use MODX\CLI\Configuration\Component;
use MODX\CLI\Configuration\ExcludedCommands;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @group integration
 * @group requires-modx
 */
class ApplicationTest extends TestCase
{
    protected $app;

    protected function setUp(): void
    {
        if (!getenv('MODX_INTEGRATION_TESTS')) {
            $this->markTestSkipped('Integration tests are disabled. Set MODX_INTEGRATION_TESTS=1 to enable.');
        }
        // Reset global state before each test
        if (defined('MODX_CORE_PATH')) {
            // Can't undefine, but we can work around it
        }
        $this->app = null;
    }

    protected function tearDown(): void
    {
        $this->app = null;
    }

    // ============================================
    // Basic Initialization Tests
    // ============================================

    public function testApplicationInitialization()
    {
        $app = new Application();
        $this->assertInstanceOf(Application::class, $app);
        $this->assertEquals('MODX CLI', $app->getName());
        $this->assertEquals('1.0.0', $app->getVersion());
    }

    public function testConfigurationInitialization()
    {
        $app = new Application();
        
        // Test that configuration objects are initialized
        $this->assertInstanceOf(Instance::class, $app->instances);
        $this->assertInstanceOf(Extension::class, $app->extensions);
        $this->assertInstanceOf(Component::class, $app->components);
        $this->assertInstanceOf(ExcludedCommands::class, $app->excludedCommands);
    }

    // ============================================
    // Input Definition Tests
    // ============================================

    public function testGetDefaultInputDefinition()
    {
        $app = new Application();
        $definition = $app->getDefinition();
        
        // Check for our custom options
        $this->assertTrue($definition->hasOption('site'));
        $this->assertEquals('s', $definition->getOption('site')->getShortcut());
        
        // Check for global options
        $this->assertTrue($definition->hasOption('json'));
        $this->assertTrue($definition->hasOption('ssh'));
        
        // Verify SSH option requires a value
        $sshOption = $definition->getOption('ssh');
        $this->assertTrue($sshOption->isValueRequired());
    }

    // ============================================
    // Command Loading Tests
    // ============================================

    public function testGetDefaultCommands()
    {
        $app = new Application();
        $commands = $app->all();
        
        // Check for built-in Symfony commands
        $this->assertArrayHasKey('list', $commands);
        $this->assertArrayHasKey('help', $commands);
        
        // Check for our custom commands
        $this->assertArrayHasKey('version', $commands);
        $this->assertArrayHasKey('system:info', $commands);
        $this->assertArrayHasKey('system:clearcache', $commands);
        $this->assertArrayHasKey('resource:list', $commands);
    }

    public function testCommandLoadingFromCore()
    {
        $app = new Application();
        $commands = $app->all();
        
        // Verify core commands are loaded
        $coreCommands = [
            'version',
            'system:info',
            'system:clearcache',
            'resource:list',
            'category:create',
            'chunk:create',
            'snippet:create',
            'template:create',
        ];
        
        foreach ($coreCommands as $commandName) {
            $this->assertArrayHasKey($commandName, $commands, "Core command '{$commandName}' should be loaded");
        }
    }

    public function testCommandsAreInstancesOfBaseCmd()
    {
        $app = new Application();
        $commands = $app->all();
        
        // Check that custom commands extend the base class
        if (isset($commands['version'])) {
            $this->assertInstanceOf('Symfony\Component\Console\Command\Command', $commands['version']);
        }
    }

    // ============================================
    // MODX Integration Tests
    // ============================================

    public function testGetCwd()
    {
        $app = new Application();
        $cwd = $app->getCwd();
        
        $this->assertIsString($cwd);
        $this->assertStringEndsWith('/', $cwd, 'getCwd should return path with trailing slash');
    }

    // ============================================
    // Excluded Commands Tests
    // ============================================

    public function testGetExcludedCommands()
    {
        $app = new Application();
        $excluded = $app->getExcludedCommands();
        
        $this->assertIsArray($excluded, 'getExcludedCommands should return an array');
    }

    // ============================================
    // Service Loading Tests
    // ============================================

    public function testGetServiceReturnsNullWithoutMODX()
    {
        // Create a temporary directory for testing
        $tempDir = sys_get_temp_dir() . '/modx_cli_test_' . uniqid();
        mkdir($tempDir, 0777, true);
        
        $originalCwd = getcwd();
        chdir($tempDir);
        
        $app = new Application();
        $service = $app->getService('nonexistent');
        
        $this->assertNull($service, 'getService should return null when MODX is not initialized');
        
        // Cleanup
        chdir($originalCwd);
        rmdir($tempDir);
    }

    // ============================================
    // Instance Handling Tests
    // ============================================

    public function testCheckInstanceAsArgument()
    {
        // Store original argv
        $originalArgv = $_SERVER['argv'] ?? null;
        
        // Test with -s flag
        $_SERVER['argv'] = ['modx', '-stest', 'some:command'];
        
        $app = new Application();
        
        // Reflection to access protected method
        $reflection = new \ReflectionClass($app);
        $method = $reflection->getMethod('checkInstanceAsArgument');
        $method->setAccessible(true);
        
        $result = $method->invoke($app, null);
        $this->assertEquals('test', $result);
        
        // Restore original argv
        if ($originalArgv !== null) {
            $_SERVER['argv'] = $originalArgv;
        } else {
            unset($_SERVER['argv']);
        }
    }

    public function testCheckInstanceAsArgumentWithoutFlag()
    {
        // Store original argv
        $originalArgv = $_SERVER['argv'] ?? null;
        
        // Test without -s flag
        $_SERVER['argv'] = ['modx', 'some:command'];
        
        $app = new Application();
        
        // Reflection to access protected method
        $reflection = new \ReflectionClass($app);
        $method = $reflection->getMethod('checkInstanceAsArgument');
        $method->setAccessible(true);
        
        $result = $method->invoke($app, 'default');
        $this->assertEquals('default', $result);
        
        // Restore original argv
        if ($originalArgv !== null) {
            $_SERVER['argv'] = $originalArgv;
        } else {
            unset($_SERVER['argv']);
        }
    }

    // ============================================
    // Command Class Generation Tests
    // ============================================

    public function testGetCommandClass()
    {
        $app = new Application();
        
        // Create a mock SplFileInfo object
        $mockFile = $this->getMockBuilder('Symfony\Component\Finder\SplFileInfo')
            ->setConstructorArgs(['test.php', '', 'Category/Create.php'])
            ->getMock();
        
        $mockFile->method('getRelativePathname')
            ->willReturn('Category/Create.php');
        
        // Use reflection to access protected method
        $reflection = new \ReflectionClass($app);
        $method = $reflection->getMethod('getCommandClass');
        $method->setAccessible(true);
        
        $result = $method->invoke($app, $mockFile);
        
        $this->assertEquals('MODX\\CLI\\Command\\Category\\Create', $result);
    }

    // ============================================
    // Extra Service Tests
    // ============================================

    public function testGetExtraServiceWithParams()
    {
        // Create a temporary directory for testing
        $tempDir = sys_get_temp_dir() . '/modx_cli_test_' . uniqid();
        mkdir($tempDir, 0777, true);
        
        $originalCwd = getcwd();
        chdir($tempDir);
        
        $app = new Application();
        
        $data = [
            'service' => 'testService',
            'params' => ['key' => 'value']
        ];
        
        $result = $app->getExtraService($data);
        
        // Should return null since MODX is not initialized
        $this->assertNull($result);
        
        // Cleanup
        chdir($originalCwd);
        rmdir($tempDir);
    }

    public function testGetExtraServiceWithoutParams()
    {
        // Create a temporary directory for testing
        $tempDir = sys_get_temp_dir() . '/modx_cli_test_' . uniqid();
        mkdir($tempDir, 0777, true);
        
        $originalCwd = getcwd();
        chdir($tempDir);
        
        $app = new Application();
        
        $data = [
            'service' => 'testService'
        ];
        
        $result = $app->getExtraService($data);
        
        // Should return null since MODX is not initialized
        $this->assertNull($result);
        
        // Cleanup
        chdir($originalCwd);
        rmdir($tempDir);
    }

    // ============================================
    // Edge Cases and Error Handling Tests
    // ============================================

    public function testApplicationHandlesEmptyExtensions()
    {
        $app = new Application();
        $commands = $app->all();
        
        // Should successfully load commands even with no extensions
        $this->assertIsArray($commands);
        $this->assertNotEmpty($commands);
    }

    public function testApplicationHandlesEmptyComponents()
    {
        $app = new Application();
        $commands = $app->all();
        
        // Should successfully load commands even with no components (no MODX)
        $this->assertIsArray($commands);
        $this->assertNotEmpty($commands);
    }
}
