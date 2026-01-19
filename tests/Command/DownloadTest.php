<?php

namespace MODX\CLI\Tests\Command;

use MODX\CLI\Command\Download;
use MODX\CLI\Tests\Configuration\BaseTest;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Tester\CommandTester;

class DownloadTest extends BaseTest
{
    public function testBuildFileNameForLatest()
    {
        $command = new TestDownload();
        $this->setCommandInput($command, [
            'version' => 'latest',
            'path' => '/tmp',
        ]);

        $method = new \ReflectionMethod($command, 'buildFileName');
        $method->setAccessible(true);
        $this->assertEquals('latest.zip', $method->invoke($command));
    }

    public function testBuildFileNameForAdvanced()
    {
        $command = new TestDownload();
        $this->setCommandInput($command, [
            'version' => '3.0.0',
            'path' => '/tmp',
            '--advanced' => true,
        ]);

        $method = new \ReflectionMethod($command, 'buildFileName');
        $method->setAccessible(true);
        $this->assertEquals('modx-3.0.0-advanced.zip', $method->invoke($command));
    }

    public function testBuildFileNameForSdk()
    {
        $command = new TestDownload();
        $this->setCommandInput($command, [
            'version' => '3.0.0',
            'path' => '/tmp',
            '--sdk' => true,
        ]);

        $method = new \ReflectionMethod($command, 'buildFileName');
        $method->setAccessible(true);
        $this->assertEquals('modx-3.0.0-sdk.zip', $method->invoke($command));
    }

    public function testBuildUrlForLatest()
    {
        $command = new TestDownload();
        $this->setCommandInput($command, [
            'version' => 'latest',
            'path' => '/tmp',
        ]);

        $method = new \ReflectionMethod($command, 'buildURL');
        $method->setAccessible(true);
        $this->assertEquals('http://modx.com/download/latest', $method->invoke($command));
    }

    public function testBuildUrlForSpecificVersion()
    {
        $command = new TestDownload();
        $this->setCommandInput($command, [
            'version' => '3.0.0',
            'path' => '/tmp',
        ]);

        $method = new \ReflectionMethod($command, 'buildURL');
        $method->setAccessible(true);
        $this->assertEquals('http://modx.com/download/direct/modx-3.0.0.zip', $method->invoke($command));
    }

    public function testExecuteOutputsJson()
    {
        $command = new TestDownload();
        $tester = new CommandTester($command);

        $dir = sys_get_temp_dir() . '/modx-cli-download-json/';
        @mkdir($dir, 0777, true);

        $tester->execute([
            'version' => '3.0.0',
            'path' => $dir,
            '--json' => true
        ]);

        $decoded = json_decode($tester->getDisplay(), true);
        $this->assertTrue($decoded['success']);
        $this->assertEquals('Download complete', $decoded['message']);
        $this->assertEquals('http://modx.com/download/direct/modx-3.0.0.zip', $decoded['url']);
        $this->assertEquals($dir . 'modx-3.0.0.zip', $decoded['destination']);
        $this->assertFalse($decoded['already_downloaded']);
        $this->assertFileExists($dir . 'modx-3.0.0.zip');

        @unlink($dir . 'modx-3.0.0.zip');
        @rmdir($dir);
    }

    private function setCommandInput(Download $command, array $params): void
    {
        $input = new ArrayInput($params, $command->getDefinition());
        $output = new BufferedOutput();

        $reflection = new \ReflectionClass($command);
        $inputProp = $reflection->getProperty('input');
        $inputProp->setAccessible(true);
        $inputProp->setValue($command, $input);

        $outputProp = $reflection->getProperty('output');
        $outputProp->setAccessible(true);
        $outputProp->setValue($command, $output);
    }
}

class TestDownload extends Download
{
    protected $name = 'download:test';
    protected $description = 'Download test command';

    protected function download(string $url, string $target): void
    {
        file_put_contents($target, 'test');
    }
}
