<?php

namespace MODX\CLI\Command\User;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to block a MODX user
 */
class Block extends BaseCmd
{
    const MODX = true;

    protected $name = 'user:block';
    protected $description = 'Block a MODX user';

    protected function getArguments()
    {
        return array(
            array(
                'identifier',
                InputArgument::REQUIRED,
                'The user ID or username'
            ),
        );
    }

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

        if ((int) $profile->get('blocked') === 1) {
            return $this->emitResult(true, 'User is already blocked', $user, $profile);
        }

        $profile->set('blocked', 1);
        if ($profile->save()) {
            return $this->emitResult(true, 'User blocked', $user, $profile);
        }

        return $this->emitResult(false, 'Failed to block user', $user, $profile);
    }

    private function getUser(string $identifier)
    {
        if (is_numeric($identifier)) {
            return $this->modx->getObject(\MODX\Revolution\modUser::class, (int) $identifier);
        }

        return $this->modx->getObject(\MODX\Revolution\modUser::class, ['username' => $identifier]);
    }

    private function emitResult(bool $success, string $message, $user, $profile): int
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
