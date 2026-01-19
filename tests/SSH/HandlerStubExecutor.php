<?php

namespace MODX\CLI\Tests\SSH;

use MODX\CLI\SSH\CommandExecutorInterface;

class HandlerStubExecutor implements CommandExecutorInterface
{
    public $command;
    public $timeout;
    public $tty;
    public $outputCallback;
    public $returnCode = 0;

    public function run(string $command, int $timeout, bool $tty, ?callable $outputCallback = null): int
    {
        $this->command = $command;
        $this->timeout = $timeout;
        $this->tty = $tty;
        $this->outputCallback = $outputCallback;

        return $this->returnCode;
    }
}
