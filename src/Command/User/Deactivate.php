<?php

namespace MODX\CLI\Command\User;

use MODX\CLI\Command\BaseCmd;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to deactivate a MODX user
 */
class Deactivate extends BaseCmd
{
    const MODX = true;

    protected $name = 'user:deactivate';
    protected $description = 'Deactivate a MODX user';

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

        if ((int) $user->get('active') === 0) {
            return $this->emitResult(true, 'User is already inactive', $user);
        }

        $user->set('active', 0);
        if ($user->save()) {
            return $this->emitResult(true, 'User deactivated', $user);
        }

        return $this->emitResult(false, 'Failed to deactivate user', $user);
    }

    private function getUser(string $identifier)
    {
        if (is_numeric($identifier)) {
            return $this->modx->getObject(\MODX\Revolution\modUser::class, (int) $identifier);
        }

        return $this->modx->getObject(\MODX\Revolution\modUser::class, ['username' => $identifier]);
    }

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
