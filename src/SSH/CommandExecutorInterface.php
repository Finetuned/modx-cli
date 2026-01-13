<?php

namespace MODX\CLI\SSH;

/**
 * Interface for executing SSH commands.
 */
interface CommandExecutorInterface
{
    /**
     * @param string $command
     * @param int $timeout
     * @param bool $tty
     * @param callable|null $outputCallback
     * @return int
     */
    public function run(string $command, int $timeout, bool $tty, ?callable $outputCallback = null): int;
}
