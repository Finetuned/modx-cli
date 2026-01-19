<?php

namespace MODX\CLI\Command\User;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to unblock a MODX user
 */
class Unblock extends BaseCmd
{
    public const MODX = true;

    protected $name = 'user:unblock';
    protected $description = 'Unblock a MODX user';

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

        $profile = $user->getOne('Profile');
        if (!$profile) {
            $this->error('User profile not found');
            return 1;
        }

        if ((int) $profile->get('blocked') === 0) {
            return $this->emitResult(true, 'User is not blocked', $user, $profile);
        }

        $profile->set('blocked', 0);
        $profile->set('blockeduntil', 0);
        if ($profile->save()) {
            return $this->emitResult(true, 'User unblocked', $user, $profile);
        }

        return $this->emitResult(false, 'Failed to unblock user', $user, $profile);
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
     * @param boolean $success Whether the operation succeeded.
     * @param string  $message The message to display.
     * @param mixed   $user    The user instance.
     * @param mixed   $profile The profile instance.
     * @return integer
     */
    private function emitResult(bool $success, string $message, mixed $user, mixed $profile): int
    {
        if ($this->option('json')) {
            $this->output->writeln(json_encode([
                'success' => $success,
                'message' => $message,
                'object' => $user ? [
                    'id' => $user->get('id'),
                    'username' => $user->get('username'),
                    'blocked' => $profile ? (int) $profile->get('blocked') : null,
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
