<?php

namespace MODX\CLI\Registry;

/**
 * Command metadata registry
 *
 * Central registry for command metadata, enabling command discovery,
 * filtering, and documentation generation.
 */
class MetadataRegistry
{
    /**
     * @var array Registered command metadata
     */
    private static $metadata = [];

    /**
     * @var array Category index
     */
    private static $categoryIndex = [];

    /**
     * @var array Tag index
     */
    private static $tagIndex = [];

    /**
     * @var array Alias index
     */
    private static $aliasIndex = [];

    /**
     * Register command metadata
     *
     * @param string $commandName Command name
     * @param array|CommandMetadata $metadata Metadata array or object
     * @return void
     */
    public static function register(string $commandName, $metadata): void
    {
        if (is_array($metadata)) {
            $metadata = new CommandMetadata($commandName, $metadata);
        }

        self::$metadata[$commandName] = $metadata;

        // Update indices
        self::indexByCategory($commandName, $metadata);
        self::indexByTags($commandName, $metadata);
        self::indexByAliases($commandName, $metadata);
    }

    /**
     * Get metadata for a command
     *
     * @param string $commandName Command name
     * @return CommandMetadata|null
     */
    public static function get(string $commandName): ?CommandMetadata
    {
        return self::$metadata[$commandName] ?? null;
    }

    /**
     * Check if command metadata exists
     *
     * @param string $commandName Command name
     * @return bool
     */
    public static function has(string $commandName): bool
    {
        return isset(self::$metadata[$commandName]);
    }

    /**
     * Get all registered metadata
     *
     * @return array
     */
    public static function all(): array
    {
        return self::$metadata;
    }

    /**
     * Get commands by category
     *
     * @param string $category Category name
     * @return array Array of CommandMetadata objects
     */
    public static function getByCategory(string $category): array
    {
        $commandNames = self::$categoryIndex[$category] ?? [];
        return array_map(function($name) {
            return self::$metadata[$name];
        }, $commandNames);
    }

    /**
     * Get commands by tag
     *
     * @param string $tag Tag name
     * @return array Array of CommandMetadata objects
     */
    public static function getByTag(string $tag): array
    {
        $commandNames = self::$tagIndex[$tag] ?? [];
        return array_map(function($name) {
            return self::$metadata[$name];
        }, $commandNames);
    }

    /**
     * Get all categories
     *
     * @return array Array of category names
     */
    public static function getCategories(): array
    {
        return array_keys(self::$categoryIndex);
    }

    /**
     * Get all tags
     *
     * @return array Array of tag names
     */
    public static function getTags(): array
    {
        return array_keys(self::$tagIndex);
    }

    /**
     * Find command by alias
     *
     * @param string $alias Command alias
     * @return string|null Command name
     */
    public static function findByAlias(string $alias): ?string
    {
        return self::$aliasIndex[$alias] ?? null;
    }

    /**
     * Search commands by query
     *
     * @param string $query Search query
     * @return array Array of CommandMetadata objects
     */
    public static function search(string $query): array
    {
        $query = strtolower($query);
        $results = [];

        foreach (self::$metadata as $commandName => $metadata) {
            // Search in command name
            if (strpos(strtolower($commandName), $query) !== false) {
                $results[] = $metadata;
                continue;
            }

            // Search in description
            if (strpos(strtolower($metadata->getDescription()), $query) !== false) {
                $results[] = $metadata;
                continue;
            }

            // Search in tags
            foreach ($metadata->getTags() as $tag) {
                if (strpos(strtolower($tag), $query) !== false) {
                    $results[] = $metadata;
                    break;
                }
            }
        }

        return $results;
    }

    /**
     * Clear all metadata
     *
     * @return void
     */
    public static function clear(): void
    {
        self::$metadata = [];
        self::$categoryIndex = [];
        self::$tagIndex = [];
        self::$aliasIndex = [];
    }

    /**
     * Index command by category
     *
     * @param string $commandName Command name
     * @param CommandMetadata $metadata Metadata object
     * @return void
     */
    private static function indexByCategory(string $commandName, CommandMetadata $metadata): void
    {
        $category = $metadata->getCategory();
        if (!isset(self::$categoryIndex[$category])) {
            self::$categoryIndex[$category] = [];
        }
        self::$categoryIndex[$category][] = $commandName;
    }

    /**
     * Index command by tags
     *
     * @param string $commandName Command name
     * @param CommandMetadata $metadata Metadata object
     * @return void
     */
    private static function indexByTags(string $commandName, CommandMetadata $metadata): void
    {
        foreach ($metadata->getTags() as $tag) {
            if (!isset(self::$tagIndex[$tag])) {
                self::$tagIndex[$tag] = [];
            }
            self::$tagIndex[$tag][] = $commandName;
        }
    }

    /**
     * Index command by aliases
     *
     * @param string $commandName Command name
     * @param CommandMetadata $metadata Metadata object
     * @return void
     */
    private static function indexByAliases(string $commandName, CommandMetadata $metadata): void
    {
        foreach ($metadata->getAliases() as $alias) {
            self::$aliasIndex[$alias] = $commandName;
        }
    }

    /**
     * Export metadata to array
     *
     * @return array
     */
    public static function export(): array
    {
        $export = [];
        foreach (self::$metadata as $commandName => $metadata) {
            $export[$commandName] = $metadata->toArray();
        }
        return $export;
    }

    /**
     * Load metadata from array
     *
     * @param array $data Metadata array
     * @return void
     */
    public static function load(array $data): void
    {
        foreach ($data as $commandName => $metadata) {
            self::register($commandName, $metadata);
        }
    }
}
