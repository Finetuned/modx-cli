<?php

namespace MODX\CLI\Tests\Command;

use MODX\CLI\Command\Crawl;
use MODX\CLI\Tests\Configuration\BaseTest;
use MODX\Revolution\modResource;

class CrawlTest extends BaseTest
{
    public function testGetCriteriaForNumericParent()
    {
        $command = new Crawl();
        $modx = $this->createMock('MODX\Revolution\modX');

        $query = $this->getMockBuilder('xPDO\\Om\\xPDOQuery')
            ->disableOriginalConstructor()
            ->getMock();

        $query->expects($this->once())
            ->method('select')
            ->with('id,pagetitle,context_key');
        $query->expects($this->once())
            ->method('where')
            ->with($this->callback(function($criteria) {
                return $criteria['parent'] === 10
                    && $criteria['published'] === true
                    && $criteria['deleted'] === false
                    && $criteria['cacheable'] === true;
            }));
        $query->expects($this->once())
            ->method('sortby')
            ->with('context_key');

        $modx->expects($this->once())
            ->method('newQuery')
            ->with(modResource::class)
            ->willReturn($query);

        $command->modx = $modx;

        $method = new \ReflectionMethod($command, 'getCriteria');
        $method->setAccessible(true);
        $result = $method->invoke($command, 10);

        $this->assertSame($query, $result);
    }

    public function testGetCriteriaForAllContexts()
    {
        $command = new Crawl();
        $modx = $this->createMock('MODX\Revolution\modX');

        $query = $this->getMockBuilder('xPDO\\Om\\xPDOQuery')
            ->disableOriginalConstructor()
            ->getMock();

        $query->expects($this->once())
            ->method('where')
            ->with($this->callback(function($criteria) {
                return $criteria['context_key:!='] === 'mgr';
            }));

        $modx->expects($this->once())
            ->method('newQuery')
            ->with(modResource::class)
            ->willReturn($query);

        $command->modx = $modx;

        $method = new \ReflectionMethod($command, 'getCriteria');
        $method->setAccessible(true);
        $result = $method->invoke($command, 'all');

        $this->assertSame($query, $result);
    }
}
