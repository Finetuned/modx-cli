<?php

namespace MODX\CLI\API;

/**
 * Interface for commands that support hooks
 */
interface HookableCommand
{
    /**
     * Set the before invoke hook
     *
     * @param callable $callback The callback to execute before the command.
     * @return $this
     */
    public function setBeforeInvoke(callable $callback);

    /**
     * Set the after invoke hook
     *
     * @param callable $callback The callback to execute after the command.
     * @return $this
     */
    public function setAfterInvoke(callable $callback);
}
