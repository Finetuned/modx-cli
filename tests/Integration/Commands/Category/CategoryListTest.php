<?php

namespace MODX\CLI\Tests\Integration\Commands\Category;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * Integration test for category:list command
 * 
 * This test executes the actual CLI command against a real MODX instance
 * and verifies the output and behavior
 */
class CategoryListTest extends BaseIntegrationTest
{
    /**
     * Test that category:list command executes successfully
     */
    public function testCategoryListExecutesSuccessfully()
    {
        $process = $this->executeCommand(['category:list']);
        
        $this->assertEquals(
            0,
            $process->getExitCode(),
            'category:list command should execute successfully'
        );
        
        $output = $process->getOutput();
        $this->assertStringContainsString('category', strtolower($output));
    }

    /**
     * Test that category:list returns valid JSON when --json flag is used
     */
    public function testCategoryListReturnsValidJson()
    {
        $data = $this->executeCommandJson(['category:list']);
        
        $this->assertIsArray($data, 'JSON output should be an array');
        
        // If categories exist, verify structure
        if (!empty($data)) {
            $firstCategory = $data[0];
            $this->assertArrayHasKey('category', $firstCategory);
        }
    }

    /**
     * Test that category:list command shows error message appropriately
     */
    public function testCategoryListHandlesEmptyResults()
    {
        $process = $this->executeCommand(['category:list']);
        
        $this->assertEquals(0, $process->getExitCode());
        
        $output = $process->getOutput();
        // Should either show categories or indicate no results
        $this->assertTrue(
            str_contains($output, 'displaying') || 
            str_contains($output, 'Category'),
            'Output should indicate results or empty state'
        );
    }

    /**
     * Test database state matches command output
     */
    public function testCategoryListMatchesDatabaseState()
    {
        // Count categories in database
        $count = $this->countTableRows('modx_categories');
        
        // Get categories from command
        $data = $this->executeCommandJson(['category:list']);
        
        $this->assertEquals(
            $count,
            count($data),
            'Command output should match database category count'
        );
    }

    /**
     * Test command execution performance
     */
    public function testCategoryListPerformance()
    {
        $startTime = microtime(true);
        
        $process = $this->executeCommand(['category:list']);
        
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;
        
        $this->assertEquals(0, $process->getExitCode());
        $this->assertLessThan(
            5.0,
            $executionTime,
            'category:list should complete within 5 seconds'
        );
    }
}
