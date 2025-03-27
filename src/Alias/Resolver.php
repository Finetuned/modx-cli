<?php namespace MODX\CLI\Alias;

use MODX\CLI\Configuration\Yaml\YamlConfig;

/**
 * Class Resolver
 * 
 * Resolves aliases to their actual connection details
 * 
 * @package MODX\CLI\Alias
 */
class Resolver
{
    /**
     * @var YamlConfig The configuration
     */
    protected $config;

    /**
     * Resolver constructor.
     * 
     * @param YamlConfig $config The configuration
     */
    public function __construct(YamlConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Check if a command is an alias
     * 
     * @param string $command The command to check
     * @return bool True if the command is an alias, false otherwise
     */
    public function isAlias($command)
    {
        return strpos($command, '@') === 0;
    }

    /**
     * Resolve an alias to its definition
     * 
     * @param string $alias The alias to resolve
     * @return array The alias definition
     * @throws \Exception If the alias is not found
     */
    public function resolveAlias($alias)
    {
        // Remove @ prefix
        $aliasName = substr($alias, 1);
        
        // Get alias definition
        $aliasDef = $this->config->getAlias($aliasName);
        
        if (!$aliasDef) {
            throw new \Exception("Alias '@{$aliasName}' not found.");
        }
        
        return $aliasDef;
    }

    /**
     * Check if an alias definition is a group
     * 
     * @param array $aliasDef The alias definition
     * @return bool True if the alias is a group, false otherwise
     */
    public function isAliasGroup($aliasDef)
    {
        return is_array($aliasDef) && !isset($aliasDef['ssh']);
    }

    /**
     * Get the members of an alias group
     * 
     * @param array $aliasDef The alias group definition
     * @return array The members of the group
     */
    public function getAliasGroupMembers($aliasDef)
    {
        return $aliasDef;
    }
}
