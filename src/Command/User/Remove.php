<?php

namespace MODX\CLI\Command\User;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * A command to remove a MODX user
 */
class Remove extends ProcessorCmd
{
    protected $processor = 'Security\\User\\Remove';

    protected $name = 'user:remove';
    protected $description = 'Remove a MODX user';
    protected $userForRemoval;

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
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            [
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force removal without confirmation'
            ],
        ]);
    }

    /**
     * Prepare processor properties before execution.
     *
     * @param array $properties The processor properties.
     * @param array $options    The processor options.
     * @return boolean|null False to abort execution, otherwise null.
     */
    protected function beforeRun(array &$properties = [], array &$options = [])
    {
        $identifier = $this->argument('identifier');

        // If numeric, treat as ID; otherwise, treat as username and look up ID
        if (is_numeric($identifier)) {
            $userId = (int)$identifier;
            $user = $this->modx->getObject(\MODX\Revolution\modUser::class, $userId);
        } else {
            $user = $this->modx->getObject(\MODX\Revolution\modUser::class, ['username' => $identifier]);
        }

        if (!$user) {
            $this->error($this->trans('user_not_found', ['%identifier%' => $identifier], 'errors'));
            return false;
        }

        $userId = $user->get('id');
        $username = $user->get('username');
        $properties['id'] = $userId;
        $this->userForRemoval = $user;

        // Ask for confirmation unless --force is used
        if (!$this->option('force')) {
            $confirmed = $this->confirm(
                $this->trans('user.remove.confirm', ['%username%' => $username, '%id%' => $userId], 'commands'),
                false
            );

            if (!$confirmed) {
                $this->info($this->trans('user.remove.cancelled', [], 'commands'));
                exit(0);
            }
        }
        return null;
    }

    /**
     * Execute the command.
     *
     * @return integer
     */
    protected function process()
    {
        $properties = array_merge(
            $this->defaultsProperties,
            $this->processArray('properties')
        );

        $options = array_merge(
            $this->defaultsOptions,
            $this->processArray('options')
        );

        if (!empty($this->required)) {
            foreach ($this->required as $field) {
                $properties[$field] = $this->argument($field);
            }
        }

        $this->handleColumns();

        if ($this->beforeRun($properties, $options) === false) {
            $this->info($this->trans('operation_aborted', [], 'errors'));
            return 0;
        }

        $response = $this->modx->runProcessor($this->processor, $properties, $options);
        if (!($response instanceof \MODX\Revolution\Processors\ProcessorResponse) || !$response->getResponse()) {
            return $this->removeUserDirectly();
        }

        $this->response =& $response;
        $decoded = $this->decodeResponse($response);

        $message = $response->getMessage();
        if (
            $response->isError()
            && (stripos($message, 'Requested processor not found') !== false
                || stripos((string) ($decoded['message'] ?? ''), 'Requested processor not found') !== false)
        ) {
            return $this->removeUserDirectly();
        }

        $result = $this->processResponse($decoded);

        return $result === null ? 0 : $result;
    }

    /**
     * Remove a user without relying on processors.
     *
     * @return integer
     */
    private function removeUserDirectly(): int
    {
        if (!$this->userForRemoval) {
            $this->error($this->trans('user.remove.failed', [], 'commands'));
            return 1;
        }

        $userId = $this->userForRemoval->get('id');
        $username = $this->userForRemoval->get('username');

        if ($this->userForRemoval->remove()) {
            if ($this->option('json')) {
                $this->output->writeln(json_encode([
                    'success' => true,
                    'object' => [
                        'id' => $userId,
                        'username' => $username,
                    ],
                ], JSON_PRETTY_PRINT));
            } else {
                $this->info($this->trans('user.remove.success', [], 'commands'));
            }
            return 0;
        }

        if ($this->option('json')) {
            $this->output->writeln(json_encode([
                'success' => false,
                'message' => $this->trans('user.remove.failed', [], 'commands'),
            ], JSON_PRETTY_PRINT));
        } else {
            $this->error($this->trans('user.remove.failed', [], 'commands'));
        }

        return 1;
    }

    /**
     * Process processor response.
     *
     * @param array $response The decoded processor response.
     * @return integer
     */
    protected function processResponse(array $response = [])
    {
        if ($this->option('json')) {
            return parent::processResponse($response);
        }

        if (isset($response['success']) && $response['success']) {
            $this->info($this->trans('user.remove.success', [], 'commands'));
            return 0;
        } else {
            $this->error($this->trans('user.remove.failed', [], 'commands'));

            if (isset($response['message'])) {
                $this->error($response['message']);
            }
            return 1;
        }
    }
}
