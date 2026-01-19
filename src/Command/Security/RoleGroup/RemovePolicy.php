<?php

namespace MODX\CLI\Command\Security\RoleGroup;

use MODX\CLI\Command\BaseCmd;
use MODX\Revolution\modAccessPolicyTemplate;
use MODX\Revolution\modAccessPolicyTemplateGroup;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to remove a policy template from a role group
 */
class RemovePolicy extends BaseCmd
{
    public const MODX = true;

    protected $name = 'security:rolegroup:removepolicy';
    protected $description = 'Remove a policy template from a role group';

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            [
                'group',
                InputArgument::REQUIRED,
                'The role group name or ID'
            ],
            [
                'policy',
                InputArgument::REQUIRED,
                'The policy template name or ID'
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
        $group = $this->findGroup($this->argument('group'));
        if (!$group) {
            return $this->outputResult(false, 'Role group not found');
        }

        $policy = $this->findPolicyTemplate($this->argument('policy'));
        if (!$policy) {
            return $this->outputResult(false, 'Policy template not found');
        }

        if ((int) $policy->get('template_group') !== (int) $group->get('id')) {
            return $this->outputResult(false, 'Policy template is not assigned to the specified role group');
        }

        $policy->set('template_group', 0);
        if (!$policy->save()) {
            return $this->outputResult(false, 'Failed to remove policy template from role group');
        }

        return $this->outputResult(true, 'Policy template removed from role group', [
            'group' => $group->get('name'),
            'group_id' => (int) $group->get('id'),
            'policy' => $policy->get('name'),
            'policy_id' => (int) $policy->get('id'),
        ]);
    }

    /**
     * Find a role group by identifier.
     *
     * @param string $group The group name or ID.
     * @return mixed
     */
    protected function findGroup(string $group)
    {
        if (ctype_digit($group)) {
            return $this->modx->getObject(modAccessPolicyTemplateGroup::class, (int) $group);
        }

        return $this->modx->getObject(modAccessPolicyTemplateGroup::class, ['name' => $group]);
    }

    /**
     * Find a policy template by identifier.
     *
     * @param string $policy The template name or ID.
     * @return mixed
     */
    protected function findPolicyTemplate(string $policy)
    {
        if (ctype_digit($policy)) {
            return $this->modx->getObject(modAccessPolicyTemplate::class, (int) $policy);
        }

        return $this->modx->getObject(modAccessPolicyTemplate::class, ['name' => $policy]);
    }

    /**
     * Output the result payload.
     *
     * @param boolean $success Whether the operation succeeded.
     * @param string  $message The message to display.
     * @param array   $payload Additional payload data.
     * @return integer
     */
    protected function outputResult(bool $success, string $message, array $payload = [])
    {
        if ($this->option('json')) {
            $this->output->writeln(json_encode(array_merge([
                'success' => $success,
                'message' => $message,
            ], $payload), JSON_PRETTY_PRINT));
            return $success ? 0 : 1;
        }

        if ($success) {
            $this->info($message);
        } else {
            $this->error($message);
        }

        return $success ? 0 : 1;
    }
}
