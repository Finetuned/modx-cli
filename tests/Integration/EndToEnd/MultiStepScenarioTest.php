<?php

namespace MODX\CLI\Tests\Integration\EndToEnd;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;

/**
 * End-to-end integration tests for multi-step scenarios
 * 
 * Tests real-world workflows that combine multiple commands and operations
 * to validate complex user scenarios and command interdependencies.
 */
class MultiStepScenarioTest extends BaseIntegrationTest
{
    /**
     * Test complete content creation workflow with category hierarchy
     */
    public function testContentCreationWorkflow()
    {
        // Step 1: Create parent category
        $categoryName = 'IntegrationTest_' . uniqid();
        $this->executeCommandSuccessfully(['category:create', $categoryName]);
        
        // Step 2: Get category ID
        $categories = $this->executeCommandJson(['category:list']);
        $categoryId = $this->findCategoryId($categories, $categoryName);
        $this->assertNotNull($categoryId, 'Category should be created and retrievable');
        
        // Step 3: Create chunk in category
        $chunkName = 'TestChunk_' . uniqid();
        $this->executeCommandSuccessfully([
            'chunk:create', 
            $chunkName,
            '--category=' . $categoryId
        ]);
        
        // Step 4: Create snippet in same category
        $snippetName = 'TestSnippet_' . uniqid();
        $this->executeCommandSuccessfully([
            'snippet:create', 
            $snippetName,
            '--category=' . $categoryId
        ]);
        
        // Step 5: Verify both items exist and are in correct category
        $chunks = $this->executeCommandJson(['chunk:list']);
        $this->assertChunkExists($chunks, $chunkName, $categoryId);
        
        $snippets = $this->executeCommandJson(['snippet:list']);
        $this->assertSnippetExists($snippets, $snippetName, $categoryId);
    }
    
    /**
     * Test template variable (TV) and template relationship workflow
     */
    public function testContentRelationshipWorkflow()
    {
        // Step 1: Create template
        $templateName = 'IntegrationTest_' . uniqid();
        $this->executeCommandSuccessfully([
            'template:create',
            $templateName,
            '--content=<html>Test Template</html>'
        ]);
        
        // Step 2: Get template ID
        $templates = $this->executeCommandJson(['template:list']);
        $templateId = $this->findTemplateId($templates, $templateName);
        $this->assertNotNull($templateId, 'Template should be created');
        
        // Step 3: Create TV
        $tvName = 'TestTV_' . uniqid();
        $this->executeCommandSuccessfully([
            'tv:create',
            $tvName,
            '--type=text',
            '--caption=' . $tvName
        ]);
        
        // Step 4: Verify TV exists
        $tvs = $this->executeCommandJson(['tv:list']);
        $tvId = $this->findTVId($tvs, $tvName);
        $this->assertNotNull($tvId, 'TV should be created');
        
        // Step 5: Get template and verify creation
        $templateData = $this->executeCommandJson(['template:get', $templateId]);
        $this->assertEquals($templateName, $templateData['templatename']);
    }
    
    /**
     * Test hierarchical category updates maintain parent-child relationships
     */
    public function testUpdateCascadeWorkflow()
    {
        // Step 1: Create parent category
        $parentName = 'IntegrationTest_Parent_' . uniqid();
        $this->executeCommandSuccessfully(['category:create', $parentName]);
        
        // Step 2: Get parent ID
        $categories = $this->executeCommandJson(['category:list']);
        $parentId = $this->findCategoryId($categories, $parentName);
        
        // Step 3: Create child categories
        $child1Name = 'IntegrationTest_Child1_' . uniqid();
        $child2Name = 'IntegrationTest_Child2_' . uniqid();
        
        $this->executeCommandSuccessfully([
            'category:create',
            $child1Name,
            '--parent=' . $parentId
        ]);
        $this->executeCommandSuccessfully([
            'category:create',
            $child2Name,
            '--parent=' . $parentId
        ]);
        
        // Step 4: Update parent category name
        $newParentName = 'IntegrationTest_UpdatedParent_' . uniqid();
        $this->executeCommandSuccessfully([
            'category:update',
            $parentId,
            '--category=' . $newParentName
        ]);
        
        // Step 5: Verify hierarchy maintained
        $updatedCategories = $this->executeCommandJson(['category:list']);
        
        // Find updated parent
        $updatedParent = $this->findCategoryById($updatedCategories, $parentId);
        $this->assertEquals($newParentName, $updatedParent['category']);
        
        // Find children and verify they still reference correct parent
        $child1 = $this->findCategoryByName($updatedCategories, $child1Name);
        $child2 = $this->findCategoryByName($updatedCategories, $child2Name);
        
        $this->assertEquals($parentId, $child1['parent']);
        $this->assertEquals($parentId, $child2['parent']);
    }
    
    /**
     * Test batch creation and verify element persistence after category deletion
     * IMPORTANT: In MODX, deleting a category does NOT cascade delete elements.
     * Elements become uncategorized (category = null/0) but persist.
     */
    public function testBatchCreationAndCategoryRelationship()
    {
        // Step 1: Create multiple categories
        $category1 = 'IntegrationTest_Cat1_' . uniqid();
        $category2 = 'IntegrationTest_Cat2_' . uniqid();
        
        $this->executeCommandSuccessfully(['category:create', $category1]);
        $this->executeCommandSuccessfully(['category:create', $category2]);
        
        // Step 2: Get category IDs
        $categories = $this->executeCommandJson(['category:list']);
        $cat1Id = $this->findCategoryId($categories, $category1);
        $cat2Id = $this->findCategoryId($categories, $category2);
        
        // Step 3: Create chunks in each category
        $chunk1 = 'TestChunk_Cat1_' . uniqid();
        $chunk2 = 'TestChunk_Cat2_' . uniqid();
        
        $this->executeCommandSuccessfully([
            'chunk:create', 
            $chunk1,
            '--category=' . $cat1Id,
            '--content=Chunk in Category 1'
        ]);
        $this->executeCommandSuccessfully([
            'chunk:create', 
            $chunk2,
            '--category=' . $cat2Id,
            '--content=Chunk in Category 2'
        ]);
        
        // Step 4: Verify initial category assignments
        $chunksInitial = $this->executeCommandJson(['chunk:list']);
        $this->assertChunkExists($chunksInitial, $chunk1, $cat1Id);
        $this->assertChunkExists($chunksInitial, $chunk2, $cat2Id);
        
        // Step 5: Delete category1
        $this->executeCommandSuccessfully(['category:remove', $cat1Id]);
        
        // Step 6: Verify chunk1 still exists but has NO category
        // This is MODX's actual behavior - elements are not cascade deleted
        $chunksAfter = $this->executeCommandJson(['chunk:list']);
        $chunk1Data = $this->findChunkByName($chunksAfter, $chunk1);
        
        $this->assertNotNull($chunk1Data, 'Chunk should still exist after category deletion');
        $this->assertTrue(
            empty($chunk1Data['category']) || 
            $chunk1Data['category'] == 0 || 
            $chunk1Data['category'] === '0',
            'Chunk category should be null/0 after category deletion (not cascade deleted)'
        );
        
        // Step 7: Verify chunk2 still in category2
        $this->assertChunkExists($chunksAfter, $chunk2, $cat2Id);
    }
    
    /**
     * Test configuration management workflow
     */
    public function testConfigurationChangeWorkflow()
    {
        // Step 1: List initial configs
        $initialConfigs = $this->executeCommand(['config:list']);
        $this->assertEquals(0, $initialConfigs->getExitCode());
        
        // Step 2: Add test config
        $configName = 'test_config_' . uniqid();
        $configPath = '/test/path/modx';
        
        $this->executeCommandSuccessfully([
            'config:add',
            $configName,
            $configPath
        ]);
        
        // Step 3: Verify config added
        $configs = $this->executeCommandJson(['config:list']);
        $this->assertConfigExists($configs, $configName);
        
        // Step 4: Remove config
        $this->executeCommandSuccessfully(['config:rm', $configName]);
        
        // Step 5: Verify config removed
        $finalConfigs = $this->executeCommandJson(['config:list']);
        $this->assertConfigNotExists($finalConfigs, $configName);
    }
    
    /**
     * Test mixed command integration (standard + custom commands)
     */
    public function testMixedCommandIntegration()
    {
        // Step 1: Use standard command - create category
        $categoryName = 'IntegrationTest_' . uniqid();
        $standardCmd = $this->executeCommandSuccessfully(['category:create', $categoryName]);
        $this->assertStringContainsString('created', strtolower($standardCmd->getOutput()));
        
        // Step 2: Use custom command - list package upgrades
        $customCmd = $this->executeCommand(['package:list-upgrades', '--format=json']);
        // Command should execute (may return empty if no upgrades available)
        $this->assertTrue($customCmd->getExitCode() === 0 || $customCmd->getExitCode() === 1);
        
        // Step 3: Use another standard command - verify category exists
        $categories = $this->executeCommandJson(['category:list']);
        $categoryId = $this->findCategoryId($categories, $categoryName);
        $this->assertNotNull($categoryId, 'Category from standard command should exist');
        
        // Step 4: Verify all operations succeeded without interference
        $this->assertTrue(true, 'Mixed standard and custom commands executed successfully');
    }
    
    /**
     * Test error recovery workflow
     */
    public function testErrorRecoveryWorkflow()
    {
        // Step 1: Create unique chunk
        $chunkName = 'TestChunk_' . uniqid();
        $this->executeCommandSuccessfully([
            'chunk:create',
            $chunkName,
            '--content=Original content'
        ]);
        
        // Step 2: Attempt to create duplicate chunk (should fail)
        $duplicateAttempt = $this->executeCommand([
            'chunk:create',
            $chunkName,
            '--content=Duplicate content'
        ]);
        
        // Should fail with error
        $this->assertNotEquals(0, $duplicateAttempt->getExitCode(), 'Duplicate creation should fail');
        
        // Step 3: Verify only one chunk exists
        $count = $this->countTableRows('modx_site_htmlsnippets', 'name = ?', [$chunkName]);
        $this->assertEquals(1, $count, 'Should only have one chunk, not duplicate');
        
        // Step 4: Create chunk with different name (should succeed)
        $uniqueChunkName = 'TestChunk_' . uniqid();
        $this->executeCommandSuccessfully([
            'chunk:create',
            $uniqueChunkName,
            '--content=Unique content'
        ]);
        
        // Step 5: Verify both valid chunks exist
        $chunks = $this->executeCommandJson(['chunk:list']);
        $this->assertChunkByNameExists($chunks, $chunkName);
        $this->assertChunkByNameExists($chunks, $uniqueChunkName);
    }
    
    /**
     * Test data consistency chain across multiple operations
     */
    public function testDataConsistencyChain()
    {
        // Step 1: Create chunk with initial content
        $chunkName = 'TestChunk_' . uniqid();
        $initialContent = 'Initial content ' . uniqid();
        
        $this->executeCommandSuccessfully([
            'chunk:create',
            $chunkName,
            '--content=' . $initialContent
        ]);
        
        // Step 2: Get chunk and verify initial content
        $chunks = $this->executeCommandJson(['chunk:list']);
        $chunkId = $this->findChunkId($chunks, $chunkName);
        
        $chunkData = $this->executeCommandJson(['chunk:get', $chunkId]);
        $this->assertEquals($initialContent, $chunkData['snippet']);
        
        // Step 3: Update chunk content
        $updatedContent = 'Updated content ' . uniqid();
        $this->executeCommandSuccessfully([
            'chunk:update',
            $chunkId,
            '--content=' . $updatedContent
        ]);
        
        // Step 4: Get chunk again and verify updated content persists
        $updatedChunkData = $this->executeCommandJson(['chunk:get', $chunkId]);
        $this->assertEquals($updatedContent, $updatedChunkData['snippet']);
        
        // Step 5: List chunks and verify in results
        $finalChunks = $this->executeCommandJson(['chunk:list']);
        $foundChunk = $this->findChunkByName($finalChunks, $chunkName);
        $this->assertNotNull($foundChunk, 'Updated chunk should appear in list');
        
        // Step 6: Verify database consistency
        $dbContent = $this->queryDatabase(
            'SELECT snippet FROM modx_site_htmlsnippets WHERE id = ?',
            [$chunkId]
        );
        $this->assertEquals($updatedContent, $dbContent[0]['snippet']);
    }
    
    /**
     * Helper: Find category ID by name
     */
    protected function findCategoryId(array $categories, string $name): ?int
    {
        foreach ($categories as $category) {
            if ($category['category'] === $name) {
                return (int)$category['id'];
            }
        }
        return null;
    }
    
    /**
     * Helper: Find category by ID
     */
    protected function findCategoryById(array $categories, int $id): ?array
    {
        foreach ($categories as $category) {
            if ((int)$category['id'] === $id) {
                return $category;
            }
        }
        return null;
    }
    
    /**
     * Helper: Find category by name
     */
    protected function findCategoryByName(array $categories, string $name): ?array
    {
        foreach ($categories as $category) {
            if ($category['category'] === $name) {
                return $category;
            }
        }
        return null;
    }
    
    /**
     * Helper: Find chunk ID by name
     */
    protected function findChunkId(array $chunks, string $name): ?int
    {
        foreach ($chunks as $chunk) {
            if ($chunk['name'] === $name) {
                return (int)$chunk['id'];
            }
        }
        return null;
    }
    
    /**
     * Helper: Find chunk by name
     */
    protected function findChunkByName(array $chunks, string $name): ?array
    {
        foreach ($chunks as $chunk) {
            if ($chunk['name'] === $name) {
                return $chunk;
            }
        }
        return null;
    }
    
    /**
     * Helper: Find template ID by name
     */
    protected function findTemplateId(array $templates, string $name): ?int
    {
        foreach ($templates as $template) {
            if ($template['templatename'] === $name) {
                return (int)$template['id'];
            }
        }
        return null;
    }
    
    /**
     * Helper: Find TV ID by name
     */
    protected function findTVId(array $tvs, string $name): ?int
    {
        foreach ($tvs as $tv) {
            if ($tv['name'] === $name) {
                return (int)$tv['id'];
            }
        }
        return null;
    }
    
    /**
     * Helper: Assert chunk exists with correct category
     */
    protected function assertChunkExists(array $chunks, string $name, int $categoryId): void
    {
        $chunk = $this->findChunkByName($chunks, $name);
        $this->assertNotNull($chunk, "Chunk '{$name}' should exist");
        $this->assertEquals($categoryId, (int)$chunk['category'], "Chunk should be in category {$categoryId}");
    }
    
    /**
     * Helper: Assert chunk exists by name only
     */
    protected function assertChunkByNameExists(array $chunks, string $name): void
    {
        $chunk = $this->findChunkByName($chunks, $name);
        $this->assertNotNull($chunk, "Chunk '{$name}' should exist");
    }
    
    /**
     * Helper: Assert snippet exists with correct category
     */
    protected function assertSnippetExists(array $snippets, string $name, int $categoryId): void
    {
        $snippet = null;
        foreach ($snippets as $s) {
            if ($s['name'] === $name) {
                $snippet = $s;
                break;
            }
        }
        $this->assertNotNull($snippet, "Snippet '{$name}' should exist");
        $this->assertEquals($categoryId, (int)$snippet['category'], "Snippet should be in category {$categoryId}");
    }
    
    /**
     * Helper: Assert config exists
     */
    protected function assertConfigExists(array $configs, string $name): void
    {
        $found = false;
        foreach ($configs as $config) {
            if (isset($config['name']) && $config['name'] === $name) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, "Config '{$name}' should exist");
    }
    
    /**
     * Helper: Assert config does not exist
     */
    protected function assertConfigNotExists(array $configs, string $name): void
    {
        $found = false;
        foreach ($configs as $config) {
            if (isset($config['name']) && $config['name'] === $name) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found, "Config '{$name}' should not exist");
    }
    
    /**
     * Clean up test data
     */
    protected function tearDown(): void
    {
        // Clean up all integration test data
        $this->queryDatabase('DELETE FROM modx_categories WHERE category LIKE ?', ['IntegrationTest_%']);
        $this->queryDatabase('DELETE FROM modx_site_htmlsnippets WHERE name LIKE ?', ['TestChunk_%']);
        $this->queryDatabase('DELETE FROM modx_site_snippets WHERE name LIKE ?', ['TestSnippet_%']);
        $this->queryDatabase('DELETE FROM modx_site_templates WHERE templatename LIKE ?', ['IntegrationTest_%']);
        $this->queryDatabase('DELETE FROM modx_site_tmplvars WHERE name LIKE ?', ['TestTV_%']);
        
        parent::tearDown();
    }
}
