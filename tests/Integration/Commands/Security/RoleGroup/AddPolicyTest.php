<?php

namespace MODX\CLI\Tests\Integration\Commands\Security\RoleGroup;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for security:rolegroup:addpolicy command
 */
class AddPolicyTest extends BaseIntegrationTest
{
    protected string $groupsTable;
    protected string $templatesTable;

    protected function setUp(): void
    {
        parent::setUp();
        $this->groupsTable = $this->getTableName('access_policy_template_groups');
        $this->templatesTable = $this->getTableName('access_policy_templates');
    }

    public function testAddPolicyAssignsTemplateToGroup()
    {
        $groupName = 'integtest_group_' . uniqid();
        $templateName = 'integtest_template_' . uniqid();

        $this->queryDatabase(
            'INSERT INTO ' . $this->groupsTable . ' (name, description) VALUES (?, ?)',
            [$groupName, 'Integration test group']
        );

        $groupRow = $this->queryDatabase(
            'SELECT id FROM ' . $this->groupsTable . ' WHERE name = ?',
            [$groupName]
        );
        $this->assertNotEmpty($groupRow);
        $groupId = (int) $groupRow[0]['id'];

        $this->queryDatabase(
            'INSERT INTO ' . $this->templatesTable . ' (template_group, name, description, lexicon) VALUES (?, ?, ?, ?)',
            [0, $templateName, 'Integration template', 'permissions']
        );

        $data = $this->executeCommandJson([
            'security:rolegroup:addpolicy',
            $groupName,
            $templateName,
        ]);

        $this->assertTrue($data['success']);

        $templateRow = $this->queryDatabase(
            'SELECT template_group FROM ' . $this->templatesTable . ' WHERE name = ?',
            [$templateName]
        );
        $this->assertNotEmpty($templateRow);
        $this->assertSame($groupId, (int) $templateRow[0]['template_group']);

        $this->cleanupRecords($groupName, $templateName);
    }

    protected function cleanupRecords(string $groupName, string $templateName): void
    {
        $this->queryDatabase(
            'DELETE FROM ' . $this->templatesTable . ' WHERE name = ?',
            [$templateName]
        );
        $this->queryDatabase(
            'DELETE FROM ' . $this->groupsTable . ' WHERE name = ?',
            [$groupName]
        );
    }
}
