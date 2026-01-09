<?php namespace MODX\CLI\Tests\Command\Package\Provider;

use MODX\CLI\Command\Package\Provider\GetList;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class GetListTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        // Create a mock MODX object
        $this->modx = $this->createMock('MODX\Revolution\modX');

        // Create the command
        $this->command = new GetList();
        $this->command->modx = $this->modx;

        // Create a command tester
        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectProcessorPath()
    {
        $processor = $this->getProtectedProperty($this->command, 'processor');
        $this->assertEquals('Workspace\\Providers\\GetList', $processor);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('package:provider:list', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Get a list of package providers in MODX', $this->command->getDescription());
    }

    public function testConfigureHasCorrectHeaders()
    {
        $headers = $this->getProtectedProperty($this->command, 'headers');
        $this->assertIsArray($headers);
        $this->assertContains('id', $headers);
        $this->assertContains('name', $headers);
        $this->assertContains('service_url', $headers);
        $this->assertContains('username', $headers);
        $this->assertContains('verified', $headers);
    }

    public function testExecuteWithJsonOption()
    {
        $response = $this->createProcessorResponse([
            'success' => true,
            'total' => 1,
            'results' => [
                [
                    'id' => 1,
                    'name' => 'modx.com',
                    'service_url' => 'https://modx.com',
                    'username' => 'user',
                    'verified' => 1
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
        $this->assertCount(1, $decoded['results']);
    }

    public function testExecuteWithPaginationOptions()
    {
        $response = $this->createProcessorResponse([
            'success' => true,
            'total' => 1,
            'results' => [
                [
                    'id' => 1,
                    'name' => 'modx.com',
                    'service_url' => 'https://modx.com',
                    'username' => 'user',
                    'verified' => 1
                ]
            ]
        ]);

        $this->modx->expects($this->once())
            ->method('runProcessor')
            ->with(
                'Workspace\\Providers\\GetList',
                $this->callback(function($properties) {
                    return $properties['limit'] === 5 && $properties['start'] === 10;
                }),
                $this->anything()
            )
            ->willReturn($response);

        $this->commandTester->execute([
            '--limit' => 5,
            '--start' => 10
        ]);

        $output = $this->commandTester->getDisplay();
        $this->assertStringContainsString('modx.com', $output);
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
