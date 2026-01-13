<?php

namespace MODX\CLI\SSH;

use Symfony\Component\Process\Process;

/**
 * Executes commands via Symfony Process.
 */
class SymfonyProcessExecutor implements CommandExecutorInterface
{
    /**
     * @inheritDoc
     */
    public function run(string $command, int $timeout, bool $tty, ?callable $outputCallback = null): int
    {
        $process = Process::fromShellCommandline($command);
        $process->setTimeout($timeout);
        $process->setTty($tty);

        if ($outputCallback) {
            return $process->run($outputCallback);
        }

        return $process->run();
    }
}
