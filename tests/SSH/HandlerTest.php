<?php namespace MODX\CLI\Tests\SSH;

use MODX\CLI\SSH\Handler;
use MODX\CLI\SSH\CommandExecutorInterface;
use PHPUnit\Framework\TestCase;

/**
 * Test SSH Handler functionality
 * 
 * NOTE: These tests verify the Handler's coordination role without actual SSH execution.
 * The Handler is a simple wrapper that delegates to ConnectionParser and CommandProxy.
 */
class HandlerTest extends TestCase
{
    // ============================================
    // Construction Tests
    // ============================================

    public function testConstructorStoresConnectionString()
    {
        $handler = new Handler('user@example.com');
        
        $this->assertInstanceOf(Handler::class, $handler);
    }

    public function testConstructorAcceptsVariousConnectionFormats()
    {
        $formats = [
            'example.com',
            'user@example.com',
            'user@example.com:2222',
            'user@example.com:/var/www',
        ];
        
        foreach ($formats as $format) {
            $handler = new Handler($format);
            $this->assertInstanceOf(Handler::class, $handler);
        }
    }

    // ============================================
    // Execution Tests (Skipped - Require Real SSH)
    // ============================================

    public function testExecuteCreatesConnectionParser()
    {
        $executor = new HandlerStubExecutor();
        $handler = new Handler('user@example.com', $executor);

        $handler->execute('system:info');

        $this->assertStringContainsString('user@example.com', $executor->command);
    }

    public function testExecuteCreatesCommandProxy()
    {
        $executor = new HandlerStubExecutor();
        $handler = new Handler('user@example.com', $executor);

        $handler->execute('system:info');

        $this->assertStringContainsString('ssh', $executor->command);
    }

    public function testExecuteDelegatesToProxyExecute()
    {
        $executor = new HandlerStubExecutor();
        $executor->returnCode = 7;
        $handler = new Handler('user@example.com', $executor);

        $result = $handler->execute('system:info');

        $this->assertEquals(7, $result);
    }

    public function testExecuteReturnsProxyExitCode()
    {
        $executor = new HandlerStubExecutor();
        $executor->returnCode = 13;
        $handler = new Handler('user@example.com', $executor);

        $this->assertEquals(13, $handler->execute('system:info'));
    }

    // ============================================
    // Integration Tests (Skipped)
    // ============================================

    public function testEndToEndFlowWithMockedComponents()
    {
        $executor = new HandlerStubExecutor();
        $handler = new Handler('user@example.com:/var/www', $executor);

        $handler->execute('system:info');

        $this->assertStringContainsString('cd /var/www &&', $executor->command);
    }

    public function testCorrectParameterPassing()
    {
        $executor = new HandlerStubExecutor();
        $handler = new Handler('user@example.com', $executor);

        $handler->execute('resource:list', ['--limit=10', '--start=20']);

        $this->assertStringContainsString('--limit=10', $executor->command);
        $this->assertStringContainsString('--start=20', $executor->command);
    }
}

class HandlerStubExecutor implements CommandExecutorInterface
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
