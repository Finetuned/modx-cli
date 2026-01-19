<?php

namespace MODX\CLI\Command\User;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to activate a MODX user
 */
class Activate extends BaseCmd
{
    public const MODX = true;

    protected $name = 'user:activate';
    protected $description = 'Activate a MODX user';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'identifier',
                InputArgument::REQUIRED,
                'The user ID or username'
            ],
        ];
    }

    /**
     * Execute the command.
     *
     * @return integer
     */
    protected function process()
    {
        $identifier = $this->argument('identifier');
        $user = $this->getUser($identifier);
        if (!$user) {
            $this->error("User not found: {$identifier}");
            return 1;
        }

        if ((int) $user->get('active') === 1) {
            return $this->emitResult(true, 'User is already active', $user);
        }

        $user->set('active', 1);
        if ($user->save()) {
            return $this->emitResult(true, 'User activated', $user);
        }

        return $this->emitResult(false, 'Failed to activate user', $user);
    }

    /**
     * Fetch a user by identifier.
     *
     * @param string $identifier The user ID or username.
     * @return \MODX\Revolution\modUser|null The user instance, or null when not found.
     */
    private function getUser(string $identifier)
    {
        if (is_numeric($identifier)) {
            return $this->modx->getObject(\MODX\Revolution\modUser::class, (int) $identifier);
        }

        return $this->modx->getObject(\MODX\Revolution\modUser::class, ['username' => $identifier]);
    }

    /**
     * Emit command output and return exit code.
     *
     * @param boolean                       $success Whether the operation succeeded.
     * @param string                        $message The message to display.
     * @param \MODX\Revolution\modUser|null $user    The user instance.
     * @return integer
     */
    private function emitResult(bool $success, string $message, $user): int
    {
        if ($this->option('json')) {
            $this->output->writeln(json_encode([
                'success' => $success,
                'message' => $message,
                'object' => $user ? [
                    'id' => $user->get('id'),
                    'username' => $user->get('username'),
                    'active' => (int) $user->get('active'),
                ] : null,
            ], JSON_PRETTY_PRINT));
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
