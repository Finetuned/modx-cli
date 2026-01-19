<?php

namespace MODX\CLI\Tests\Command\Package\Provider;

use MODX\CLI\Command\Package\Provider\CategoriesList;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class CategoriesListTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');

        // Create the command
        $this->command = new CategoriesList();
        $this->command->modx = $this->modx;

        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Workspace\\Packages\\Rest\\GetNodes', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('package:provider:categories', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Get a list of categories from a provider in MODX', $this->command->getDescription());
    }

    public function testConfigureHasProviderArgument()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasArgument('provider'));
        $this->assertTrue($definition->getArgument('provider')->isRequired());
    }

    public function testExecuteSetsRepositoryNodeAndProvider()
    {
        $response = $this->createProcessorResponse([
            'success' => true,
            'total' => 1,
            'results' => [
                [
                    'id' => 10,
                    'name' => 'Utilities',
                    'description' => 'Utilities category'
                ]
            ]
        ]);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Workspace\\Packages\\Rest\\GetNodes',
                $this->callback(function ($properties) {
                    return $properties['provider'] === '1'
                        && $properties['id'] === 'n_repository_0';
                }),
                $this->anything()
            )
            ->willReturn($response);

        $this->commandTester->execute([
            'provider' => '1'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Utilities', $output);
    }

    public function testExecuteWithJsonOption()
    {
        $response = $this->createProcessorResponse([
            'success' => true,
            'total' => 1,
            'results' => [
                [
                    'id' => 10,
                    'name' => 'Utilities',
                    'description' => 'Utilities category'
                ]
            ]
        ]);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->willReturn($response);

        $this->commandTester->execute([
            'provider' => '1',
            '--json' => true
        ]);

        $output = $this->commandTester->getDisplay();
        $decoded = json_decode($output, true);
        $this->assertNotNull($decoded);
        $this->assertEquals(1, $decoded['total']);
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
