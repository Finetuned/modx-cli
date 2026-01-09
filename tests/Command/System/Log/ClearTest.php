<?php

namespace MODX\CLI\Tests\Command\System\Log;

use MODX\CLI\Command\System\Log\Clear;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class ClearTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        $this->modx = $this->createMock('MODX\Revolution\modX');
        $this->command = new Clear();
        $this->command->modx = $this->modx;
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('System\\Log\\Truncate', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('system:log:clear', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Clear the MODX system log', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        $response = $this->createProcessorResponse(['success' => true]);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with('System\\Log\\Truncate', $this->anything(), $this->anything())
            ->willReturn($response);

        $this->commandTester->execute([]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('System log cleared successfully', $output);
    }

    public function testExecuteWithJsonOption()
    {
        $response = $this->createProcessorResponse(['success' => true]);

        $this->modx->method('runProcessor')
            ->willReturn($response);

        $this->commandTester->execute([
            '--json' => true
        ]);

        $output = $this->commandTester->getDisplay();
        $decoded = json_decode($output, true);
        $this->assertNotNull($decoded);
        $this->assertTrue($decoded['success']);
    }

    private function createProcessorResponse(array $payload, bool $isError = false)
    {
        $response = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $response->method('getResponse')
            ->willReturn(json_encode($payload));
        $response->method('isError')
            ->willReturn($isError);

        return $response;
    }
}
