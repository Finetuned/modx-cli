<?php namespace MODX\CLI\Tests\SSH;

use MODX\CLI\SSH\CommandProxy;
use MODX\CLI\SSH\CommandExecutorInterface;
use MODX\CLI\SSH\ConnectionParser;
use PHPUnit\Framework\TestCase;

/**
 * Test CommandProxy functionality
 * 
 * NOTE: These tests skip actual SSH execution since they require a real SSH connection.
 * The tests verify command building and structure without executing real SSH commands.
 */
class CommandProxyTest extends TestCase
{
    // ============================================
    // SSH Command Building Tests
    // ============================================

    public function testBuildSSHCommandBasicStructure()
    {
        $parser = new ConnectionParser('user@example.com');
        $proxy = new CommandProxy($parser, 'system:info', [], new CommandProxyStubExecutor());

        $command = $this->callProtectedMethod($proxy, 'buildSSHCommand');

        $this->assertStringContainsString('ssh', $command);
        $this->assertStringContainsString('user@example.com', $command);
        $this->assertStringContainsString('modx system:info', $command);
    }

    public function testBuildSSHCommandWithCustomPort()
    {
        $parser = new ConnectionParser('user@example.com:2222');
        $proxy = new CommandProxy($parser, 'system:info', [], new CommandProxyStubExecutor());

        $command = $this->callProtectedMethod($proxy, 'buildSSHCommand');

        $this->assertStringContainsString('-p 2222', $command);
    }

    public function testBuildSSHCommandWithPath()
    {
        $parser = new ConnectionParser('user@example.com:/var/www');
        $proxy = new CommandProxy($parser, 'system:info', [], new CommandProxyStubExecutor());

        $command = $this->callProtectedMethod($proxy, 'buildSSHCommand');

        $this->assertStringContainsString('cd /var/www &&', $command);
        $this->assertStringContainsString('modx system:info', $command);
    }

    public function testArgumentEscaping()
    {
        $parser = new ConnectionParser('user@example.com');
        $proxy = new CommandProxy($parser, 'resource:list', ['--name=My Site'], new CommandProxyStubExecutor());

        $command = $this->callProtectedMethod($proxy, 'buildRemoteCommand');

        $this->assertStringContainsString('modx resource:list', $command);
        $this->assertStringContainsString("'--name=My Site'", $command);
    }

    public function testMultipleArguments()
    {
        $parser = new ConnectionParser('user@example.com');
        $proxy = new CommandProxy($parser, 'resource:list', ['--limit=10', '--start=20'], new CommandProxyStubExecutor());

        $command = $this->callProtectedMethod($proxy, 'buildRemoteCommand');

        $this->assertStringContainsString('--limit=10', $command);
        $this->assertStringContainsString('--start=20', $command);
    }

    public function testSpecialCharactersInArguments()
    {
        $parser = new ConnectionParser('user@example.com');
        $proxy = new CommandProxy($parser, 'resource:list', ["--name=O'Reilly"], new CommandProxyStubExecutor());

        $command = $this->callProtectedMethod($proxy, 'buildRemoteCommand');

        $this->assertStringContainsString('Reilly', $command);
    }

    // ============================================
    // Process Execution Tests
    // ============================================

    public function testProcessTimeoutSet()
    {
        $executor = new CommandProxyStubExecutor();
        $parser = new ConnectionParser('user@example.com');
        $proxy = new CommandProxy($parser, 'system:info', [], $executor);

        $proxy->execute();

        $this->assertEquals(3600, $executor->timeout);
    }

    public function testTTYModeEnabled()
    {
        $executor = new CommandProxyStubExecutor();
        $parser = new ConnectionParser('user@example.com');
        $proxy = new CommandProxy($parser, 'system:info', [], $executor);

        $proxy->execute();

        $this->assertTrue($executor->tty);
    }

    public function testStdoutStderrStreaming()
    {
        $executor = new CommandProxyStubExecutor();
        $parser = new ConnectionParser('user@example.com');
        $proxy = new CommandProxy($parser, 'system:info', [], $executor);

        $proxy->execute();

        $this->assertIsCallable($executor->outputCallback);
    }

    public function testExitCodeReturned()
    {
        $executor = new CommandProxyStubExecutor();
        $executor->returnCode = 12;
        $parser = new ConnectionParser('user@example.com');
        $proxy = new CommandProxy($parser, 'system:info', [], $executor);

        $this->assertEquals(12, $proxy->execute());
    }

    // ============================================
    // Constructor Tests
    // ============================================

    public function testConstructorAcceptsConnectionParser()
    {
        $parser = new ConnectionParser('user@example.com');
        $proxy = new CommandProxy($parser, 'system:info', []);
        
        $this->assertInstanceOf(CommandProxy::class, $proxy);
    }

    public function testConstructorAcceptsCommandAndArgs()
    {
        $parser = new ConnectionParser('user@example.com');
        $proxy = new CommandProxy($parser, 'cache:clear', ['--partition' => 'web']);
        
        $this->assertInstanceOf(CommandProxy::class, $proxy);
    }

    protected function callProtectedMethod($object, $methodName)
    {
        $reflection = new \ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invoke($object);
    }
}

class CommandProxyStubExecutor implements CommandExecutorInterface
{
    public $command;
    public $timeout;
    public $tty;
    public $outputCallback;
    public $returnCode = 0;

    public function run(string $command, int $timeout, bool $tty, ?callable $outputCallback = null): int
    {
        $this->command = $command;
        $this->timeout = $timeout;
        $this->tty = $tty;
        $this->outputCallback = $outputCallback;

        return $this->returnCode;
    }
}
