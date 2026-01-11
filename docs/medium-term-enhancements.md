# Medium-Term Enhancements Documentation

This document describes the medium-term enhancements implemented to improve code maintainability and extensibility.

## Overview

Three major architectural improvements have been implemented:

1. **Centralized Error Messages** - Single source of truth for all error messages
2. **Centralized Field Mappings** - Configurable field mappings for MODX objects
3. **Command Metadata Registry** - Enhanced command discovery and organization

Note: This document reflects completed enhancements. For parity validation and documentation workflows, see the Task 16/17 memory bank notes.

---

## 1. Centralized Error Messages

### Purpose

Provides a centralized system for managing error messages, making them:
- Easier to maintain
- Consistent across the application
- Prepared for future internationalization (i18n)
- Searchable and documentable

### Location

`src/Messages/ErrorMessages.php`

### Usage

#### Basic Usage

```php
use MODX\CLI\Messages\ErrorMessages;

// Get a simple message
$this->error(ErrorMessages::get(ErrorMessages::PROCESSOR_FAILED));
// Output: "Something went wrong while executing the processor"

// Format a message with parameters
$message = ErrorMessages::format(ErrorMessages::RESOURCE_NOT_FOUND, [
    'id' => 123
]);
// Output: "Resource with ID 123 not found"
```

#### Available Message Categories

**General Errors:**
- `COMMAND_INIT_FAILED`
- `COMMAND_NOT_FOUND`
- `OPERATION_ABORTED`
- `UNKNOWN_ERROR`

**MODX Instance Errors:**
- `MODX_NOT_FOUND`
- `MODX_VERSION_INCOMPATIBLE`
- `MODX_INIT_FAILED`

**Processor Errors:**
- `PROCESSOR_FAILED`
- `PROCESSOR_NOT_FOUND`
- `PROCESSOR_INVALID_RESPONSE`

**Resource Errors:**
- `RESOURCE_NOT_FOUND`
- `RESOURCE_CREATE_FAILED`
- `RESOURCE_UPDATE_FAILED`
- `RESOURCE_DELETE_FAILED`

**Object Errors:**
- `OBJECT_NOT_FOUND`
- `OBJECT_CREATE_FAILED`
- `OBJECT_UPDATE_FAILED`
- `OBJECT_DELETE_FAILED`

**Validation Errors:**
- `INVALID_ARGUMENT`
- `MISSING_REQUIRED_FIELD`
- `INVALID_FIELD_VALUE`

**Configuration Errors:**
- `CONFIG_NOT_FOUND`
- `CONFIG_INVALID`
- `CONFIG_WRITE_FAILED`
- `INSTANCE_NOT_FOUND`

**SSH/Remote Errors:**
- `SSH_CONNECTION_FAILED`
- `SSH_COMMAND_FAILED`
- `ALIAS_NOT_FOUND`

**File System Errors:**
- `FILE_NOT_FOUND`
- `FILE_NOT_READABLE`
- `FILE_NOT_WRITABLE`
- `DIRECTORY_NOT_FOUND`

### API Methods

```php
// Get message by key
ErrorMessages::get(string $key): string

// Format message with parameters
ErrorMessages::format(string $key, array $params = []): string

// Check if message exists
ErrorMessages::has(string $key): bool

// Get all message keys
ErrorMessages::keys(): array

// Get all messages
ErrorMessages::all(): array
```

### Example: Custom Command Using Error Messages

```php
<?php

namespace MODX\CLI\Command\Custom;

use MODX\CLI\Command\ProcessorCmd;
use MODX\CLI\Messages\ErrorMessages;

class MyCommand extends ProcessorCmd
{
    protected function process()
    {
        $resourceId = $this->argument('id');

        if (!$resourceId) {
            $this->error(ErrorMessages::format(
                ErrorMessages::MISSING_REQUIRED_FIELD,
                ['field' => 'id']
            ));
            return 1;
        }

        $resource = $this->modx->getObject('modResource', $resourceId);

        if (!$resource) {
            $this->error(ErrorMessages::format(
                ErrorMessages::RESOURCE_NOT_FOUND,
                ['id' => $resourceId]
            ));
            return 1;
        }

        // Process resource...
        return 0;
    }
}
```

---

## 2. Centralized Field Mappings

### Purpose

Eliminates hardcoded field mappings in `ProcessorCmd`, providing:
- Centralized configuration for object field mappings
- User-customizable mappings via configuration files
- Project-specific overrides
- Easier maintenance and extensibility

### Location

`src/Configuration/FieldMappings.php`

### Default Mappings

Field mappings are provided for common MODX objects:

- `modChunk` - name, description, category, snippet
- `modTemplate` - templatename, description, category, content, icon
- `modSnippet` - name, description, category, snippet
- `modPlugin` - name, description, category, plugincode
- `modTemplateVar` - name, caption, description, category, type, default_text
- `modResource` - pagetitle, parent, template, published, class_key, context_key, etc.
- `modCategory` - category, parent
- `modUser` - username, active
- `modUserProfile` - fullname, email, phone, mobilephone, blocked
- `modContext` - key, description
- `modNamespace` - name, path, assets_path

### Usage

#### Get Field Mapping

```php
use MODX\CLI\Configuration\FieldMappings;

// Get mapping for a class
$mapping = FieldMappings::get('modResource');
// Returns: ['pagetitle' => 'pagetitle', 'parent' => 'parent', ...]

// Check if mapping exists
if (FieldMappings::has('modResource')) {
    // ...
}
```

#### Custom Mappings

**Option 1: User Configuration File**

Create `~/.modx/field-mappings.json`:

```json
{
    "modResource": {
        "pagetitle": "pagetitle",
        "longtitle": "longtitle",
        "custom_field": "custom_field"
    },
    "MyCustomClass": {
        "field1": "field1",
        "field2": "field2"
    }
}
```

**Option 2: Project Configuration File**

Create `modx-cli-field-mappings.json` in your project root:

```json
{
    "MyProjectClass": {
        "name": "name",
        "value": "value"
    }
}
```

#### Programmatic Usage

```php
use MODX\CLI\Configuration\FieldMappings;

// Set custom mapping
FieldMappings::set('MyClass', [
    'field1' => 'field1',
    'field2' => 'field2'
]);

// Extend default mapping
FieldMappings::extend('modResource', [
    'custom_field' => 'custom_field'
]);

// Save to user config
FieldMappings::save();
```

### API Methods

```php
// Get mapping for a class
FieldMappings::get(string $class): array

// Check if mapping exists
FieldMappings::has(string $class): bool

// Set custom mapping
FieldMappings::set(string $class, array $mapping): void

// Extend default mapping
FieldMappings::extend(string $class, array $mapping): void

// Get all default mappings
FieldMappings::getDefaults(): array

// Get all custom mappings
FieldMappings::getCustom(): array

// Reset custom mappings
FieldMappings::reset(): void

// Save to user config
FieldMappings::save(): bool
```

### Integration with ProcessorCmd

The `prePopulateFromExisting()` method in `ProcessorCmd` now automatically uses `FieldMappings`:

```php
// Before (hardcoded)
$defaultMappings = array(
    'modChunk' => array('name' => 'name', ...),
    // ...
);

// After (centralized)
$mapping = FieldMappings::get($class);
```

This change is **backward compatible** - custom field maps can still be passed as parameters.

---

## 3. Command Metadata Registry

### Purpose

Provides a centralized registry for command metadata, enabling:
- Command discovery and filtering
- Category-based organization
- Tag-based searching
- Command aliasing
- Documentation generation
- Related command suggestions

### Location

- `src/Registry/CommandMetadata.php` - Metadata class
- `src/Registry/MetadataRegistry.php` - Registry class

### Usage

#### Registering Command Metadata

```php
use MODX\CLI\Registry\MetadataRegistry;

// Register with array
MetadataRegistry::register('resource:list', [
    'category' => 'resource',
    'tags' => ['content', 'crud', 'list'],
    'minModxVersion' => '3.0.0',
    'aliases' => ['r:l', 'res:list'],
    'description' => 'List all resources',
    'relatedCommands' => ['resource:get', 'resource:create'],
]);

// Register with object
use MODX\CLI\Registry\CommandMetadata;

$metadata = new CommandMetadata('resource:create', [
    'category' => 'resource',
    'tags' => ['content', 'crud', 'create'],
    'description' => 'Create a new resource',
]);
MetadataRegistry::register('resource:create', $metadata);
```

#### Querying Metadata

```php
use MODX\CLI\Registry\MetadataRegistry;

// Get metadata for a specific command
$metadata = MetadataRegistry::get('resource:list');

// Get all commands in a category
$resourceCommands = MetadataRegistry::getByCategory('resource');

// Get all commands with a tag
$crudCommands = MetadataRegistry::getByTag('crud');

// Search commands
$results = MetadataRegistry::search('resource');

// Find command by alias
$commandName = MetadataRegistry::findByAlias('r:l');
// Returns: 'resource:list'

// Get all categories
$categories = MetadataRegistry::getCategories();

// Get all tags
$tags = MetadataRegistry::getTags();
```

### CommandMetadata API

```php
$metadata = MetadataRegistry::get('resource:list');

// Getters
$metadata->getName();              // 'resource:list'
$metadata->getCategory();          // 'resource'
$metadata->getTags();              // ['content', 'crud', 'list']
$metadata->getMinModxVersion();    // '3.0.0'
$metadata->getAliases();           // ['r:l', 'res:list']
$metadata->getDescription();       // 'List all resources'
$metadata->getRelatedCommands();   // ['resource:get', 'resource:create']
$metadata->getCustom('key');       // Custom metadata

// Checks
$metadata->hasTag('crud');         // true
$metadata->hasAlias('r:l');        // true

// Export
$metadata->toArray();              // Convert to array
```

### MetadataRegistry API

```php
// Registration
MetadataRegistry::register(string $commandName, $metadata): void

// Retrieval
MetadataRegistry::get(string $commandName): ?CommandMetadata
MetadataRegistry::has(string $commandName): bool
MetadataRegistry::all(): array

// Filtering
MetadataRegistry::getByCategory(string $category): array
MetadataRegistry::getByTag(string $tag): array
MetadataRegistry::search(string $query): array
MetadataRegistry::findByAlias(string $alias): ?string

// Information
MetadataRegistry::getCategories(): array
MetadataRegistry::getTags(): array

// Import/Export
MetadataRegistry::export(): array
MetadataRegistry::load(array $data): void

// Utility
MetadataRegistry::clear(): void
```

### Example: Enhanced List Command

```php
<?php

namespace MODX\CLI\Command;

use MODX\CLI\Registry\MetadataRegistry;
use Symfony\Component\Console\Input\InputOption;

class EnhancedListCommand extends BaseCmd
{
    protected $name = 'command:list';
    protected $description = 'List all commands with filtering';

    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['category', 'c', InputOption::VALUE_REQUIRED, 'Filter by category'],
            ['tag', 't', InputOption::VALUE_REQUIRED, 'Filter by tag'],
        ]);
    }

    protected function process()
    {
        $category = $this->option('category');
        $tag = $this->option('tag');

        if ($category) {
            $commands = MetadataRegistry::getByCategory($category);
        } elseif ($tag) {
            $commands = MetadataRegistry::getByTag($tag);
        } else {
            $commands = MetadataRegistry::all();
        }

        foreach ($commands as $metadata) {
            $this->line(sprintf(
                '<info>%s</info> - %s',
                $metadata->getName(),
                $metadata->getDescription()
            ));

            if (!empty($metadata->getTags())) {
                $this->comment('  Tags: ' . implode(', ', $metadata->getTags()));
            }
        }

        return 0;
    }
}
```

---

## Configuration File Locations

### Error Messages
- Built into `src/Messages/ErrorMessages.php`
- Future: Support for `~/.modx/messages.json` for custom messages

### Field Mappings
- Defaults: `src/Configuration/FieldMappings.php`
- User config: `~/.modx/field-mappings.json`
- Project config: `{project}/modx-cli-field-mappings.json`

### Command Metadata
- Registered programmatically in command classes
- Future: Support for `~/.modx/command-metadata.json`

---

## Benefits

### Centralized Error Messages
✅ Single source of truth for error messages
✅ Easy to update and maintain
✅ Prepared for internationalization
✅ Consistent user experience
✅ Searchable and documentable

### Centralized Field Mappings
✅ Removed hardcoded mappings from ProcessorCmd
✅ User-customizable per project or globally
✅ Easy to add support for custom MODX objects
✅ Cleaner, more maintainable code
✅ Backward compatible with custom field maps

### Command Metadata Registry
✅ Better command organization and discovery
✅ Category and tag-based filtering
✅ Command aliasing support
✅ Foundation for auto-documentation
✅ Related command suggestions
✅ Enhanced user experience

---

## Migration Guide

### For Error Messages

**Before:**
```php
$this->error('Something went wrong while executing the processor');
```

**After:**
```php
use MODX\CLI\Messages\ErrorMessages;

$this->error(ErrorMessages::get(ErrorMessages::PROCESSOR_FAILED));
```

### For Field Mappings

**Before:**
```php
// Hardcoded in ProcessorCmd
$defaultMappings = array(...);
```

**After:**
```php
use MODX\CLI\Configuration\FieldMappings;

$mapping = FieldMappings::get($class);
```

No code changes needed in commands - update happens automatically!

### For Command Metadata

**New Feature - Add to Your Commands:**
```php
// In your command's constructor or a static method
use MODX\CLI\Registry\MetadataRegistry;

public function __construct()
{
    parent::__construct();

    MetadataRegistry::register($this->name, [
        'category' => 'resource',
        'tags' => ['crud', 'content'],
        'description' => $this->description,
    ]);
}
```

---

## Future Enhancements

### Error Messages
- Load custom messages from JSON file
- Support for multiple languages (i18n)
- Error code system for programmatic handling

### Field Mappings
- GUI for managing mappings
- Automatic detection from MODX schema
- Validation rules alongside mappings

### Command Metadata
- Auto-registration via attributes/annotations
- Enhanced documentation generation
- Command dependency tracking
- Usage statistics and recommendations

---

## Testing

All three systems have been integrated and tested:

✅ ErrorMessages used in ProcessorCmd
✅ FieldMappings used in ProcessorCmd
✅ MetadataRegistry ready for command registration

No breaking changes - all existing functionality preserved.

---

## Additional Resources

- [Error Messages Source](../src/Messages/ErrorMessages.php)
- [Field Mappings Source](../src/Configuration/FieldMappings.php)
- [Command Metadata Source](../src/Registry/CommandMetadata.php)
- [Metadata Registry Source](../src/Registry/MetadataRegistry.php)
- [ProcessorCmd Integration](../src/Command/ProcessorCmd.php)
