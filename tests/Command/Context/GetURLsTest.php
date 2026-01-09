<?php

namespace MODX\CLI\Tests\Command\Context;

use MODX\CLI\Command\Context\GetURLs;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Tester\CommandTester;

class GetURLsTest extends BaseTest
{
    public function testExecuteWithNoContextsJsonOutput()
    {
        $command = new GetURLs();
        $modx = $this->createMock('MODX\\Revolution\\modX');
        $modx->method('getCollection')
            ->with(\MODX\Revolution\modContext::class)
            ->willReturn([]);
        $command->modx = $modx;

        $tester = new CommandTester($command);
        $tester->execute(['--json' => true]);

        $decoded = json_decode($tester->getDisplay(), true);
        $this->assertEquals(0, $decoded['total']);
        $this->assertEquals([], $decoded['results']);
    }

    public function testExecuteWithContextsJsonOutput()
    {
        $command = new GetURLs();
        $modx = $this->createMock('MODX\\Revolution\\modX');

        $context = $this->getMockBuilder('stdClass')
            ->addMethods(['get', 'prepare', 'getOption'])
            ->getMock();
        $context->method('get')->with('key')->willReturn('web');
        $context->method('prepare');
        $context->method('getOption')->willReturnMap([
            ['site_url', '', 'https://example.test/'],
            ['site_start', null, 10],
        ]);

        $resource = $this->getMockBuilder('stdClass')
            ->addMethods(['get'])
            ->getMock();
        $resource->method('get')->willReturnMap([
            ['id', 10],
        ]);

        $modx->method('getCollection')
            ->with(\MODX\Revolution\modContext::class)
            ->willReturn([$context]);
        $modx->method('getObject')
            ->with(\MODX\Revolution\modResource::class, 10)
            ->willReturn($resource);
        $modx->method('makeUrl')
            ->with(10, 'web', '', 'full')
            ->willReturn('https://example.test/');

        $command->modx = $modx;

        $tester = new CommandTester($command);
        $tester->execute(['--json' => true]);

        $decoded = json_decode($tester->getDisplay(), true);
        $this->assertEquals(1, $decoded['total']);
        $this->assertEquals('web', $decoded['results'][0]['key']);
        $this->assertEquals('https://example.test/', $decoded['results'][0]['site_url']);
        $this->assertEquals('https://example.test/', $decoded['results'][0]['site_start_url']);
    }
}
