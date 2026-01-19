<?php

namespace MODX\CLI\Tests\Command\Package\Provider;

use MODX\CLI\Command\Package\Provider\PackagesList;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class PackagesListTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');

        // Create the command
        $this->command = new PackagesList();
        $this->command->modx = $this->modx;

        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Workspace\\Packages\\Rest\\GetList', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('package:provider:packages', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Get a list of packages from a provider in MODX', $this->command->getDescription());
    }

    public function testConfigureHasArgumentsAndOptions()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasArgument('provider'));
        $this->assertTrue($definition->getArgument('provider')->isRequired());

        $this->assertTrue($definition->hasOption('query'));
        $this->assertTrue($definition->getOption('query')->isValueRequired());
        $this->assertTrue($definition->hasOption('category'));
        $this->assertTrue($definition->getOption('category')->isValueRequired());
    }

    public function testExecuteWithFilters()
    {
        $response = $this->createProcessorResponse([
            'success' => true,
            'total' => 1,
            'results' => [
                [
                    'signature' => 'mypkg-1.2.0-pl',
                    'name' => 'mypkg',
                    'version' => '1.2.0',
                    'release' => 'pl',
                    'installed' => true
                ]
            ]
        ]);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Workspace\\Packages\\Rest\\GetList',
                $this->callback(function ($properties) {
                    return $properties['provider'] === '1'
                        && $properties['query'] === 'mypkg'
                        && $properties['category'] === 'utilities';
                }),
                $this->anything()
            )
            ->willReturn($response);

        $this->commandTester->execute([
            'provider' => '1',
            '--query' => 'mypkg',
            '--category' => 'utilities'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('mypkg', $output);
        $this->assertStringContainsString('Yes', $output);
    }

    public function testExecuteWithJsonOption()
    {
        $response = $this->createProcessorResponse([
            'success' => true,
            'total' => 1,
            'results' => [
                [
                    'signature' => 'mypkg-1.2.0-pl',
                    'name' => 'mypkg',
                    'version' => '1.2.0',
                    'release' => 'pl',
                    'installed' => true
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
