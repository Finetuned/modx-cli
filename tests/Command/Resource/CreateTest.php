<?php namespace MODX\CLI\Tests\Command\Resource;

use MODX\CLI\Command\Resource\Create;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class CreateTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');

        // Create the command
        $this->command = new Create();
        $this->command->modx = $this->modx;

        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Resource\Create', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('resource:create', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Create a MODX resource', $this->command->getDescription());
    }

    public function testConfigureHasArgumentAndOptions()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasArgument('pagetitle'));
        $this->assertTrue($definition->getArgument('pagetitle')->isRequired());

        $this->assertEquals(0, $definition->getOption('parent')->getDefault());
        $this->assertEquals(0, $definition->getOption('template')->getDefault());
        $this->assertEquals(1, $definition->getOption('published')->getDefault());
        $this->assertEquals(0, $definition->getOption('hidemenu')->getDefault());
        $this->assertEquals('', $definition->getOption('content')->getDefault());
        $this->assertEquals('', $definition->getOption('alias')->getDefault());
        $this->assertEquals('web', $definition->getOption('context_key')->getDefault());
    }

    public function testExecuteWithDefaultOptions()
    {
        $response = $this->createProcessorResponse([
            'success' => true,
            'object' => ['id' => 42]
        ]);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Resource\Create',
                $this->callback(function($properties) {
                    return $properties['pagetitle'] === 'Test Page'
                        && $properties['parent'] === 0
                        && $properties['template'] === 0
                        && $properties['published'] === 1
                        && $properties['hidemenu'] === 0
                        && $properties['content'] === ''
                        && $properties['alias'] === ''
                        && $properties['context_key'] === 'web';
                }),
                $this->anything()
            )
            ->willReturn($response);

        $this->commandTester->execute([
            'pagetitle' => 'Test Page'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Resource created successfully', $output);
        $this->assertStringContainsString('Resource ID: 42', $output);
    }

    public function testExecuteWithOptionOverrides()
    {
        $response = $this->createProcessorResponse([
            'success' => true,
            'object' => ['id' => 123]
        ]);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Resource\Create',
                $this->callback(function($properties) {
                    return $properties['pagetitle'] === 'Custom Page'
                        && $properties['parent'] === 10
                        && $properties['template'] === 3
                        && $properties['published'] === 0
                        && $properties['hidemenu'] === 1
                        && $properties['content'] === 'Body'
                        && $properties['alias'] === 'custom'
                        && $properties['context_key'] === 'web';
                }),
                $this->anything()
            )
            ->willReturn($response);

        $this->commandTester->execute([
            'pagetitle' => 'Custom Page',
            '--parent' => '10',
            '--template' => '3',
            '--published' => 'false',
            '--hidemenu' => 'true',
            '--content' => 'Body',
            '--alias' => 'custom',
            '--context_key' => 'web'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Resource created successfully', $output);
    }

    public function testExecuteWithJsonOption()
    {
        $response = $this->createProcessorResponse([
            'success' => true,
            'object' => ['id' => 7]
        ]);

        $this->modx->method('runProcessor')
            ->willReturn($response);

        $this->commandTester->execute([
            'pagetitle' => 'Json Page',
            '--json' => true
        ]);

        $output = $this->commandTester->getDisplay();
        $decoded = json_decode($output, true);
        $this->assertNotNull($decoded);
        $this->assertTrue($decoded['success']);
    }

    public function testExecuteWithFailedResponse()
    {
        $response = $this->createProcessorResponse([
            'success' => false,
            'message' => 'Error creating resource'
        ], true);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($response);

        $this->commandTester->execute([
            'pagetitle' => 'Bad Page'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Failed to create resource', $output);
        $this->assertStringContainsString('Error creating resource', $output);
    }

    private function createProcessorResponse(array $payload, bool $isError = false)
    {
        $response = $this->getMockBuilder('MODX\Revolution\Processors\ProcessorResponse')
            ->disableOriginalConstructor()
            ->getMock();
        $response->method('getResponse')
            ->willReturn(json_encode($payload));
        $response->method('isError')
            ->willReturn($isError);

        return $response;
    }
}
