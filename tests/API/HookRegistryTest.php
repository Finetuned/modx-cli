<?php

namespace MODX\CLI\Tests\API;

use MODX\CLI\API\HookRegistry;
use PHPUnit\Framework\TestCase;

class HookRegistryTest extends TestCase
{
    /**
     * @var HookRegistry
     */
    private $registry;

    protected function setUp(): void
    {
        $this->registry = new HookRegistry();
    }

    public function testRegisterHook()
    {
        $hookName = 'test:hook';
        $callback = function () {
            return 'test';
        };

        $result = $this->registry->register($hookName, $callback);

        $this->assertTrue($result);
        $this->assertTrue($this->registry->has($hookName));
    }

    public function testRegisterMultipleCallbacksForSameHook()
    {
        $hookName = 'test:multiple';
        $callback1 = function () {
            return 'test1';
        };
        $callback2 = function () {
            return 'test2';
        };

        $this->registry->register($hookName, $callback1);
        $this->registry->register($hookName, $callback2);

        $callbacks = $this->registry->get($hookName);

        $this->assertCount(2, $callbacks);
    }

    public function testUnregisterSpecificCallback()
    {
        $hookName = 'test:unregister';
        $callback1 = function () {
            return 'test1';
        };
        $callback2 = function () {
            return 'test2';
        };

        $this->registry->register($hookName, $callback1);
        $this->registry->register($hookName, $callback2);

        $result = $this->registry->unregister($hookName, $callback1);

        $this->assertTrue($result);
        $this->assertTrue($this->registry->has($hookName));
        $this->assertCount(1, $this->registry->get($hookName));
    }

    public function testUnregisterAllCallbacks()
    {
        $hookName = 'test:unregister-all';
        $callback1 = function () {
            return 'test1';
        };
        $callback2 = function () {
            return 'test2';
        };

        $this->registry->register($hookName, $callback1);
        $this->registry->register($hookName, $callback2);

        $result = $this->registry->unregister($hookName);

        $this->assertTrue($result);
        $this->assertFalse($this->registry->has($hookName));
        $this->assertEmpty($this->registry->get($hookName));
    }

    public function testUnregisterNonExistentHook()
    {
        $result = $this->registry->unregister('non:existent');

        $this->assertFalse($result);
    }

    public function testRunHook()
    {
        $hookName = 'test:run';
        $callback = function ($arg1, $arg2) {
            return $arg1 . $arg2;
        };

        $this->registry->register($hookName, $callback);

        $results = $this->registry->run($hookName, ['Hello, ', 'World!']);

        $this->assertCount(1, $results);
        $this->assertEquals('Hello, World!', $results[0]);
    }

    public function testRunMultipleCallbacks()
    {
        $hookName = 'test:run-multiple';
        $callback1 = function ($arg) {
            return $arg . '1';
        };
        $callback2 = function ($arg) {
            return $arg . '2';
        };

        $this->registry->register($hookName, $callback1);
        $this->registry->register($hookName, $callback2);

        $results = $this->registry->run($hookName, ['test']);

        $this->assertCount(2, $results);
        $this->assertEquals('test1', $results[0]);
        $this->assertEquals('test2', $results[1]);
    }

    public function testRunNonExistentHook()
    {
        $results = $this->registry->run('non:existent', ['test']);

        $this->assertEmpty($results);
    }

    public function testGetAllHooks()
    {
        $this->registry->register('test:hook1', function () {
            return 'test1';
        });

        $this->registry->register('test:hook2', function () {
            return 'test2';
        });

        $hooks = $this->registry->getAll();

        $this->assertCount(2, $hooks);
        $this->assertArrayHasKey('test:hook1', $hooks);
        $this->assertArrayHasKey('test:hook2', $hooks);
    }
}
