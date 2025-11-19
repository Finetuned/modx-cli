# Type Declarations Progress

This document tracks the progress of adding PHP type declarations throughout the codebase.

## Goals

1. Add return type declarations to all methods
2. Add parameter type hints where applicable
3. Improve code quality and IDE support
4. Enable better static analysis

## Completed Files

### Core Application (100% Complete)

#### src/Application.php
**Status**: ✅ Complete
**Lines Modified**: 573 lines
**Methods Updated**: 19 methods

All methods now have proper type declarations:
- `getDefaultInputDefinition(): InputDefinition`
- `getDefaultCommands(): array`
- `getMODX(): ?\MODX\Revolution\modX`
- `getCwd()`: Returns `string|false` (matching `getcwd()`)
- `loadMODX($config)`: Returns `\MODX\Revolution\modX|false`
- `initialize(\MODX\Revolution\modX $modx): \MODX\Revolution\modX`
- `getService(string $name = '', array $params = array()): ?object`
- `getExcludedCommands(): array`
- `doRun(InputInterface $input, OutputInterface $output): int`
- `runWithAlias(string $alias, InputInterface $input, OutputInterface $output): int`
- `runWithAliasGroup(array $group, InputInterface $input, OutputInterface $output): int`
- `runInSSHMode(InputInterface $input, OutputInterface $output): int`
- `runWithSSH(string $sshString, InputInterface $input, OutputInterface $output): int`
- All protected/private helper methods

**Benefits**:
- Clear return types for all public APIs
- Better IDE autocompletion
- Safer refactoring
- PHPStan-compatible

---

## In Progress

### Command Base Classes (0% Complete)

#### src/Command/BaseCmd.php
**Status**: 🔄 Pending
**Priority**: High
**Estimated Effort**: 2 hours

Methods to update (~25 methods):
- `getApplication(): \MODX\CLI\Application`
- `run(InputInterface $input, OutputInterface $output): int`
- `init(): bool`
- `execute(InputInterface $input, OutputInterface $output): int`
- `process(): int`
- `call(string $command, array $arguments = array()): int`
- `callSilent(string $command, array $arguments = array()): int`
- `confirm(string $question, bool $default = true): bool`
- `ask(string $question, ?string $default = null): string`
- `secret(string $question, bool $fallback = true): string`
- `line(string $string): void`
- `info(string $string): void`
- `comment(string $string): void`
- `question(string $string): void`
- `error(string $string): void`
- `getArguments(): array`
- `getOptions(): array`
- `isSSHMode(): bool`
- `getOutput(): OutputInterface`
- `getMODX(): ?\MODX\Revolution\modX`
- `getRunStats(): string`
- `convertBytes(int $bytes): string`
- `isEnabled(): bool`

#### src/Command/ProcessorCmd.php
**Status**: 🔄 Pending
**Priority**: High
**Estimated Effort**: 2 hours

Methods to update (~15 methods):
- `process(): int`
- `processResponse(array $response = array()): int`
- `beforeRun(array &$properties = array(), array &$options = array()): mixed`
- `getExistingObject(string $class, int $id): ?\xPDO\Om\xPDOObject`
- `prePopulateFromExisting(array &$properties, string $class, int $id, array $fieldMap = array()): bool`
- `applyDefaults(array &$properties, array $defaults = array()): void`
- `addOptionsToProperties(array &$properties, array $optionKeys, array $typeMap = array()): void`
- `decodeResponse(\MODX\Revolution\Processors\ProcessorResponse &$response): array`
- `processArray(string $key, string $type = 'option'): array`
- `getOptions(): array`
- `handleColumns(): void`
- `processRow(array $record = array()): array`
- `parseValue(mixed $value, string $column): mixed`
- `renderBoolean($value): string` (also needs param type)
- `renderObject(string $class, $pk, string $column): mixed`

#### src/Command/ListProcessor.php
**Status**: 🔄 Pending
**Priority**: Medium
**Estimated Effort**: 1 hour

---

## Pending Files

### Configuration Classes (0% Complete)

- `src/Configuration/Base.php` - 10 methods
- `src/Configuration/Instance.php` - 8 methods
- `src/Configuration/Extension.php` - 6 methods
- `src/Configuration/Component.php` - 6 methods
- `src/Configuration/ExcludedCommands.php` - 5 methods

**Estimated Effort**: 4 hours total

### API Classes (0% Complete)

- `src/API/MODX_CLI.php` - Already has some type hints, needs completion
- `src/API/CommandRegistry.php` - 6 methods
- `src/API/HookRegistry.php` - 5 methods
- `src/API/CommandRunner.php` - 3 methods
- `src/API/CommandPublisher.php` - 4 methods
- `src/API/ClosureCommand.php` - 5 methods
- `src/API/HookableCommand.php` - 4 methods

**Estimated Effort**: 5 hours total

### Command Classes (0% Complete)

126 command files need type declarations. Most extend ProcessorCmd or ListProcessor, so once base classes are done, commands will be easier.

**Priority**: Low (do after base classes)
**Estimated Effort**: 20-30 hours (can be done incrementally)

### Helper Classes (0% Complete)

- `src/SSH/Handler.php` - 8 methods
- `src/Alias/Resolver.php` - 6 methods
- `src/Formatter/*.php` - Various formatters
- `src/Configuration/Yaml/YamlConfig.php` - 5 methods

**Estimated Effort**: 6 hours total

---

## Implementation Strategy

### Phase 1: Core Classes (Week 1) ✅ IN PROGRESS
- [x] src/Application.php
- [ ] src/Command/BaseCmd.php
- [ ] src/Command/ProcessorCmd.php
- [ ] src/Command/ListProcessor.php

### Phase 2: Configuration & API (Week 2)
- [ ] All Configuration classes
- [ ] All API classes
- [ ] SSH and Alias classes

### Phase 3: Command Classes (Weeks 3-4)
- [ ] Update commands in batches:
  - Resource commands
  - Template/Chunk/Snippet commands
  - User/Package commands
  - System commands
  - Config commands

### Phase 4: Cleanup (Week 5)
- [ ] Fix any PHPStan issues
- [ ] Update all tests
- [ ] Final review

---

## Testing Strategy

After each phase:
1. Run PHPStan: `composer analyse`
2. Run unit tests: `composer test:unit`
3. Run integration tests: `composer test:integration`
4. Fix any issues before proceeding

---

## Common Patterns

### Return Types

```php
// Void methods
public function configure(): void

// Boolean methods
protected function init(): bool
public function isEnabled(): bool

// String methods
protected function getCommandName(): string
public function getHelp(): string

// Array methods
protected function getOptions(): array
public function getArguments(): array

// Integer methods (exit codes)
public function execute(InputInterface $input, OutputInterface $output): int
protected function process(): int

// Nullable returns
public function getMODX(): ?\MODX\Revolution\modX
public function getObject(string $class, int $id): ?\xPDO\Om\xPDOObject

// Mixed (only when truly necessary)
public function getOption(string $key): mixed
```

### Parameter Types

```php
// Primitives
public function setName(string $name): void
public function setEnabled(bool $enabled): void
public function setLimit(int $limit): void

// Nullable primitives
public function ask(string $question, ?string $default = null): string

// Arrays
public function setProperties(array $properties): void
public function mergeOptions(array $options = array()): void

// Objects
public function setModx(\MODX\Revolution\modX $modx): void
public function run(InputInterface $input, OutputInterface $output): int

// Unions (PHP 8.0+) - not yet, we're on 7.4
// For now use mixed or PHPDoc
```

---

## Benefits Achieved So Far

### Application.php
- ✅ All 19 methods have return types
- ✅ All parameters have type hints
- ✅ Clearer API contracts
- ✅ Better IDE support
- ✅ PHPStan level 5 compatible

---

## Next Steps

1. **Immediate**: Complete BaseCmd.php type declarations
2. **This Week**: Complete ProcessorCmd.php and ListProcessor.php
3. **Next Week**: Configuration and API classes
4. **Ongoing**: Command classes in batches

---

## PHPStan Integration

PHPStan is now configured (`phpstan.neon.dist`) and can be run with:

```bash
composer analyse
```

Current level: 5 (will increase to 6+ as type declarations are added)

Expected PHPStan errors to fix:
- Missing return types (decreasing as we add them)
- Parameter type mismatches
- Potential null pointer issues
- Undefined property access

---

## Notes

- Type declarations are backwards compatible with PHP 7.4
- Some methods return `mixed` which is not available in PHP 7.4, use PHPDoc instead
- Union types (PHP 8.0) not used yet, will be added during PHP 8 migration
- Nullable types (?) are available and used where appropriate
- `void` return type is used for methods that don't return values

---

## Progress Tracking

**Overall Progress**: 0.8% (1/126 files complete)
**Core Classes Progress**: 25% (1/4 files complete)
**Estimated Completion**: 4-5 weeks

Last Updated: 2025-11-18
