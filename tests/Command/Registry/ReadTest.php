<?php

namespace MODX\CLI\Tests\Command\Registry;

use MODX\CLI\Command\Registry\Read;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class ReadTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        $this->modx = $this->createMock('MODX\Revolution\modX');

        $this->command = new Read();
        $this->command->modx = $this->modx;

        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessor()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('System\\Registry\\Register\\Read', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('registry:read', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Read messages from a MODX registry register', $this->command->getDescription());
    }

    public function testExecuteWithJsonDecodesMessage()
    {
        $processorResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'message' => json_encode([
                    ['msg' => 'Hello registry']
                ]),
            ]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'System\\Registry\\Register\\Read',
                $this->callback(function ($properties) {
                    return $properties['topic'] === 'test-topic'
                        && $properties['register'] === 'db'
                        && $properties['format'] === 'json'
                        && $properties['remove_read'] === true;
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        $this->commandTester->execute([
            'topic' => 'test-topic',
            '--json' => true,
        ]);

        $output = $this->commandTester->getDisplay();
        $decoded = json_decode($output, true);

        $this->assertIsArray($decoded);
        $this->assertTrue($decoded['success']);
        $this->assertIsArray($decoded['message']);
    }

    public function testExecuteWithKeepOptionSetsRemoveReadFalse()
    {
        $processorResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
                'message' => json_encode([]),
            ]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'System\\Registry\\Register\\Read',
                $this->callback(function ($properties) {
                    return $properties['remove_read'] === false;
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        $this->commandTester->execute([
            'topic' => 'test-topic',
            '--keep' => true,
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}
