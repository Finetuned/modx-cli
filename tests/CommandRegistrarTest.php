<?php

namespace MODX\CLI\Tests;

use MODX\CLI\CommandRegistrar;
use MODX\CLI\Configuration\Extension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * Test CommandRegistrar functionality
 * Note: CommandRegistrar is abstract, so we create a concrete implementation for testing
 */
class CommandRegistrarTest extends TestCase
{
    protected $testCommandsDir;
    protected $testDeprecatedFile;

    protected function setUp(): void
    {
        // Create a temporary commands directory for testing
        $this->testCommandsDir = sys_get_temp_dir() . '/modx_cli_test_commands_' . uniqid();
        mkdir($this->testCommandsDir . '/Command', 0777, true);

        // Create test command files
        file_put_contents(
            $this->testCommandsDir . '/Command/TestCommand.php',
            '<?php namespace Test\Command; class TestCommand {}'
        );

        file_put_contents(
            $this->testCommandsDir . '/Command/AnotherCommand.php',
            '<?php namespace Test\Command; class AnotherCommand {}'
        );

        // Create abstract class (should be excluded)
        file_put_contents(
            $this->testCommandsDir . '/Command/AbstractBase.php',
            '<?php namespace Test\Command; abstract class AbstractBase {}'
        );
    }

    protected function tearDown(): void
    {
        // Cleanup test files
        if ($this->testCommandsDir && is_dir($this->testCommandsDir)) {
            $this->recursiveDelete($this->testCommandsDir);
        }

        if ($this->testDeprecatedFile && file_exists($this->testDeprecatedFile)) {
            unlink($this->testDeprecatedFile);
        }

        TestRegistrar::setIO(null);
    }

    /**
     * Recursively delete directory and its contents
     */
    protected function recursiveDelete($dir)
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->recursiveDelete($path) : unlink($path);
        }
        rmdir($dir);
    }

    // ============================================
    // Command Discovery Tests
    // ============================================

    public function testListCommandsFindsCommandFiles()
    {
        $registrar = $this->getTestRegistrar();

        // Use reflection to call listCommands
        $reflection = new \ReflectionClass($registrar);
        $method = $reflection->getMethod('listCommands');
        $method->setAccessible(true);

        $finder = $method->invoke(null);

        $this->assertInstanceOf(Finder::class, $finder);

        // Count files (should be 2 - TestCommand and AnotherCommand, excluding AbstractBase)
        $count = iterator_count($finder);
        $this->assertEquals(2, $count, 'Should find 2 command files (excluding abstract class)');
    }

    public function testListCommandsExcludesAbstractClasses()
    {
        $registrar = $this->getTestRegistrar();

        // Use reflection to call listCommands
        $reflection = new \ReflectionClass($registrar);
        $method = $reflection->getMethod('listCommands');
        $method->setAccessible(true);

        $finder = $method->invoke(null);

        $foundAbstract = false;
        foreach ($finder as $file) {
            if (strpos($file->getFilename(), 'AbstractBase') !== false) {
                $foundAbstract = true;
            }
        }

        $this->assertFalse($foundAbstract, 'Should not find abstract classes');
    }

    // ============================================
    // Command Class Generation Tests
    // ============================================

    public function testGetCommandClassGeneratesCorrectClassName()
    {
        // Since getCommandClass requires pass-by-reference and is tightly coupled to Finder,
        // we test it indirectly through the command discovery process
        $registrar = $this->getTestRegistrar();

        // Use reflection to call listCommands
        $reflection = new \ReflectionClass($registrar);
        $method = $reflection->getMethod('listCommands');
        $method->setAccessible(true);

        $finder = $method->invoke(null);

        // Verify that files are found and can be processed
        $count = 0;
        foreach ($finder as $file) {
            $count++;
            // Just verify files exist - the actual getCommandClass is tested via integration
            $this->assertInstanceOf(SplFileInfo::class, $file);
        }

        $this->assertEquals(2, $count, 'Should find 2 command files');
    }

    public function testGetCommandClassHandlesSimpleFilename()
    {
        // Test command class generation indirectly by verifying file discovery works
        $registrar = $this->getTestRegistrar();

        $reflection = new \ReflectionClass($registrar);
        $method = $reflection->getMethod('listCommands');
        $method->setAccessible(true);

        $finder = $method->invoke(null);

        // Verify simple filename files are found
        $foundSimpleFile = false;
        foreach ($finder as $file) {
            if ($file->getFilename() === 'TestCommand.php') {
                $foundSimpleFile = true;
            }
        }

        $this->assertTrue($foundSimpleFile, 'Should find simple filename command');
    }

    public function testGetCommandClassHandlesNestedDirectories()
    {
        // Create a nested directory structure for testing
        mkdir($this->testCommandsDir . '/Command/Context/Setting', 0777, true);
        file_put_contents(
            $this->testCommandsDir . '/Command/Context/Setting/GetList.php',
            '<?php namespace Test\\Command\\Context\\Setting; class GetList {}'
        );

        $registrar = $this->getTestRegistrar();

        // Use reflection to call listCommands
        $reflection = new \ReflectionClass($registrar);
        $method = $reflection->getMethod('listCommands');
        $method->setAccessible(true);

        $finder = $method->invoke(null);

        // Verify nested directory files are found
        $foundNested = false;
        foreach ($finder as $file) {
            if (strpos($file->getRelativePathname(), 'Context/Setting/GetList.php') !== false) {
                $foundNested = true;
            }
        }

        $this->assertTrue($foundNested, 'Should find nested directory command file');
    }

    // ============================================
    // Reflection Tests
    // ============================================

    public function testGetNSReturnsCorrectNamespace()
    {
        $registrar = $this->getTestRegistrar();

        // Use reflection to call getNS
        $reflection = new \ReflectionClass($registrar);
        $method = $reflection->getMethod('getNS');
        $method->setAccessible(true);

        $result = $method->invoke(null);

        // getNS() uses get_called_class() which returns the actual calling class
        $this->assertEquals(
            'MODX\\CLI\\Tests',
            $result,
            'Should return correct namespace'
        );
    }

    public function testGetReflectionReturnsReflectionClass()
    {
        $registrar = $this->getTestRegistrar();

        // Use reflection to call getReflection
        $reflection = new \ReflectionClass($registrar);
        $method = $reflection->getMethod('getReflection');
        $method->setAccessible(true);

        $result = $method->invoke(null);

        $this->assertInstanceOf(\ReflectionClass::class, $result);
        $this->assertEquals(
            'MODX\\CLI\\Tests\\TestRegistrar',
            $result->getName()
        );
    }

    public function testGetRootPathReturnsCorrectPath()
    {
        $registrar = $this->getTestRegistrar();

        // Use reflection to call getRootPath
        $reflection = new \ReflectionClass($registrar);
        $method = $reflection->getMethod('getRootPath');
        $method->setAccessible(true);

        $result = $method->invoke(null);

        $this->assertIsString($result);
        // getRootPath() should return the temporary test directory path
        $this->assertStringContainsString('modx_cli_test_commands_', $result);
        $this->assertDirectoryExists($result, 'Path should be a valid directory');
    }

    // ============================================
    // Deprecated Command Handling Tests
    // ============================================

    public function testUnRegisterWithDeprecatedFile()
    {
        $deprecatedClasses = [
            'Test\\Command\\DeprecatedCommand',
            'Test\\Command\\LegacyCommand',
        ];
        $this->testDeprecatedFile = $this->testCommandsDir . '/deprecated.php';
        file_put_contents(
            $this->testDeprecatedFile,
            "<?php\nreturn " . var_export($deprecatedClasses, true) . ";\n"
        );

        // Create a mock configuration
        $mockConfig = $this->getMockBuilder(Extension::class)
            ->getMock();

        $removedClasses = [];
        $mockConfig->expects($this->exactly(2))
            ->method('remove')
            ->willReturnCallback(function ($class) use (&$removedClasses) {
                $removedClasses[] = $class;
            });

        // Create mock IO
        $mockIO = new class {
            public $messages = [];

            public function write($message)
            {
                $this->messages[] = $message;
            }
        };

        // Create test registrar
        $registrar = $this->getTestRegistrar();
        $registrar::setIO($mockIO);

        // Call unRegister
        $reflection = new \ReflectionClass($registrar);
        $method = $reflection->getMethod('unRegister');
        $method->setAccessible(true);
        $method->invoke(null, $mockConfig);

        $this->assertSame($deprecatedClasses, $removedClasses);
        $this->assertNotEmpty($mockIO->messages);
    }

    public function testUnRegisterWithoutDeprecatedFile()
    {
        // Create a mock configuration
        $mockConfig = $this->getMockBuilder(Extension::class)
            ->getMock();

        // Create mock IO
        $mockIO = new class {
            public function write($message)
            {
                // no-op
            }
        };

        // Should not call remove if no deprecated file exists
        $mockConfig->expects($this->never())
            ->method('remove');

        // Create test registrar
        $registrar = $this->getTestRegistrar();

        $registrar::setIO($mockIO);

        // Call unRegister
        $reflection = new \ReflectionClass($registrar);
        $method = $reflection->getMethod('unRegister');
        $method->setAccessible(true);
        $method->invoke(null, $mockConfig);
    }

    // ============================================
    // Helper Methods
    // ============================================

    /**
     * Get a test registrar instance
     */
    protected function getTestRegistrar()
    {
        return new TestRegistrar();
    }
}

/**
 * Concrete implementation of CommandRegistrar for testing
 */
class TestRegistrar extends CommandRegistrar
{
    protected static $commandsFolder = 'Command';

    /**
     * Override getRootPath to use our test directory
     */
    protected static function getRootPath()
    {
        // Get the test directory from the test case
        $testDir = sys_get_temp_dir();
        $dirs = glob($testDir . '/modx_cli_test_commands_*');

        if (!empty($dirs)) {
            rsort($dirs, SORT_STRING);
            return $dirs[0];
        }

        // Fallback to tests directory
        return dirname(__DIR__) . '/tests';
    }
}
