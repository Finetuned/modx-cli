<?php

namespace MODX\CLI\Tests\Integration\Fixtures;

/**
 * Provides sample data for populating test MODX instances
 */
class SampleData
{
    /**
     * Get sample categories for testing
     * 
     * @return array Array of category data
     */
    public static function getCategories(): array
    {
        return [
            ['category' => 'Test Category 1', 'parent' => 0, 'rank' => 0],
            ['category' => 'Test Category 2', 'parent' => 0, 'rank' => 1],
            ['category' => 'Nested Category 1', 'parent' => 1, 'rank' => 0],
            ['category' => 'Nested Category 2', 'parent' => 1, 'rank' => 1],
            ['category' => 'Test Category 3', 'parent' => 0, 'rank' => 2],
        ];
    }

    /**
     * Get sample chunks for testing
     * 
     * @return array Array of chunk data
     */
    public static function getChunks(): array
    {
        return [
            [
                'name' => 'TestChunk1',
                'description' => 'Test chunk for integration testing',
                'snippet' => '<div class="test">[[+content]]</div>',
                'category' => 1,
            ],
            [
                'name' => 'TestChunk2',
                'description' => 'Another test chunk',
                'snippet' => '<p>[[+text]]</p>',
                'category' => 1,
            ],
            [
                'name' => 'HeaderChunk',
                'description' => 'Header chunk',
                'snippet' => '<header><h1>[[++site_name]]</h1></header>',
                'category' => 2,
            ],
            [
                'name' => 'FooterChunk',
                'description' => 'Footer chunk',
                'snippet' => '<footer><p>&copy; [[++site_name]]</p></footer>',
                'category' => 2,
            ],
        ];
    }

    /**
     * Get sample snippets for testing
     * 
     * @return array Array of snippet data
     */
    public static function getSnippets(): array
    {
        return [
            [
                'name' => 'TestSnippet1',
                'description' => 'Test snippet for integration testing',
                'snippet' => '<?php return "Hello World";',
                'category' => 1,
            ],
            [
                'name' => 'TestSnippet2',
                'description' => 'Another test snippet',
                'snippet' => '<?php return $modx->getOption("test_param", $scriptProperties, "default");',
                'category' => 1,
            ],
            [
                'name' => 'GetResourcesTest',
                'description' => 'Test getResources-like snippet',
                'snippet' => '<?php return json_encode($modx->getCollection("modResource"));',
                'category' => 2,
            ],
        ];
    }

    /**
     * Get sample templates for testing
     * 
     * @return array Array of template data
     */
    public static function getTemplates(): array
    {
        return [
            [
                'templatename' => 'TestTemplate1',
                'description' => 'Test template for integration testing',
                'content' => '<!DOCTYPE html><html><head><title>[[*pagetitle]]</title></head><body>[[*content]]</body></html>',
                'category' => 1,
            ],
            [
                'templatename' => 'TestTemplate2',
                'description' => 'Another test template',
                'content' => '<!DOCTYPE html><html><head><title>[[*pagetitle]]</title></head><body><div class="wrapper">[[*content]]</div></body></html>',
                'category' => 2,
            ],
        ];
    }

    /**
     * Get sample template variables for testing
     * 
     * @return array Array of TV data
     */
    public static function getTemplateVars(): array
    {
        return [
            [
                'name' => 'testTV1',
                'caption' => 'Test TV 1',
                'description' => 'Test template variable',
                'type' => 'text',
                'elements' => '',
                'default_text' => 'default value',
                'category' => 1,
            ],
            [
                'name' => 'testTV2',
                'caption' => 'Test TV 2',
                'description' => 'Another test template variable',
                'type' => 'textarea',
                'elements' => '',
                'default_text' => '',
                'category' => 1,
            ],
        ];
    }

    /**
     * Get sample resources for testing
     * 
     * @return array Array of resource data
     */
    public static function getResources(): array
    {
        return [
            [
                'pagetitle' => 'Home',
                'longtitle' => 'Home Page',
                'description' => 'The home page',
                'alias' => 'index',
                'published' => 1,
                'parent' => 0,
                'isfolder' => 0,
                'template' => 1,
                'content' => '<h1>Welcome</h1><p>This is the home page.</p>',
            ],
            [
                'pagetitle' => 'About',
                'longtitle' => 'About Us',
                'description' => 'About page',
                'alias' => 'about',
                'published' => 1,
                'parent' => 0,
                'isfolder' => 0,
                'template' => 1,
                'content' => '<h1>About Us</h1><p>This is the about page.</p>',
            ],
            [
                'pagetitle' => 'Contact',
                'longtitle' => 'Contact Us',
                'description' => 'Contact page',
                'alias' => 'contact',
                'published' => 1,
                'parent' => 0,
                'isfolder' => 0,
                'template' => 1,
                'content' => '<h1>Contact Us</h1><p>This is the contact page.</p>',
            ],
        ];
    }

    /**
     * Get all sample data organized by type
     * 
     * @return array Array of all sample data organized by type
     */
    public static function getAllData(): array
    {
        return [
            'categories' => self::getCategories(),
            'chunks' => self::getChunks(),
            'snippets' => self::getSnippets(),
            'templates' => self::getTemplates(),
            'tvs' => self::getTemplateVars(),
            'resources' => self::getResources(),
        ];
    }

    /**
     * Load sample data into a MODX instance
     * This method uses the MODX CLI to create the sample data
     * 
     * @param string $modxPath Path to MODX installation
     * @param string $binPath Path to MODX CLI binary
     * @return array Results of data loading operations
     */
    public static function loadIntoInstance(string $modxPath, string $binPath): array
    {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        // Note: This is a placeholder for actual implementation
        // In a real scenario, this would use Symfony Process to execute
        // MODX CLI commands to create the sample data
        
        return $results;
    }
}
