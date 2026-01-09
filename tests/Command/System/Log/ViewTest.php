<?php

namespace MODX\CLI\Tests\Command\System\Log;

use MODX\CLI\Command\System\Log\View;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class ViewTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        $this->modx = $this->createMock('MODX\Revolution\modX');
        $this->command = new View();
        $this->command->modx = $this->modx;
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('System\\Log\\GetList', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('system:log:view', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('View the MODX system log', $this->command->getDescription());
    }

    public function testConfigureHasOptions()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('level'));
        $this->assertTrue($definition->getOption('level')->isValueRequired());
        $this->assertTrue($definition->hasOption('format'));
        $this->assertTrue($definition->getOption('format')->isValueRequired());
    }

    public function testExecuteWithJsonOption()
    {
        $response = $this->createProcessorResponse([
            'success' => true,
            'total' => 1,
            'results' => [
                [
                    'id' => 1,
                    'action' => 'info',
                    'name' => 'Test log',
                    'occurred' => '2024-01-01 00:00:00'
                ]
            ]
        ]);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($response);

        $this->commandTester->execute([
            '--json' => true
        ]);

        $output = $this->commandTester->getDisplay();
        $decoded = json_decode($output, true);
        $this->assertNotNull($decoded);
        $this->assertEquals(1, $decoded['total']);
    }

    public function testExecuteWithLevelFilter()
    {
        $response = $this->createProcessorResponse([
            'success' => true,
            'total' => 0,
            'results' => []
        ]);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'System\\Log\\GetList',
                $this->callback(function($properties) {
                    return $properties['level'] === 'ERROR';
                }),
                $this->anything()
            )
            ->willReturn($response);

        $this->commandTester->execute([
            '--level' => 'ERROR'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('No log entries found', $output);
    }

    public function testExecuteWithColoredFormat()
    {
        $response = $this->createProcessorResponse([
            'success' => true,
            'total' => 1,
            'results' => [
                [
                    'action' => 'info',
                    'name' => 'Colored log',
                    'occurred' => '2024-01-01 00:00:00'
                ]
            ]
        ]);

        $this->modx->method('runProcessor')
            ->willReturn($response);

        $this->commandTester->execute([
            '--format' => 'colored'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertNotSame('', $output);
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
