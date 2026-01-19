<?php

namespace MODX\CLI\Tests\Command\Package;

use MODX\CLI\Command\Package\Download;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;

class DownloadTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');

        // Create the command
        $this->command = new Download();
        $this->command->modx = $this->modx;
        $this->command->setHelperSet(new HelperSet([new QuestionHelper()]));

        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Workspace\\Packages\\Rest\\Download', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('package:download', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Download a package from the provider to MODX', $this->command->getDescription());
    }

    public function testConfigureHasSignatureArgument()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasArgument('signature'));
        $this->assertTrue($definition->getArgument('signature')->isRequired());
    }

    public function testConfigureHasForceOption()
    {
        $definition = $this->command->getDefinition();
        $this->assertTrue($definition->hasOption('force'));
        $this->assertFalse($definition->getOption('force')->acceptValue());
    }

    public function testExecuteWithSuccessfulResponse()
    {
        $upgradeableResponse = $this->createProcessorResponse([
            'results' => [
                [
                    'signature' => 'mypkg-1.0.0-pl',
                    'updateable' => true
                ]
            ]
        ]);
        $downloadResponse = $this->createProcessorResponse([
            'success' => true
        ]);

        $provider = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get', 'latest'])
            ->getMock();
        $provider->method('latest')
            ->with('mypkg-1.0.0-pl')
            ->willReturn([
                [
                    'signature' => 'mypkg-1.2.0-pl',
                    'location' => '/path/to/mypkg'
                ]
            ]);
        $provider->method('get')->willReturnMap([
            ['id', 12],
            ['name', 'Test Provider']
        ]);

        $packageObject = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get', 'getOne'])
            ->getMock();
        $packageObject->method('get')
            ->with('signature')
            ->willReturn('mypkg-1.0.0-pl');
        $packageObject->method('getOne')
            ->with('Provider')
            ->willReturn($provider);

        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('MODX\\Revolution\\Transport\\modTransportPackage', ['signature' => 'mypkg-1.0.0-pl'])
            ->willReturn($packageObject);

        $this->modx->expects($this->exactly(2))
            ->method('runProcessor')
            ->willReturnCallback(function ($processor, $properties = [], $options = []) use ($upgradeableResponse, $downloadResponse) {
                if ($processor === 'workspace/packages/getlist') {
                    $this->assertTrue($properties['newest_only']);
                    $this->assertEquals(100, $properties['limit']);
                    return $upgradeableResponse;
                }

                if ($processor === 'Workspace\\Packages\\Rest\\Download') {
                    $this->assertEquals(12, $properties['provider']);
                    $this->assertEquals('/path/to/mypkg::mypkg-1.2.0-pl', $properties['info']);
                    return $downloadResponse;
                }

                $this->fail('Unexpected processor call: ' . $processor);
            });

        $this->commandTester->execute([
            'signature' => 'mypkg-1.2.0-pl',
            '--force' => true
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Package mypkg-1.2.0-pl downloaded successfully', $output);
    }

    public function testExecuteWithJsonOption()
    {
        $upgradeableResponse = $this->createProcessorResponse([
            'results' => [
                [
                    'signature' => 'mypkg-1.0.0-pl',
                    'updateable' => true
                ]
            ]
        ]);
        $downloadResponse = $this->createProcessorResponse([
            'success' => true,
            'message' => 'Downloaded'
        ]);

        $provider = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get', 'latest'])
            ->getMock();
        $provider->method('latest')->willReturn([
            [
                'signature' => 'mypkg-1.2.0-pl',
                'location' => '/path/to/mypkg'
            ]
        ]);
        $provider->method('get')->willReturnMap([
            ['id', 12],
            ['name', 'Test Provider']
        ]);

        $packageObject = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get', 'getOne'])
            ->getMock();
        $packageObject->method('get')
            ->with('signature')
            ->willReturn('mypkg-1.0.0-pl');
        $packageObject->method('getOne')
            ->with('Provider')
            ->willReturn($provider);

        $this->modx->method('getObject')
            ->willReturn($packageObject);

        $this->modx->expects($this->exactly(2))
            ->method('runProcessor')
            ->willReturnCallback(function ($processor) use ($upgradeableResponse, $downloadResponse) {
                if ($processor === 'workspace/packages/getlist') {
                    return $upgradeableResponse;
                }
                if ($processor === 'Workspace\\Packages\\Rest\\Download') {
                    return $downloadResponse;
                }
                return null;
            });

        $this->commandTester->execute([
            'signature' => 'mypkg-1.2.0-pl',
            '--force' => true,
            '--json' => true
        ]);

        $output = $this->commandTester->getDisplay();
        $decoded = json_decode($output, true);
        $this->assertNotNull($decoded);
        $this->assertTrue($decoded['success']);
    }

    public function testExecuteWithMissingUpgradeablePackage()
    {
        $upgradeableResponse = $this->createProcessorResponse([
            'results' => [
                [
                    'signature' => 'otherpkg-1.0.0-pl',
                    'updateable' => true
                ]
            ]
        ]);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with('workspace/packages/getlist', $this->anything())
            ->willReturn($upgradeableResponse);

        $this->modx->expects($this->never())
            ->method('getObject');

        $this->commandTester->execute([
            'signature' => 'mypkg-1.2.0-pl',
            '--force' => true
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Package not found in upgradeable packages', $output);
        $this->assertStringContainsString('Operation aborted', $output);
    }

    public function testExecuteWithoutForcePromptsForConfirmation()
    {
        $upgradeableResponse = $this->createProcessorResponse([
            'results' => [
                [
                    'signature' => 'mypkg-1.0.0-pl',
                    'updateable' => true
                ]
            ]
        ]);
        $downloadResponse = $this->createProcessorResponse([
            'success' => true
        ]);

        $provider = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get', 'latest'])
            ->getMock();
        $provider->method('latest')->willReturn([
            [
                'signature' => 'mypkg-1.2.0-pl',
                'location' => '/path/to/mypkg'
            ]
        ]);
        $provider->method('get')->willReturnMap([
            ['id', 12],
            ['name', 'Test Provider']
        ]);

        $packageObject = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get', 'getOne'])
            ->getMock();
        $packageObject->method('get')
            ->with('signature')
            ->willReturn('mypkg-1.0.0-pl');
        $packageObject->method('getOne')
            ->with('Provider')
            ->willReturn($provider);

        $this->modx->method('getObject')
            ->willReturn($packageObject);

        $this->modx->expects($this->exactly(2))
            ->method('runProcessor')
            ->willReturnOnConsecutiveCalls($upgradeableResponse, $downloadResponse);

        $this->commandTester->setInputs(['yes']);
        $this->commandTester->execute([
            'signature' => 'mypkg-1.2.0-pl'
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('Package mypkg-1.2.0-pl downloaded successfully', $output);
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
