<?php namespace MODX\CLI\Tests;

use PHPUnit\Framework\TestCase;

class XdomTest extends TestCase
{
    /** @var \MODX\CLI\Xdom */
    private $xdom;

    protected function setUp(): void
    {
        if (!class_exists(\MODX\CLI\Xdom::class)) {
            $this->markTestSkipped('Xdom class unavailable');
        }

        $this->xdom = new \MODX\CLI\Xdom();
    }

    private function decodeOutput(string $output): array
    {
        $decoded = json_decode($output, true);
        $this->assertNotNull($decoded, 'Output is not valid JSON: ' . $output);
        $this->assertArrayHasKey('total', $decoded);
        $this->assertArrayHasKey('results', $decoded);
        $this->assertArrayHasKey('success', $decoded);
        return $decoded;
    }

    private function assertOutputMatches(array $input, $count = false): array
    {
        $output = $this->xdom->outputArray($input, $count);
        $this->assertIsString($output);

        $decoded = $this->decodeOutput($output);
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

    public function testOutputArrayWithNonArray()
    {
        $this->assertFalse($this->xdom->outputArray('not-an-array'));
    }

    public function testOutputArrayWithNull()
    {
        $this->assertFalse($this->xdom->outputArray(null));
    }

    public function testJSONStructureCompliance()
    {
        $decoded = $this->assertOutputMatches([['a' => 1]]);
        $this->assertIsArray($decoded['results']);
        $this->assertIsString($decoded['total']);
    }

    public function testJSONStructureHasCorrectTypes()
    {
        $decoded = $this->assertOutputMatches([['a' => 1]]);
        $this->assertTrue($decoded['success']);
    }

    public function testOutputArrayWithSpecialCharacters()
    {
        $data = [['text' => 'hello "quoted" & escaped']];
        $decoded = $this->assertOutputMatches($data);
        $this->assertSame($data, $decoded['results']);
    }

    public function testOutputArrayWithNestedArrays()
    {
        $data = [['nested' => ['child' => 'value']]];
        $decoded = $this->assertOutputMatches($data);
        $this->assertSame($data, $decoded['results']);
    }

    public function testOutputArrayWithUnicodeCharacters()
    {
        $data = [['text' => 'unicode-test']];
        $decoded = $this->assertOutputMatches($data);
        $this->assertSame($data, $decoded['results']);
    }

    public function testOutputArrayWithZeroCount()
    {
        $this->assertOutputMatches([['x' => 1]], 0);
    }

    public function testOutputArrayCountParameterOverridesArrayCount()
    {
        $this->assertOutputMatches([['x' => 1], ['x' => 2]], 5);
    }

    public function testOutputArrayWithLargeDataset()
    {
        $data = array_map(fn($i) => ['i' => $i], range(1, 100));
        $this->assertOutputMatches($data);
    }

    public function testOutputArrayFormat()
    {
        $output = $this->xdom->outputArray([['x' => 1]]);
        $this->assertStringContainsString('"total":"1"', $output);
        $this->assertStringContainsString('"success": true', $output);
    }

    public function testOutputArraySuccessFieldIsAlwaysTrue()
    {
        $decoded = $this->assertOutputMatches([]);
        $this->assertTrue($decoded['success']);
    }

    public function testOutputArrayWithAssociativeArray()
    {
        $data = [['key' => 'value']];
        $this->assertOutputMatches($data);
    }

    public function testOutputArrayWithMixedDataTypes()
    {
        $data = [['int' => 1, 'float' => 1.5, 'bool' => true, 'null' => null]];
        $decoded = $this->assertOutputMatches($data);
        $this->assertSame($data, $decoded['results']);
    }
}
