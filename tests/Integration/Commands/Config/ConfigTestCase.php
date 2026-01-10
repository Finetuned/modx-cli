<?php

namespace MODX\CLI\Tests\Integration\Commands\Config;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;
use Symfony\Component\Process\Process;

abstract class ConfigTestCase extends BaseIntegrationTest
{
    protected string $tempHome;
    protected string $configDir;
    protected string $instancesFile;
    protected string $excludedFile;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tempHome = $this->modxPath . '/tmp/modx-cli-config-' . uniqid();
        $this->configDir = $this->tempHome . '/.modx';
        $this->instancesFile = $this->configDir . '/instances.json';
        $this->excludedFile = $this->configDir . '/excluded_commands.json';

        if (!file_exists($this->configDir)) {
            mkdir($this->configDir, 0755, true);
        }
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempHome);
        parent::tearDown();
    }

    protected function runConfigCommand(array $arguments): Process
    {
        $command = array_merge(['php', $this->binPath], $arguments);
        $process = new Process($command, $this->modxPath);
        $env = $_SERVER;
        $env['HOME'] = $this->tempHome;
        $env['MODX_CONFIG_KEY'] = 'config';
        $process->setEnv($env);
        $process->run();

        return $process;
    }

    protected function readJsonFile(string $path): array
    {
        if (!file_exists($path)) {
            return [];
        }

        $data = json_decode(file_get_contents($path), true);
        return is_array($data) ? $data : [];
    }

    protected function removeDirectory(string $dir): void
    {
        if (!file_exists($dir)) {
            return;
        }

        if (!is_dir($dir)) {
            @unlink($dir);
            return;
        }

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $this->removeDirectory($dir . DIRECTORY_SEPARATOR . $item);
        }

        @rmdir($dir);
    }
}
