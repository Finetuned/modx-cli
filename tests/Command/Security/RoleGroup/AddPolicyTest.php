<?php

namespace MODX\CLI\Tests\Command\Security\RoleGroup;

use MODX\CLI\Command\Security\RoleGroup\AddPolicy;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class AddPolicyTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        $this->modx = $this->createMock('MODX\Revolution\modX');

        $this->command = new AddPolicy();
        $this->command->modx = $this->modx;

        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('security:rolegroup:addpolicy', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Add a policy template to a role group', $this->command->getDescription());
    }

    public function testExecuteWithMissingGroup()
    {
        $this->modx->expects($this->once())
            ->method('getObject')
            ->with('MODX\\Revolution\\modAccessPolicyTemplateGroup', ['name' => 'MissingGroup'])
            ->willReturn(null);

        $this->commandTester->execute([
            'group' => 'MissingGroup',
            'policy' => 'Template',
        ]);

        $this->assertEquals(1, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('Role group not found', $this->commandTester->getDisplay());
    }

    public function testExecuteWithMissingPolicyTemplate()
    {
        $group = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get'])
            ->getMock();
        $group->method('get')->willReturnCallback(function ($field) {
            return $field === 'id' ? 2 : 'TestGroup';
        });

        $this->modx->expects($this->exactly(2))
            ->method('getObject')
            ->willReturnOnConsecutiveCalls($group, null);

        $this->commandTester->execute([
            'group' => 'TestGroup',
            'policy' => 'MissingTemplate',
        ]);

        $this->assertEquals(1, $this->commandTester->getStatusCode());
        $this->assertStringContainsString('Policy template not found', $this->commandTester->getDisplay());
    }

    public function testExecuteWithSuccess()
    {
        $group = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get'])
            ->getMock();
        $group->method('get')->willReturnCallback(function ($field) {
            return $field === 'id' ? 3 : 'GroupName';
        });

        $policy = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get', 'set', 'save'])
            ->getMock();
        $policy->method('get')->willReturnCallback(function ($field) {
            return $field === 'id' ? 7 : 'TemplateName';
        });
        $policy->expects($this->once())
            ->method('set')
            ->with('template_group', 3);
        $policy->method('save')->willReturn(true);

        $this->modx->expects($this->exactly(2))
            ->method('getObject')
            ->willReturnOnConsecutiveCalls($group, $policy);

        $this->commandTester->execute([
            'group' => 'GroupName',
            'policy' => 'TemplateName',
            '--json' => true,
        ]);

        $decoded = json_decode($this->commandTester->getDisplay(), true);
        $this->assertTrue($decoded['success']);
        $this->assertEquals('GroupName', $decoded['group']);
        $this->assertEquals('TemplateName', $decoded['policy']);
    }
}
