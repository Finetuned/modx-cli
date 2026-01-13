<?php namespace MODX\CLI\Tests\Integration;

use MODX\CLI\Xdom;

/**
 * Integration-focused Xdom test: relies on a real MODX installation and tests
 * Xdom's interaction with real MODX bootstrap.
 */
class XdomIntegrationTest extends BaseIntegrationTest
{
    /** @var Xdom */
    private $xdom;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Initialize Xdom with real MODX instance
        // Note: Xdom extends modX, so it requires MODX to be loaded
        $this->loadMODX();
        $this->xdom = new Xdom();
    }
    
    /**
     * Load MODX instance for Xdom testing
     */
    private function loadMODX(): void
    {
        // Check if modX class is already loaded
        if (class_exists('modX', false)) {
            return;
        }
        
        // Load MODX configuration
        $configPath = $this->modxPath . '/config.core.php';
        if (!file_exists($configPath)) {
            $this->markTestSkipped("Skipped: MODX config.core.php not found at {$configPath}. See tests/Integration/README.md#skipped-tests.");
        }
        
        require_once $configPath;
        
        if (!defined('MODX_CORE_PATH')) {
            $this->markTestSkipped('Skipped: MODX_CORE_PATH not defined after loading config.core.php. See tests/Integration/README.md#skipped-tests.');
        }
        
        // Load MODX class
        $modxClassPath = MODX_CORE_PATH . 'model/modx/modx.class.php';
        if (!file_exists($modxClassPath)) {
            $this->markTestSkipped("Skipped: MODX class not found at {$modxClassPath}. See tests/Integration/README.md#skipped-tests.");
        }
        
        require_once $modxClassPath;
    }

    private function assertOutputMatches(array $input, $count = false): array
    {
        $output = $this->xdom->outputArray($input, $count);
        $this->assertIsString($output);

        $decoded = json_decode($output, true);
        $this->assertNotNull($decoded, 'Output is not valid JSON: ' . $output);
        $this->assertArrayHasKey('total', $decoded);
        $this->assertArrayHasKey('results', $decoded);
        $this->assertArrayHasKey('success', $decoded);

        $expectedCount = $count === false ? count($input) : $count;
        $this->assertSame((string) $expectedCount, $decoded['total']);
        $this->assertEquals($input, $decoded['results']);
        $this->assertTrue($decoded['success']);

        return $decoded;
    }

    public function testOutputArrayWithSimpleArray()
    {
        $this->assertOutputMatches([['key' => 'web']]);
    }

    public function testOutputArrayWithEmptyArray()
    {
        $this->assertOutputMatches([]);
    }

    public function testOutputArrayWithExplicitCount()
    {
        $this->assertOutputMatches([['a' => 1], ['a' => 2]], 10);
    }

    public function testOutputArrayWithAutomaticCount()
    {
        $this->assertOutputMatches([['a' => 1], ['a' => 2]]);
    }

    public function testOutputArraySuccessFieldIsAlwaysTrue()
    {
        $decoded = $this->assertOutputMatches([]);
        $this->assertTrue($decoded['success']);
    }

    public function testOutputArrayWithMixedDataTypes()
    {
        $data = [['int' => 1, 'float' => 1.5, 'bool' => true, 'null' => null]];
        $decoded = $this->assertOutputMatches($data);
        $this->assertSame($data, $decoded['results']);
    }

}
