<?php

namespace MODX\CLI\Tests\Command\System\Locks;

use MODX\CLI\Command\System\Locks\Remove;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Tester\CommandTester;

class RemoveTest extends BaseTest
{
    private function makeCommandTester($registry): CommandTester
    {
        $modx = $this->createMock('MODX\Revolution\modX');
        $modx->method('getService')
            ->with('registry', 'registry.modRegistry')
            ->willReturn($registry);

        $command = new Remove();
        $command->modx = $modx;
        $command->setHelperSet(new HelperSet([new QuestionHelper()]));

        return new CommandTester($command);
    }

    public function testExecuteWithMissingLock()
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

    public function testExecuteWithForceRemovesLock()
    {
        [$registry, $locks] = $this->makeRegistryMock();
        $locks->method('read')->with(['lock1'])->willReturn([
            'lock1' => [
                'user' => 'bob',
                'message' => 'Working',
                'timestamp' => 1700000000
            ]
        ]);
        $locks->expects($this->once())
            ->method('subscribe')
            ->with(['lock1']);
        $locks->expects($this->once())
            ->method('remove');

        $tester = $this->makeCommandTester($registry);
        $tester->execute([
            'key' => 'lock1',
            '--force' => true
        ]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString("Lock with key 'lock1' removed successfully", $output);
    }

    public function testExecuteWithoutForceAborts()
    {
        [$registry, $locks] = $this->makeRegistryMock();
        $locks->method('read')->with(['lock1'])->willReturn([
            'lock1' => [
                'user' => 'bob',
                'message' => 'Working',
                'timestamp' => 1700000000
            ]
        ]);
        $locks->expects($this->never())->method('remove');

        $tester = $this->makeCommandTester($registry);
        $tester->setInputs(['no']);
        $tester->execute([
            'key' => 'lock1'
        ]);

        $output = $tester->getDisplay();
        $this->assertStringContainsString('Operation aborted', $output);
    }

    private function makeRegistryMock(): array
    {
        $locks = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['connect', 'read', 'subscribe', 'remove'])
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
