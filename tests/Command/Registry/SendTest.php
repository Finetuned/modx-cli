<?php

namespace MODX\CLI\Tests\Command\Registry;

use MODX\CLI\Command\Registry\Send;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class SendTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        $this->modx = $this->createMock('MODX\Revolution\modX');

        $this->command = new Send();
        $this->command->modx = $this->modx;

        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessor()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('System\\Registry\\Register\\Send', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('registry:send', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Send a message to a MODX registry register', $this->command->getDescription());
    }

    public function testExecuteWithJsonSuccess()
    {
        $processorResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
            ]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'System\\Registry\\Register\\Send',
                $this->callback(function ($properties) {
                    return $properties['topic'] === 'test-topic'
                        && $properties['message'] === 'hello'
                        && $properties['register'] === 'db'
                        && $properties['message_format'] === 'string';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        $this->commandTester->execute([
            'topic' => 'test-topic',
            'message' => 'hello',
            '--json' => true,
        ]);

        $output = $this->commandTester->getDisplay();
        $decoded = json_decode($output, true);
        $this->assertIsArray($decoded);
        $this->assertTrue($decoded['success']);
    }

    public function testExecuteWithMessageKey()
    {
        $processorResponse = $this->getMockBuilder('MODX\\Revolution\\Processors\\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $processorResponse->method('getResponse')
            ->willReturn(json_encode([
                'success' => true,
            ]));
        $processorResponse->method('isError')->willReturn(false);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'System\\Registry\\Register\\Send',
                $this->callback(function ($properties) {
                    return $properties['message_key'] === 'key1'
                        && $properties['message_format'] === 'json';
                }),
                $this->anything()
            )
            ->willReturn($processorResponse);

        $this->commandTester->execute([
            'topic' => 'test-topic',
            'message' => '{"foo":"bar"}',
            '--message_key' => 'key1',
            '--message_format' => 'json',
        ]);

        $this->assertEquals(0, $this->commandTester->getStatusCode());
    }
}
