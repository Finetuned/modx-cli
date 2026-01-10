<?php

namespace MODX\CLI\Tests\Command\Security\RoleGroup;

use MODX\CLI\Command\Security\RoleGroup\RemovePolicy;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class RemovePolicyTest extends BaseTest
{
    protected $command;
    protected $commandTester;
    protected $modx;

    protected function setUp(): void
    {
        $this->modx = $this->createMock('MODX\Revolution\modX');

        $this->command = new RemovePolicy();
        $this->command->modx = $this->modx;

        $this->commandTester = new CommandTester($this->command);
    }

    public function testConfigureHasCorrectName()
    {
        $this->assertEquals('security:rolegroup:removepolicy', $this->command->getName());
    }

    public function testConfigureHasCorrectDescription()
    {
        $this->assertEquals('Remove a policy template from a role group', $this->command->getDescription());
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

    public function testExecuteWithPolicyNotInGroup()
    {
        $group = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get'])
            ->getMock();
        $group->method('get')->willReturnCallback(function ($field) {
            return $field === 'id' ? 5 : 'GroupName';
        });

        $policy = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get'])
            ->getMock();
        $policy->method('get')->willReturnCallback(function ($field) {
            if ($field === 'id') {
                return 11;
            }
            if ($field === 'template_group') {
                return 99;
            }
            return 'TemplateName';
        });

        $this->modx->expects($this->exactly(2))
            ->method('getObject')
            ->willReturnOnConsecutiveCalls($group, $policy);

        $this->commandTester->execute([
            'group' => 'GroupName',
            'policy' => 'TemplateName',
        ]);

        $this->assertEquals(1, $this->commandTester->getStatusCode());
        $this->assertStringContainsString(
            'Policy template is not assigned to the specified role group',
            $this->commandTester->getDisplay()
        );
    }

    public function testExecuteWithSuccess()
    {
        $group = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get'])
            ->getMock();
        $group->method('get')->willReturnCallback(function ($field) {
            return $field === 'id' ? 6 : 'GroupName';
        });

        $policy = $this->getMockBuilder(\stdClass::class)
            ->addMethods(['get', 'set', 'save'])
            ->getMock();
        $policy->method('get')->willReturnCallback(function ($field) {
            if ($field === 'id') {
                return 12;
            }
            if ($field === 'template_group') {
                return 6;
            }
            return 'TemplateName';
        });
        $policy->expects($this->once())
            ->method('set')
            ->with('template_group', 0);
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
