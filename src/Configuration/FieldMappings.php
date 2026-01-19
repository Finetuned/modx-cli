<?php

namespace MODX\CLI\Configuration;

/**
 * Field mappings configuration for MODX objects
 *
 * This class centralizes field mappings used for pre-populating data
 * in update operations, making them easier to maintain and extend.
 */
class FieldMappings
{
    /**
     * @var array Default field mappings for MODX objects
     */
    private static $defaultMappings = [
        'modChunk' => [
            'name' => 'name',
            'description' => 'description',
            'category' => 'category',
            'snippet' => 'snippet',
        ],
        'modTemplate' => [
            'templatename' => 'templatename',
            'description' => 'description',
            'category' => 'category',
            'content' => 'content',
            'icon' => 'icon',
        ],
        'modSnippet' => [
            'name' => 'name',
            'description' => 'description',
            'category' => 'category',
            'snippet' => 'snippet',
        ],
        'modPlugin' => [
            'name' => 'name',
            'description' => 'description',
            'category' => 'category',
            'plugincode' => 'plugincode',
        ],
        'modTemplateVar' => [
            'name' => 'name',
            'caption' => 'caption',
            'description' => 'description',
            'category' => 'category',
            'type' => 'type',
            'default_text' => 'default_text',
            'input_properties' => 'input_properties',
            'output_properties' => 'output_properties',
        ],
        'modResource' => [
            'pagetitle' => 'pagetitle',
            'parent' => 'parent',
            'template' => 'template',
            'published' => 'published',
            'class_key' => 'class_key',        // CRITICAL - prevents null classKey error
            'context_key' => 'context_key',    // CRITICAL - required by processor
            'content_type' => 'content_type',  // Usually defaults to 1
            'alias' => 'alias',
            'content' => 'content',
            'longtitle' => 'longtitle',
            'description' => 'description',
            'introtext' => 'introtext',
            'link_attributes' => 'link_attributes',
            'hidemenu' => 'hidemenu',
            'searchable' => 'searchable',
            'cacheable' => 'cacheable',
            'deleted' => 'deleted',
            'publishedon' => 'publishedon',
            'pub_date' => 'pub_date',
            'unpub_date' => 'unpub_date',
            'menutitle' => 'menutitle',
            'menuindex' => 'menuindex',
        ],
        'modCategory' => [
            'category' => 'category',
            'parent' => 'parent',
        ],
        'modUser' => [
            'username' => 'username',
            'active' => 'active',
        ],
        'modUserProfile' => [
            'fullname' => 'fullname',
            'email' => 'email',
            'phone' => 'phone',
            'mobilephone' => 'mobilephone',
            'blocked' => 'blocked',
        ],
        'modContext' => [
            'key' => 'key',
            'description' => 'description',
        ],
        'modNamespace' => [
            'name' => 'name',
            'path' => 'path',
            'assets_path' => 'assets_path',
        ],
    ];

    /**
     * @var array Custom mappings loaded from configuration
     */
    private static $customMappings = [];

    /**
     * @var bool Whether custom mappings have been loaded
     */
    private static $loaded = false;

    /**
     * Get field mapping for a class
     *
     * @param string $class MODX object class name.
     * @return array Field mapping array.
     */
    public static function get(string $class): array
    {
        self::loadCustomMappings();

        $normalized = $class;
        if (str_contains($class, '\\')) {
            $normalized = substr($class, strrpos($class, '\\') + 1);
        }

        if (isset(self::$customMappings[$class])) {
            return self::$customMappings[$class];
        }
        if (isset(self::$customMappings[$normalized])) {
            return self::$customMappings[$normalized];
        }

        if (isset(self::$defaultMappings[$class])) {
            return self::$defaultMappings[$class];
        }

        return self::$defaultMappings[$normalized] ?? [];
    }

    /**
     * Check if a mapping exists for a class
     *
     * @param string $class MODX object class name.
     * @return boolean True if mapping exists.
     */
    public static function has(string $class): bool
    {
        self::loadCustomMappings();

        return isset(self::$customMappings[$class]) || isset(self::$defaultMappings[$class]);
    }

    /**
     * Set a custom field mapping for a class
     *
     * @param string $class   MODX object class name.
     * @param array  $mapping Field mapping array.
     * @return void
     */
    public static function set(string $class, array $mapping): void
    {
        self::$customMappings[$class] = $mapping;
    }

    /**
     * Merge custom mapping with default mapping
     *
     * @param string $class   MODX object class name.
     * @param array  $mapping Additional field mappings.
     * @return void
     */
    public static function extend(string $class, array $mapping): void
    {
        $default = self::$defaultMappings[$class] ?? [];
        self::$customMappings[$class] = array_merge($default, $mapping);
    }

    /**
     * Get all default mappings
     *
     * @return array All default mappings
     */
    public static function getDefaults(): array
    {
        return self::$defaultMappings;
    }

    /**
     * Get all custom mappings
     *
     * @return array All custom mappings
     */
    public static function getCustom(): array
    {
        self::loadCustomMappings();
        return self::$customMappings;
    }

    /**
     * Reset all custom mappings
     *
     * @return void
     */
    public static function reset(): void
    {
        self::$customMappings = [];
        self::$loaded = false;
    }

    /**
     * Load custom mappings from configuration file
     *
     * @return void
     */
    private static function loadCustomMappings(): void
    {
        if (self::$loaded) {
            return;
        }

        self::$loaded = true;

        // Try to load from user config
        $userConfig = self::getConfigPath() . 'field-mappings.json';
        if (file_exists($userConfig)) {
            $data = json_decode(file_get_contents($userConfig), true);
            if (is_array($data)) {
                self::$customMappings = array_merge(self::$customMappings, $data);
            }
        }

        // Try to load from project config
        $projectConfig = getcwd() . '/modx-cli-field-mappings.json';
        if (file_exists($projectConfig)) {
            $data = json_decode(file_get_contents($projectConfig), true);
            if (is_array($data)) {
                self::$customMappings = array_merge(self::$customMappings, $data);
            }
        }
    }

    /**
     * Get the configuration directory path
     *
     * @return string Configuration directory path with trailing slash
     */
    private static function getConfigPath(): string
    {
        $home = getenv('HOME') ?: getenv('USERPROFILE');
        if (!$home) {
            return '';
        }

        return rtrim($home, '/\\') . '/.modx/';
    }

    /**
     * Save custom mappings to user configuration file
     *
     * @return boolean True if saved successfully
     */
    public static function save(): bool
    {
        $configPath = self::getConfigPath();

        if (!$configPath) {
            return false;
        }

        if (!is_dir($configPath)) {
            mkdir($configPath, 0755, true);
        }

        $configFile = $configPath . 'field-mappings.json';
        $json = json_encode(self::$customMappings, JSON_PRETTY_PRINT);

        return file_put_contents($configFile, $json) !== false;
    }
}
