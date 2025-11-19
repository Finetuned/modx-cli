<?php

namespace MODX\CLI\Messages;

/**
 * Centralized error messages for MODX CLI
 *
 * This class provides a single source of truth for all error messages,
 * making them easier to maintain and prepare for future internationalization.
 */
class ErrorMessages
{
    // General errors
    const COMMAND_INIT_FAILED = 'command_init_failed';
    const COMMAND_NOT_FOUND = 'command_not_found';
    const OPERATION_ABORTED = 'operation_aborted';
    const UNKNOWN_ERROR = 'unknown_error';

    // MODX instance errors
    const MODX_NOT_FOUND = 'modx_not_found';
    const MODX_VERSION_INCOMPATIBLE = 'modx_version_incompatible';
    const MODX_INIT_FAILED = 'modx_init_failed';

    // Processor errors
    const PROCESSOR_FAILED = 'processor_failed';
    const PROCESSOR_NOT_FOUND = 'processor_not_found';
    const PROCESSOR_INVALID_RESPONSE = 'processor_invalid_response';

    // Resource errors
    const RESOURCE_NOT_FOUND = 'resource_not_found';
    const RESOURCE_CREATE_FAILED = 'resource_create_failed';
    const RESOURCE_UPDATE_FAILED = 'resource_update_failed';
    const RESOURCE_DELETE_FAILED = 'resource_delete_failed';

    // Object errors
    const OBJECT_NOT_FOUND = 'object_not_found';
    const OBJECT_CREATE_FAILED = 'object_create_failed';
    const OBJECT_UPDATE_FAILED = 'object_update_failed';
    const OBJECT_DELETE_FAILED = 'object_delete_failed';

    // Validation errors
    const INVALID_ARGUMENT = 'invalid_argument';
    const MISSING_REQUIRED_FIELD = 'missing_required_field';
    const INVALID_FIELD_VALUE = 'invalid_field_value';

    // Configuration errors
    const CONFIG_NOT_FOUND = 'config_not_found';
    const CONFIG_INVALID = 'config_invalid';
    const CONFIG_WRITE_FAILED = 'config_write_failed';
    const INSTANCE_NOT_FOUND = 'instance_not_found';

    // SSH/Remote errors
    const SSH_CONNECTION_FAILED = 'ssh_connection_failed';
    const SSH_COMMAND_FAILED = 'ssh_command_failed';
    const ALIAS_NOT_FOUND = 'alias_not_found';

    // File system errors
    const FILE_NOT_FOUND = 'file_not_found';
    const FILE_NOT_READABLE = 'file_not_readable';
    const FILE_NOT_WRITABLE = 'file_not_writable';
    const DIRECTORY_NOT_FOUND = 'directory_not_found';

    /**
     * @var array Message templates
     */
    private static $messages = [
        // General
        'command_init_failed' => 'Unable to initialize the command',
        'command_not_found' => 'Command not found',
        'operation_aborted' => 'Operation aborted',
        'unknown_error' => 'An unknown error occurred',

        // MODX instance
        'modx_not_found' => 'MODX instance not found. Please configure a MODX instance first.',
        'modx_version_incompatible' => 'MODX version {current} is not compatible. Minimum required: {required}',
        'modx_init_failed' => 'Failed to initialize MODX',

        // Processor
        'processor_failed' => 'Something went wrong while executing the processor',
        'processor_not_found' => 'Processor "{processor}" not found',
        'processor_invalid_response' => 'Invalid response from processor',

        // Resource
        'resource_not_found' => 'Resource with ID {id} not found',
        'resource_create_failed' => 'Failed to create resource',
        'resource_update_failed' => 'Failed to update resource with ID {id}',
        'resource_delete_failed' => 'Failed to delete resource with ID {id}',

        // Object
        'object_not_found' => '{class} with ID {id} not found',
        'object_create_failed' => 'Failed to create {class}',
        'object_update_failed' => 'Failed to update {class} with ID {id}',
        'object_delete_failed' => 'Failed to delete {class} with ID {id}',

        // Validation
        'invalid_argument' => 'Invalid argument: {argument}',
        'missing_required_field' => 'Missing required field: {field}',
        'invalid_field_value' => 'Invalid value for field "{field}": {value}',

        // Configuration
        'config_not_found' => 'Configuration file not found',
        'config_invalid' => 'Invalid configuration',
        'config_write_failed' => 'Failed to write configuration file',
        'instance_not_found' => 'Instance "{instance}" not found',

        // SSH/Remote
        'ssh_connection_failed' => 'Failed to connect to SSH host: {host}',
        'ssh_command_failed' => 'SSH command failed',
        'alias_not_found' => 'Alias "{alias}" not found',

        // File system
        'file_not_found' => 'File not found: {path}',
        'file_not_readable' => 'File is not readable: {path}',
        'file_not_writable' => 'File is not writable: {path}',
        'directory_not_found' => 'Directory not found: {path}',
    ];

    /**
     * Get a message by key
     *
     * @param string $key Message key
     * @return string The message template
     */
    public static function get(string $key): string
    {
        return self::$messages[$key] ?? $key;
    }

    /**
     * Format a message with parameters
     *
     * @param string $key Message key
     * @param array $params Parameters to substitute
     * @return string Formatted message
     */
    public static function format(string $key, array $params = []): string
    {
        $message = self::get($key);

        foreach ($params as $k => $v) {
            $message = str_replace('{' . $k . '}', (string)$v, $message);
        }

        return $message;
    }

    /**
     * Check if a message key exists
     *
     * @param string $key Message key
     * @return bool True if the key exists
     */
    public static function has(string $key): bool
    {
        return isset(self::$messages[$key]);
    }

    /**
     * Get all message keys
     *
     * @return array Array of message keys
     */
    public static function keys(): array
    {
        return array_keys(self::$messages);
    }

    /**
     * Get all messages
     *
     * @return array All messages
     */
    public static function all(): array
    {
        return self::$messages;
    }
}
