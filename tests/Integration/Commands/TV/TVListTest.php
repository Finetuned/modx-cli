<?php

namespace MODX\CLI\Tests\Integration\Commands\TV;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for tv:list command
 */
class TVListTest extends BaseIntegrationTest
{
    /**
     * Test that tv:list executes successfully
     */
    public function testTVListExecutesSuccessfully()
    {
        $tvName = 'IntegrationTestTV_' . uniqid();
        
        // Create a test TV first
        $this->executeCommandSuccessfully([
            'tv:create',
            $tvName,
            '--type=text',
            '--caption=Test TV'
        ]);
        
        // List TVs
        $process = $this->executeCommandSuccessfully([
            'tv:list'
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString($tvName, $output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_tmplvars WHERE name = ?', [$tvName]);
    }

    /**
     * Test tv:list with JSON output
     */
    public function testTVListReturnsValidJson()
    {
        $tvName = 'IntegrationTestTV_' . uniqid();
        
        // Create test TV
        $this->executeCommandSuccessfully([
            'tv:create',
            $tvName,
            '--type=text'
        ]);
        
        // List with JSON
        $data = $this->executeCommandJson([
            'tv:list'
        ]);
        
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        
        // Find our test TV in the results
        $found = false;
        foreach ($data as $tv) {
            if ($tv['name'] === $tvName) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, "Created TV not found in list");
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_tmplvars WHERE name = ?', [$tvName]);
    }

    /**
     * Test tv:list filtering by category
     */
    public function testTVListFilterByCategory()
    {
        $categoryName = 'IntegrationTestCategory_' . uniqid();
        $tvName = 'IntegrationTestTV_' . uniqid();
        
        // Create category
        $this->executeCommandSuccessfully([
            'category:create',
            $categoryName
        ]);
        
        // Get category ID
        $catRows = $this->queryDatabase('SELECT id FROM modx_categories WHERE category = ?', [$categoryName]);
        $categoryId = $catRows[0]['id'];
        
        // Create TV in category
        $this->executeCommandSuccessfully([
            'tv:create',
            $tvName,
            '--type=text',
            '--category=' . $categoryId
        ]);
        
        // List TVs with category filter
        $process = $this->executeCommandSuccessfully([
            'tv:list',
            '--category=' . $categoryId
        ]);
        
        $output = $process->getOutput();
        $this->assertStringContainsString($tvName, $output);
        
        // Cleanup
        $this->queryDatabase('DELETE FROM modx_site_tmplvars WHERE name = ?', [$tvName]);
        $this->queryDatabase('DELETE FROM modx_categories WHERE id = ?', [$categoryId]);
    }

    /**
     * Test empty TV list
     */
    public function testTVListWhenEmpty()
    {
        // Remove all test TVs first
        $this->queryDatabase('DELETE FROM modx_site_tmplvars WHERE name LIKE ?', ['IntegrationTestTV_%']);
        
        $process = $this->executeCommandSuccessfully([
            'tv:list'
        ]);
        
        $output = $process->getOutput();
        $this->assertNotEmpty($output);
    }

    /**
     * Test tv:list with limit
     */
    public function testTVListWithLimit()
    {
        // Create multiple test TVs
        $tvNames = [];
        for ($i = 0; $i < 3; $i++) {
            $tvName = 'IntegrationTestTV_' . uniqid() . '_' . $i;
            $tvNames[] = $tvName;
            
            $this->executeCommandSuccessfully([
                'tv:create',
                $tvName,
                '--type=text'
            ]);
        }
        
        // List with limit
        $data = $this->executeCommandJson([
            'tv:list',
            '--limit=2'
        ]);
        
        $this->assertIsArray($data);
        
        // Cleanup
        foreach ($tvNames as $name) {
            $this->queryDatabase('DELETE FROM modx_site_tmplvars WHERE name = ?', [$name]);
        }
    }

    /**
     * Clean up test data
     */
    protected function tearDown(): void
    {
        $this->queryDatabase('DELETE FROM modx_site_tmplvars WHERE name LIKE ?', ['IntegrationTestTV_%']);
        $this->queryDatabase('DELETE FROM modx_categories WHERE category LIKE ?', ['IntegrationTestCategory_%']);
        parent::tearDown();
    }
}
