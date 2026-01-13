<?php

namespace MODX\CLI\Tests\Integration\Commands\Misc;

use MODX\CLI\Tests\Integration\BaseIntegrationTest;
use Symfony\Component\Process\Process;

/**
 * Integration test for self-update --check with mocked HTTP fixtures
 */
class SelfUpdateTest extends BaseIntegrationTest
{
    public function testCheckUsesFixtureResponse()
    {
        $fixturePath = realpath(__DIR__ . '/../../Fixtures/self-update-releases.json');
        $this->assertNotFalse($fixturePath, 'Fixture file should exist');

        $process = $this->executeSelfUpdateCommand([
            'self-update',
            '--check',
            '--json',
        ], [
            'MODX_CLI_SELF_UPDATE_FIXTURES' => $fixturePath,
        ]);

        $data = json_decode($process->getOutput(), true);
        $this->assertIsArray($data);
        $this->assertTrue($data['success']);

        $versionPath = dirname(__DIR__, 4) . '/VERSION';
        $expectedVersion = trim((string) file_get_contents($versionPath));
        $this->assertSame($expectedVersion, $data['current_version']);
        $this->assertSame('0.7.0', $data['latest_version']);
        $this->assertSame(
            version_compare('0.7.0', $expectedVersion, '>'),
            $data['update_available']
        );
        $this->assertSame('https://example.com/modx-cli.phar', $data['download_url']);
        $this->assertSame(1234, $data['file_size']);
    }

    /**
     * @param array<int, string> $arguments
     * @param array<string, string> $extraEnv
     */
    private function executeSelfUpdateCommand(array $arguments, array $extraEnv = []): Process
    {
        $command = array_merge(['php', $this->binPath], $arguments);
        $process = new Process($command);
        $process->setTimeout(30);
        $process->setWorkingDirectory($this->modxPath);

        $env = array_merge($_SERVER, $_ENV, [
            'MODX_CONFIG_KEY' => 'config',
        ], $extraEnv);

        if (method_exists($process, 'inheritEnvironmentVariables')) {
            $process->inheritEnvironmentVariables(true);
        }
        $process->setEnv($env);
        $process->run();

        $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput());

        return $process;
    }
}
