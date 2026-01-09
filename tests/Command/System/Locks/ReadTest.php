<?php

namespace MODX\CLI\Tests\Command\System\Locks;

use MODX\CLI\Command\System\Locks\Read;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class ReadTest extends BaseTest
{
    protected function makeCommandTester($registry): CommandTester
    {
        $modx = $this->createMock('MODX\Revolution\modX');
        $modx->method('getService')
            ->with('registry', 'registry.modRegistry')
            ->willReturn($registry);

        $command = new Read();
        $command->modx = $modx;

        return new CommandTester($command);
    }

    public function testExecuteWithNoLocks()
    {
        [$registry, $locks] = $this->makeRegistryMock();
        $locks->method('read')->with([''])->willReturn([]);

        $tester = $this->makeCommandTester($registry);
        $tester->execute([]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('No locks found', $output);
    }

    public function testExecuteWithMissingKey()
    {
        [$registry, $locks] = $this->makeRegistryMock();
        $locks->method('read')->with(['missing'])->willReturn([]);

        $tester = $this->makeCommandTester($registry);
        $tester->execute([
            'key' => 'missing'
        ]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString("Lock with key 'missing' not found", $output);
        $this->assertEquals(1, $tester->getStatusCode());
    }

    public function testExecuteWithJsonFormat()
    {
        [$registry, $locks] = $this->makeRegistryMock();
        $locks->method('read')->with(['lock1'])->willReturn([
            'lock1' => [
                'user' => 'bob',
                'message' => 'Working',
                'timestamp' => 1700000000
            ]
        ]);

        $tester = $this->makeCommandTester($registry);
        $tester->execute([
            'key' => 'lock1',
            '--format' => 'json'
        ]);

        $output = $tester->getDisplay();
        $decoded = json_decode($output, true);
        $this->assertNotNull($decoded);
        $this->assertEquals('bob', $decoded['lock1']['user']);
    }

    public function testExecuteWithJsonOption()
    {
        [$registry, $locks] = $this->makeRegistryMock();
        $locks->method('read')->with(['lock1'])->willReturn([
            'lock1' => [
                'user' => 'bob',
                'message' => 'Working',
                'timestamp' => 1700000000
            ]
        ]);

        $tester = $this->makeCommandTester($registry);
        $tester->execute([
            'key' => 'lock1',
            '--json' => true
        ]);

        $decoded = json_decode($tester->getDisplay(), true);
        $this->assertNotNull($decoded);
        $this->assertEquals('bob', $decoded['lock1']['user']);
    }

    private function makeRegistryMock(): array
    {
        $locks = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['connect', 'read'])
            ->getMock();
        $locks->method('connect');

        $registry = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['addRegister'])
            ->getMock();
        $registry->method('addRegister');
        $registry->locks = $locks;

        return [$registry, $locks];
    }
}
