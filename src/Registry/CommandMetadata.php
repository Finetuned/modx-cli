<?php

namespace MODX\CLI\Registry;

/**
 * Command metadata class
 *
 * Holds metadata about a command including category, tags, version requirements, etc.
 */
class CommandMetadata
{
    /**
     * @var string Command name
     */
    private $name;

    /**
     * @var string Command category
     */
    private $category;

    /**
     * @var array Command tags
     */
    private $tags;

    /**
     * @var string Minimum MODX version required
     */
    private $minModxVersion;

    /**
     * @var array Command aliases
     */
    private $aliases;

    /**
     * @var string Command description
     */
    private $description;

    /**
     * @var array Related commands
     */
    private $relatedCommands;

    /**
     * @var array Custom metadata
     */
    private $custom;

    /**
     * Constructor
     *
     * @param string $name Command name
     * @param array $data Metadata array
     */
    public function __construct(string $name, array $data = [])
    {
        $this->name = $name;
        $this->category = $data['category'] ?? 'general';
        $this->tags = $data['tags'] ?? [];
        $this->minModxVersion = $data['minModxVersion'] ?? '';
        $this->aliases = $data['aliases'] ?? [];
        $this->description = $data['description'] ?? '';
        $this->relatedCommands = $data['relatedCommands'] ?? [];
        $this->custom = $data['custom'] ?? [];
    }

    /**
     * Get command name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get command category
     *
     * @return string
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * Get command tags
     *
     * @return array
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * Get minimum MODX version
     *
     * @return string
     */
    public function getMinModxVersion(): string
    {
        return $this->minModxVersion;
    }

    /**
     * Get command aliases
     *
     * @return array
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * Get command description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Get related commands
     *
     * @return array
     */
    public function getRelatedCommands(): array
    {
        return $this->relatedCommands;
    }

    /**
     * Get custom metadata value
     *
     * @param string $key Custom metadata key
     * @return mixed|null
     */
    public function getCustom(string $key)
    {
        return $this->custom[$key] ?? null;
    }

    /**
     * Check if command has a specific tag
     *
     * @param string $tag Tag to check
     * @return bool
     */
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->tags);
    }

    /**
     * Check if command has an alias
     *
     * @param string $alias Alias to check
     * @return bool
     */
    public function hasAlias(string $alias): bool
    {
        return in_array($alias, $this->aliases);
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'category' => $this->category,
            'tags' => $this->tags,
            'minModxVersion' => $this->minModxVersion,
            'aliases' => $this->aliases,
            'description' => $this->description,
            'relatedCommands' => $this->relatedCommands,
            'custom' => $this->custom,
        ];
    }
}
