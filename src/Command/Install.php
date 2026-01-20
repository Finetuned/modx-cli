<?php

namespace MODX\CLI\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;

/**
 * Install MODX into a target directory.
 */
class Install extends BaseCmd
{
    protected $name = 'install';
    protected $description = 'Install MODX here';

    /**
     * Execute the install command.
     */
    protected function process()
    {
        $target = $this->argument('target');
        if ($target === null || $target === '') {
            $target = getcwd();
        }

        $configPath = $this->argument('config');
        if ($configPath === '') {
            $configPath = null;
        }
        $installer = (string) $this->option('installer');

        if ($installer === 'composer') {
            $result = $this->runComposerInstall($target);
        } else {
            $result = $this->runCustomInstaller($installer, $target, $configPath);
        }

        if (!$result['success']) {
            return $this->emitResult(false, $result['message'], $result['payload']);
        }

        if ($this->option('setup')) {
            $setupConfig = $this->option('setup-config');
            if ($setupConfig === '') {
                $setupConfig = null;
            }
            if ($setupConfig === null) {
                $setupConfig = $configPath;
            }
            $setupResult = $this->runSetup($target, $setupConfig);
            if (!$setupResult['success']) {
                return $this->emitResult(false, $setupResult['message'], array_merge(
                    $result['payload'],
                    $setupResult['payload']
                ));
            }

            return $this->emitResult(true, 'MODX installed and setup completed', array_merge(
                $result['payload'],
                $setupResult['payload']
            ));
        }

        return $this->emitResult(true, $result['message'], $result['payload']);
    }

    /**
     * @inheritDoc
     */
    protected function getArguments()
    {
        return [
            [
                'target',
                InputArgument::OPTIONAL,
                'Path to install MODX into (defaults to current directory)',
                ''
            ],
            [
                'config',
                InputArgument::OPTIONAL,
                'Path to installer configuration file',
                ''
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'installer',
                null,
                InputOption::VALUE_REQUIRED,
                'Installer to use (composer or custom)',
                'composer',
            ],
            [
                'installer-command',
                null,
                InputOption::VALUE_REQUIRED,
                'Custom installer command line (use {target} and {config} placeholders)',
                null,
            ],
            [
                'modx-version',
                null,
                InputOption::VALUE_REQUIRED,
                'MODX version for composer create-project (optional)',
                null,
            ],
            [
                'composer-bin',
                null,
                InputOption::VALUE_REQUIRED,
                'Composer executable or command (default: composer)',
                'composer',
            ],
            [
                'composer-no-interaction',
                null,
                InputOption::VALUE_NONE,
                'Disable interactive prompts for composer',
                null,
            ],
            [
                'setup',
                null,
                InputOption::VALUE_NONE,
                'Run MODX setup after installation',
                null,
            ],
            [
                'setup-command',
                null,
                InputOption::VALUE_REQUIRED,
                'Custom setup command line (use {target} and {config} placeholders)',
                null,
            ],
            [
                'setup-config',
                null,
                InputOption::VALUE_REQUIRED,
                'Path to setup config file (defaults to install config argument)',
                null,
            ],
        ]);
    }

    /**
     * Run the composer create-project installer.
     */
    private function runComposerInstall(string $target): array
    {
        $composerBin = (string) $this->option('composer-bin');
        if ($composerBin === 'composer') {
            $envComposer = getenv('COMPOSER_BIN');
            if ($envComposer) {
                $composerBin = $envComposer;
            }
        }
        $version = $this->normalizeVersionConstraint($this->option('modx-version'));

        $commandParts = ['create-project', 'modx/revolution', $target];
        if ($version) {
            $commandParts[] = $version;
        }

        if ($this->option('composer-no-interaction')) {
            $commandParts[] = '--no-interaction';
        }

        $command = array_merge([$composerBin], $commandParts);
        if ($composerBin === 'composer') {
            $shell = getenv('SHELL') ?: '/bin/bash';
            $composerCmd = implode(' ', array_merge(['composer'], array_map('escapeshellarg', $commandParts)));
            $commandLine = sprintf('%s -ic %s', $shell, escapeshellarg($composerCmd));
            $process = Process::fromShellCommandline($commandLine);
        } elseif (strpos($composerBin, ' ') !== false) {
            $commandLineParts = array_merge([$composerBin], array_map('escapeshellarg', $commandParts));
            $commandLine = implode(' ', $commandLineParts);
            $process = Process::fromShellCommandline($commandLine);
        } else {
            $process = new Process($command);
        }
        $process->setTimeout(null);
        $process->run(function ($type, $buffer): void {
            $this->output->write($buffer);
        });

        if ($process->isSuccessful()) {
            return $this->buildResult(true, 'MODX installed via composer', [
                'installer' => 'composer',
                'target' => $target,
                'command' => $command,
            ]);
        }

        return $this->buildResult(false, 'Composer installation failed', [
            'installer' => 'composer',
            'target' => $target,
            'command' => $command,
            'error' => trim($process->getErrorOutput()),
        ]);
    }

    /**
     * Run a custom installer command.
     */
    private function runCustomInstaller(string $installer, string $target, ?string $configPath): array
    {
        $commandLine = (string) $this->option('installer-command');
        if ($commandLine === '') {
            return $this->buildResult(false, 'Custom installer requires --installer-command', [
                'installer' => $installer,
                'target' => $target,
            ]);
        }

        $commandLine = str_replace(
            ['{target}', '{config}'],
            [$target, $configPath ?? ''],
            $commandLine
        );

        $process = Process::fromShellCommandline($commandLine);
        $process->setTimeout(null);
        $process->run(function ($type, $buffer): void {
            $this->output->write($buffer);
        });

        if ($process->isSuccessful()) {
            return $this->buildResult(true, 'MODX installed via custom installer', [
                'installer' => $installer,
                'target' => $target,
                'command' => $commandLine,
            ]);
        }

        return $this->buildResult(false, 'Custom installer failed', [
            'installer' => $installer,
            'target' => $target,
            'command' => $commandLine,
            'error' => trim($process->getErrorOutput()),
        ]);
    }

    /**
     * Run MODX setup after install.
     */
    private function runSetup(string $target, ?string $configPath): array
    {
        $commandLine = (string) $this->option('setup-command');
        if ($commandLine !== '') {
            $commandLine = str_replace(
                ['{target}', '{config}'],
                [$target, $configPath ?? ''],
                $commandLine
            );
            $process = Process::fromShellCommandline($commandLine, $target);
        } else {
            $phpBin = PHP_BINARY;
            $commandParts = [$phpBin, 'setup/index.php'];
            if ($configPath) {
                $commandParts[] = '--config=' . $configPath;
            }
            $process = new Process($commandParts, $target);
        }

        $process->setTimeout(null);
        $process->run(function ($type, $buffer): void {
            $this->output->write($buffer);
        });

        if ($process->isSuccessful()) {
            return $this->buildResult(true, 'MODX setup completed', [
                'setup' => true,
                'setup_command' => $process->getCommandLine(),
            ]);
        }

        return $this->buildResult(false, 'MODX setup failed', [
            'setup' => true,
            'setup_command' => $process->getCommandLine(),
            'error' => trim($process->getErrorOutput()),
        ]);
    }

    /**
     * Normalize user-provided MODX version constraints for Packagist tags.
     */
    protected function normalizeVersionConstraint(?string $version): ?string
    {
        if ($version === null || $version === '') {
            return null;
        }

        $normalized = trim($version);
        if (strpos($normalized, 'dev') !== false) {
            return $normalized;
        }

        if (preg_match('/^\d+\.\d+\.\d+$/', $normalized) === 1) {
            return 'v' . $normalized . '-pl';
        }

        if (preg_match('/^\d+\.\d+\.\d+-[A-Za-z0-9.]+$/', $normalized) === 1) {
            return 'v' . $normalized;
        }

        return $normalized;
    }

    /**
     * Build a normalized result payload for install/setup steps.
     */
    private function buildResult(bool $success, string $message, array $payload): array
    {
        return [
            'success' => $success,
            'message' => $message,
            'payload' => $payload,
        ];
    }

    /**
     * Emit output and return an exit code.
     */
    private function emitResult(bool $success, string $message, array $payload = []): int
    {
        if ($this->option('json')) {
            $this->output->writeln(json_encode(array_merge([
                'success' => $success,
                'message' => $message,
            ], $payload), JSON_PRETTY_PRINT));
        } else {
            if ($success) {
                $this->info($message);
            } else {
                $this->error($message);
            }
        }

        return $success ? 0 : 1;
    }
}
