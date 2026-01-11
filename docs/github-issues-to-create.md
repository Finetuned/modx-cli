# GitHub Issues to Create

This document contains issue templates for major initiatives identified in the codebase review. Copy these into GitHub issues.

---

## Issue #1: Add PHP Type Declarations Throughout Codebase

**Labels**: `enhancement`, `code-quality`, `good-first-issue`
**Milestone**: 2025
**Assignee**: TBD

### Description

Add return type declarations and parameter type hints throughout the codebase to improve code quality, IDE support, and reduce runtime errors.

### Current Behavior

Most methods lack return type declarations:
```php
public function getMODX()
{
    // ...
}
```

### Desired Behavior

All methods should have proper type declarations:
```php
public function getMODX(): ?\MODX\Revolution\modX
{
    // ...
}
```

### Scope

- `src/Application.php` - All methods
- `src/Command/BaseCmd.php` - All methods
- `src/Command/ProcessorCmd.php` - All methods
- All command classes in `src/Command/`
- All configuration classes in `src/Configuration/`
- API classes in `src/API/`

### Benefits

- Better IDE autocompletion and inline documentation
- Reduced runtime type errors
- Improved code maintainability
- Better static analysis support (PHPStan/Psalm)

### Implementation Plan

**Phase 1**: Core classes (1 week)
- Application.php
- BaseCmd.php
- ProcessorCmd.php
- ListProcessor.php

**Phase 2**: Command classes (1 week)
- All commands in src/Command/

**Phase 3**: Supporting classes (1 week)
- Configuration classes
- API classes
- Formatters, SSH, Alias classes

### Acceptance Criteria

- [ ] All public methods have return type declarations
- [ ] All protected methods have return type declarations
- [ ] Parameters have type hints where applicable
- [ ] PHPDoc blocks updated to match type declarations
- [ ] All tests pass
- [ ] No breaking changes to public API

### Estimated Effort

2-3 weeks

---

## Issue #2: Add Static Analysis with PHPStan

**Labels**: `enhancement`, `code-quality`, `tooling`
**Milestone**: 2025
**Assignee**: TBD

### Description

Add PHPStan for static analysis to catch bugs before runtime and improve code quality.

### Current Behavior

No static analysis tool configured. Potential type errors and bugs go undetected until runtime.

### Desired Behavior

PHPStan configured and running at level 5-6, integrated into CI/CD pipeline.

### Implementation Plan

1. Add PHPStan to composer.json dev dependencies
   ```bash
   composer require --dev phpstan/phpstan
   ```

2. Create `phpstan.neon` configuration
   ```neon
   parameters:
       level: 5
       paths:
           - src
       excludePaths:
           - src/Xdom.php
   ```

3. Add composer scripts
   ```json
   "scripts": {
       "analyse": "phpstan analyse",
       "analyse:baseline": "phpstan analyse --generate-baseline"
   }
   ```

4. Fix identified issues at level 5
5. Gradually increase to level 6+
6. Add to CI/CD pipeline

### Acceptance Criteria

- [ ] PHPStan installed and configured
- [ ] Running at minimum level 5
- [ ] All identified issues at level 5 fixed
- [ ] Composer script added for easy execution
- [ ] Documentation updated
- [ ] CI/CD integration (if applicable)

### Estimated Effort

1 week

---

## Issue #3: Centralize Field Mappings Configuration

**Labels**: `enhancement`, `refactoring`, `maintainability`
**Milestone**: 2025
**Assignee**: TBD

### Description

Move hardcoded field mappings from ProcessorCmd to a centralized configuration system for better maintainability and extensibility.

### Current Behavior

Field mappings are hardcoded in `ProcessorCmd::prePopulateFromExisting()` (lines 187-207):

```php
$defaultMappings = array(
    'modChunk' => array('name' => 'name', 'description' => 'description', ...),
    'modTemplate' => array('templatename' => 'templatename', ...),
    // ...
);
```

### Problems

- Hard to maintain
- Cannot be customized per instance
- Not extensible for custom MODX objects
- Mixed concerns (logic + data)

### Desired Behavior

Field mappings should be:
- Stored in configuration files (JSON/YAML)
- Loadable from instance-specific configs
- Extensible for custom objects
- Overridable in custom commands

### Implementation Plan

1. Create `src/Configuration/FieldMappings.php` class
2. Create `config/field-mappings.json` with default mappings
3. Support instance-specific overrides in `.modx/field-mappings.json`
4. Update `ProcessorCmd` to use configuration
5. Add documentation for custom field mappings

### File Structure

```
config/
  └── field-mappings.json       # Default mappings
~/.modx/
  └── field-mappings.json       # User overrides
project/
  └── modx-cli.yml
        └── field_mappings:     # Project-specific
```

### Acceptance Criteria

- [ ] FieldMappings configuration class created
- [ ] Default mappings in JSON/YAML file
- [ ] Support for instance-specific overrides
- [ ] ProcessorCmd refactored to use configuration
- [ ] Backward compatibility maintained
- [ ] Documentation added
- [ ] Tests updated

### Estimated Effort

1-2 weeks

---

## Issue #4: Implement Command Metadata Registry

**Labels**: `enhancement`, `architecture`, `documentation`
**Milestone**: Q2 2025
**Assignee**: TBD

### Description

Create a central registry for command metadata to enable better organization, documentation generation, and command discovery.

### Current Behavior

Command metadata is scattered across individual command classes with no central registry.

### Desired Behavior

Centralized metadata registry supporting:
- Command categories and tags
- Version requirements
- Related commands
- Auto-generated documentation
- Command aliases

### Use Cases

1. **Command Discovery**: Find all commands in a category
   ```bash
   modx list --category=resource
   ```

2. **Auto-Documentation**: Generate reference docs
   ```bash
   modx docs:generate
   ```

3. **Command Aliases**: Support shortcuts
   ```bash
   modx r:l  # Alias for resource:list
   ```

### Implementation Plan

1. Create `src/Registry/CommandMetadata.php`
2. Create `src/Registry/MetadataRegistry.php`
3. Add metadata to existing commands via attributes or annotations
4. Implement registry loading and querying
5. Update `list` command to use metadata
6. Create documentation generation command

### Example Usage

```php
#[CommandMetadata(
    category: 'resource',
    tags: ['content', 'crud'],
    minModxVersion: '3.0.0',
    aliases: ['r:l']
)]
class GetList extends ListProcessor
{
    // ...
}
```

### Acceptance Criteria

- [ ] CommandMetadata class created
- [ ] MetadataRegistry implemented
- [ ] Metadata added to core commands
- [ ] list command enhanced with filtering
- [ ] Documentation generation working
- [ ] Tests added

### Estimated Effort

2 weeks

---

## Issue #5: Centralize Error Messages

**Labels**: `enhancement`, `maintainability`, `i18n-prep`
**Milestone**: Q1 2025
**Assignee**: TBD

### Description

Centralize error messages to improve consistency and prepare for future internationalization.

### Current Behavior

Error messages are hardcoded throughout command classes:

```php
$this->error('Something went wrong while executing the processor');
$this->error('Unable to init the command!');
```

### Problems

- Inconsistent messaging
- Hard to translate
- Difficult to maintain
- Duplication across commands

### Desired Behavior

Centralized, templated error messages:

```php
$this->error(ErrorMessages::PROCESSOR_FAILED);
$this->error(ErrorMessages::format('RESOURCE_NOT_FOUND', ['id' => $id]));
```

### Implementation Plan

1. Create `src/Messages/ErrorMessages.php`
2. Create `src/Messages/MessageFormatter.php` for templating
3. Define common error messages as constants
4. Refactor commands to use centralized messages
5. Support message parameters/templating

### Example Implementation

```php
namespace MODX\CLI\Messages;

class ErrorMessages
{
    const PROCESSOR_FAILED = 'processor_failed';
    const RESOURCE_NOT_FOUND = 'resource_not_found';
    const COMMAND_INIT_FAILED = 'command_init_failed';

    private static $messages = [
        'processor_failed' => 'Something went wrong while executing the processor',
        'resource_not_found' => 'Resource with ID {id} not found',
        'command_init_failed' => 'Unable to initialize the command',
    ];

    public static function get(string $key): string
    {
        return self::$messages[$key] ?? $key;
    }

    public static function format(string $key, array $params): string
    {
        $message = self::get($key);
        foreach ($params as $k => $v) {
            $message = str_replace("{{$k}}", $v, $message);
        }
        return $message;
    }
}
```

### Acceptance Criteria

- [ ] ErrorMessages class created
- [ ] MessageFormatter implemented
- [ ] All common errors centralized
- [ ] Commands refactored to use centralized messages
- [ ] Support for message templating
- [ ] Documentation added
- [ ] Tests updated

### Estimated Effort

3-5 days

---

## Issue #6: Add Windows Configuration Path Support

**Labels**: `bug`, `enhancement`, `cross-platform`
**Milestone**: Q1 2025
**Assignee**: TBD

### Description

Add proper support for Windows configuration paths to make MODX CLI fully cross-platform.

### Current Behavior

Configuration path detection may not work correctly on Windows systems.

### Desired Behavior

Proper configuration path resolution on Windows:
- `C:\Users\{username}\.modx\` for user config
- Project-relative paths working correctly
- Path separator handling

### Implementation Plan

1. Research Windows config path conventions
2. Update `src/Configuration/Base.php::getConfigPath()`
3. Add Windows-specific path handling
4. Test on Windows environment
5. Update installation documentation

### Platform Paths

- **Linux/Mac**: `~/.modx/`
- **Windows**: `%USERPROFILE%\.modx\` or `%APPDATA%\modx-cli\`

### Files to Modify

- `src/Configuration/Base.php`

### Acceptance Criteria

- [ ] Windows config path properly detected
- [ ] Path separators handled correctly
- [ ] Tests pass on Windows
- [ ] Documentation updated for Windows users
- [ ] Cross-platform CI tests added (if applicable)

### Estimated Effort

2-3 days

---

## Issue #7: Implement Enhanced Logging System

**Labels**: `enhancement`, `feature`, `logging`
**Milestone**: Q2 2025
**Assignee**: TBD

### Description

Implement a comprehensive logging system with PSR-3 interface, log levels, rotation, and file output.

### Current Behavior

Limited logging capabilities, mostly console output.

### Desired Behavior

Full-featured logging system:
- PSR-3 compliant
- Log levels (DEBUG, INFO, WARNING, ERROR)
- File and console output
- Log rotation
- Configurable verbosity

### Features

1. **Log Levels**
   ```bash
   modx resource:list --log-level=debug
   ```

2. **File Logging**
   ```bash
   modx resource:list --log-file=operations.log
   ```

3. **Verbosity Control**
   ```bash
   modx resource:list -v      # Verbose
   modx resource:list -vv     # Very verbose
   modx resource:list -q      # Quiet
   ```

### Implementation Plan

1. Install PSR-3 logger (Monolog)
2. Create `src/Logging/Logger.php` wrapper
3. Add global verbosity options to Application
4. Integrate with BaseCmd
5. Add log file configuration
6. Implement log rotation

### Acceptance Criteria

- [ ] PSR-3 logger integrated
- [ ] Log levels implemented
- [ ] File logging working
- [ ] Verbosity flags functional
- [ ] Log rotation configured
- [ ] Documentation added
- [ ] Tests added

### Estimated Effort

1-2 weeks

---

## Issue #8: Upgrade to PHP 8.x

**Labels**: `enhancement`, `modernization`, `breaking-change`
**Milestone**: Q2-Q3 2025
**Assignee**: TBD

### Description

Upgrade minimum PHP version from 7.4 to 8.0 or 8.1 to leverage modern PHP features.

### Current Behavior

Minimum PHP 7.4 requirement limits use of modern features.

### Benefits of PHP 8.x

- Named arguments
- Constructor property promotion
- Union types
- Match expressions
- Nullsafe operator
- Better performance
- Better type system

### Migration Plan

**Phase 1: Preparation**
- [ ] Add PHP 8.0 to test matrix
- [ ] Fix any PHP 8.x compatibility issues
- [ ] Update all dependencies for PHP 8.x

**Phase 2: PHP 8.0 Features**
- [ ] Use constructor property promotion
- [ ] Replace `strpos` with `str_contains`, `str_starts_with`
- [ ] Use named arguments where beneficial
- [ ] Add union types

**Phase 3: PHP 8.1+ Features** (if moving to 8.1)
- [ ] Use enums for constants
- [ ] Implement readonly properties
- [ ] Use `never` return type
- [ ] First-class callable syntax

### Breaking Changes

- Update `composer.json`: `"php": ">=8.0"`
- May require MODX 3.x update
- User systems must have PHP 8.0+

### Migration Guide

Need to create comprehensive guide for users including:
- PHP upgrade instructions
- Compatibility checks
- Fallback options

### Acceptance Criteria

- [ ] Minimum PHP version updated
- [ ] All PHP 8.x features utilized appropriately
- [ ] All tests pass on PHP 8.x
- [ ] Dependencies updated
- [ ] Migration guide created
- [ ] CHANGELOG updated
- [ ] Major version bump

### Estimated Effort

4-6 weeks

---

## Issue #9: Create Plugin Architecture

**Labels**: `enhancement`, `feature`, `architecture`
**Milestone**: Q3 2025
**Assignee**: TBD

### Description

Design and implement a plugin architecture for third-party extensibility.

### Current Behavior

Extensions must be added via composer or manual file inclusion. No standardized plugin system.

### Desired Behavior

Full plugin system supporting:
- Plugin discovery and loading
- Plugin lifecycle hooks
- Plugin management commands
- Plugin marketplace integration

### Use Cases

1. **Install Plugin**
   ```bash
   modx plugin:install vendor/plugin-name
   ```

2. **List Plugins**
   ```bash
   modx plugin:list
   ```

3. **Enable/Disable**
   ```bash
   modx plugin:enable plugin-name
   modx plugin:disable plugin-name
   ```

### Plugin Structure

```
plugins/
  └── vendor/
        └── plugin-name/
              ├── plugin.json          # Metadata
              ├── src/
              │   └── Plugin.php       # Entry point
              └── commands/            # Plugin commands
```

### Implementation Plan

1. Design plugin interface and lifecycle
2. Create plugin discovery mechanism
3. Implement plugin loader
4. Add plugin management commands
5. Create plugin development guide
6. Build example plugins

### Acceptance Criteria

- [ ] Plugin interface defined
- [ ] Plugin discovery working
- [ ] Plugin lifecycle implemented
- [ ] Management commands created
- [ ] Documentation complete
- [ ] Example plugins created
- [ ] Tests added

### Estimated Effort

4-6 weeks

---

## Issue #10: Implement Command Output Streaming

**Labels**: `enhancement`, `feature`, `performance`
**Milestone**: Q3 2025
**Assignee**: TBD

### Description

Add support for streaming output for long-running commands with progress bars and real-time updates.

### Current Behavior

Commands wait until completion before showing output, making long operations appear hung.

### Desired Behavior

Real-time progress feedback:
- Progress bars for operations
- Streaming output for logs
- Async command execution
- Cancelable operations

### Use Cases

1. **Package Installation**
   ```
   Installing package...
   [==============>           ] 65% - Copying files...
   ```

2. **Cache Clearing**
   ```
   Clearing cache...
   ✓ Cleared resource cache (120 items)
   ✓ Cleared template cache (45 items)
   ⏳ Clearing db cache...
   ```

3. **Resource Crawling**
   ```
   Crawling resources...
   [====================] 100% - 150/150 resources
   ```

### Implementation Plan

1. Add Symfony ProgressBar component
2. Implement streaming output handler
3. Add progress tracking to long operations
4. Support async execution
5. Add cancellation handling (Ctrl+C)

### Acceptance Criteria

- [ ] Progress bars implemented
- [ ] Streaming output working
- [ ] Async execution supported
- [ ] Graceful cancellation
- [ ] Applied to long-running commands
- [ ] Documentation added
- [ ] Tests added

### Estimated Effort

2-3 weeks

---

## Issue Priority Summary

### Immediate (This Week)
- ✅ Configure PHP_CodeSniffer
- ✅ Review and prioritize TODOs
- ✅ Create GitHub issues

### High Priority (Q1 2025)
- #1: Add PHP Type Declarations
- #2: Add Static Analysis (PHPStan)
- #3: Centralize Field Mappings
- #5: Centralize Error Messages
- #6: Windows Configuration Path Support

### Medium Priority (Q2 2025)
- #4: Command Metadata Registry
- #7: Enhanced Logging System
- #8: PHP 8.x Upgrade (Preparation)

### Long-term (Q3 2025)
- #8: PHP 8.x Upgrade (Implementation)
- #9: Plugin Architecture
- #10: Command Output Streaming

---

## Notes

- Each issue should be created in GitHub with appropriate labels and milestones
- Assign to team members based on expertise and availability
- Link related issues using "Related to #X" or "Blocks #X"
- Update this document as issues are created with issue numbers
