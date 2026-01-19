<?php

namespace MODX\CLI\Tests\Command\Source;

use MODX\CLI\Command\Source\Get;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class GetTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');

        // Create the command
        $this->command = new Get();
        $this->command->modx = $this->modx;

        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertNull($processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('source:get', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Get a MODX media source by ID', $this->command->getDescription());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        $source = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['toArray'])
            ->getMock();
        $source->method('toArray')
            ->willReturn([
                'id' => 1,
                'name' => 'Filesystem',
                'description' => 'Default filesystem source',
                'class_key' => 'MODX\\Revolution\\Sources\\modFileMediaSource'
            ]);

        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('MODX\\Revolution\\Sources\\modMediaSource', ['id' => '1'])
            ->willReturn($source);

        // Execute the command
        $this->commandTester->execute([
            'id' => '1'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('ID: 1', $output);
        $this->assertStringContainsString('Name: Filesystem', $output);
    }

    public function testExecuteWithFailedResponse()
    {
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('MODX\\Revolution\\Sources\\modMediaSource', ['id' => '999'])
            ->willReturn(null);

        // Execute the command
        $this->commandTester->execute([
            'id' => '999'
        ]);

        // Verify the output
        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Media source with ID 999 not found', $output);
    }

    public function testExecuteWithJsonOption()
    {
        $source = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['toArray'])
            ->getMock();
        $source->method('toArray')
            ->willReturn(['id' => 1, 'name' => 'Filesystem']);

        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('MODX\\Revolution\\Sources\\modMediaSource', ['id' => '1'])
            ->willReturn($source);

        $this->commandTester->execute([
            'id' => '1',
            '--json' => true
        ]);

        $output = $this->commandTester->getDisplay();
        $data = json_decode($output, true);

        $this->assertIsArray($data);
        $this->assertArrayHasKey('success', $data);
        $this->assertTrue($data['success']);
    }
}
