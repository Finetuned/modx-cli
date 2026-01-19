<?php

namespace MODX\CLI\SSH;

/**
 * Interface for executing SSH commands.
 */
interface CommandExecutorInterface
{
    /**
     * @param string        $command        The command.
     * @param integer       $timeout        The timeout.
     * @param boolean       $tty            The tty.
     * @param callable|null $outputCallback The outputCallback.
     * @return integer
     */
    public function run(string $command, int $timeout, bool $tty, ?callable $outputCallback = null): int;
}
