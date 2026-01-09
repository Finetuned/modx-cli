<?php

namespace MODX\CLI\Tests\Command\System\Events;

use MODX\CLI\Command\System\Events\Delete;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class DeleteTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        $this->modx = $this->createMock('MODX\Revolution\modX');
        $this->command = new Delete();
        $this->command->modx = $this->modx;
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('System\\Event\\Remove', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('system:event:delete', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Delete a system event in MODX', $this->command->getDescription());
    }

    public function testConfigureHasArgumentAndOption()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasArgument('id'));
        $this->assertTrue($definition->getArgument('id')->isRequired());
        $this->assertTrue($definition->hasOption('force'));
        $this->assertFalse($definition->getOption('force')->acceptValue());
    }

    public function testExecuteWithMissingEvent()
    {
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modEvent::class, '10')
            ->willReturn(null);

        $this->commandTester->execute([
            'id' => '10'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Event with ID 10 not found', $output);
        $this->assertStringContainsString('Operation aborted', $output);
        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }

    public function testExecuteWithForceDeletesEvent()
    {
        $event = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get'])
            ->getMock();
        $event->method('get')->with('name')->willReturn('OnDocFormSave');

        $response = $this->createProcessorResponse(['success' => true]);

        $this->modx->expects($this->once())
            ->method('getObject')
            ->with(\MODX\Revolution\modEvent::class, '10')
            ->willReturn($event);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'System\\Event\\Remove',
                $this->callback(function($properties) {
                    return $properties['id'] === '10';
                }),
                $this->anything()
            )
            ->willReturn($response);

        $this->commandTester->execute([
            'id' => '10',
            '--force' => true
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Event deleted successfully', $output);
    }

    public function testExecuteWithJsonOption()
    {
        $event = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get'])
            ->getMock();
        $event->method('get')->with('name')->willReturn('OnDocFormSave');

        $response = $this->createProcessorResponse(['success' => true]);

        $this->modx->method('getObject')
            ->willReturn($event);
        $this->modx->method('runProcessor')
            ->willReturn($response);

        $this->commandTester->execute([
            'id' => '10',
            '--force' => true,
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
