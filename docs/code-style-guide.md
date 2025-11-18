# Code Style Guide

This document outlines the coding standards for the MODX CLI project.

## Overview

MODX CLI follows the PSR-12 coding standard with some additional rules for code quality and consistency.

## PHP_CodeSniffer Configuration

The project uses PHP_CodeSniffer to enforce coding standards. The configuration is defined in `phpcs.xml.dist`.

### Running Code Style Checks

```bash
# Check code style
composer cs:check

# Automatically fix code style issues
composer cs:fix

# Or use phpcs directly
./vendor/bin/phpcs
./vendor/bin/phpcbf
```

## Coding Standards

### PSR-12 Base Standard

We follow [PSR-12: Extended Coding Style](https://www.php-fig.org/psr/psr-12/) as our base standard, which includes:

- Use 4 spaces for indentation (not tabs)
- Opening braces for classes and methods must be on the next line
- Visibility must be declared on all properties and methods
- Control structure keywords must have one space after them
- Opening braces for control structures must be on the same line

### Additional Rules

#### Array Syntax

Use short array syntax:

```php
// Good
$array = ['foo', 'bar'];

// Bad
$array = array('foo', 'bar');
```

#### Line Length

- **Soft limit**: 120 characters (warning)
- **Hard limit**: 150 characters (error)

Lines longer than 120 characters should be split for readability.

#### Deprecated Functions

Do not use deprecated PHP functions. The code sniffer will flag these.

#### TODO/FIXME Comments

TODO and FIXME comments are flagged by the code sniffer to ensure they're tracked. When adding them:

- Keep them brief and actionable
- Reference a GitHub issue if applicable
- Include your initials and date if relevant

```php
// Good
// TODO: Refactor to use dependency injection (#123)

// Bad
// TODO: fix this later
```

#### Forbidden Constructs

The following are discouraged or forbidden:

- **Backtick operator**: Use `shell_exec()` instead
- **goto**: Avoid using goto statements
- **Long array syntax**: Use `[]` instead of `array()`

### Type Declarations

While not currently enforced, we're moving toward requiring:

- Return type declarations on all methods
- Parameter type hints where applicable
- Strict types declaration (`declare(strict_types=1);`)

Example:

```php
<?php

declare(strict_types=1);

namespace MODX\CLI\Command;

class Example extends BaseCmd
{
    public function process(): int
    {
        return 0;
    }

    protected function getMessage(string $key): ?string
    {
        return $this->messages[$key] ?? null;
    }
}
```

### Namespaces and Use Statements

- Always declare a namespace for classes
- Group and order use statements logically
- Remove unused use statements
- One use statement per line

```php
<?php

namespace MODX\CLI\Command\Resource;

use MODX\CLI\Command\ProcessorCmd;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
```

### DocBlocks

All classes and public methods should have DocBlocks:

```php
/**
 * Get a MODX resource by ID
 *
 * @param int $id The resource ID
 * @return \MODX\Revolution\modResource|null The resource or null if not found
 */
public function getResource(int $id): ?\MODX\Revolution\modResource
{
    return $this->modx->getObject('modResource', $id);
}
```

For test methods, DocBlocks are optional but recommended for complex tests.

### Whitespace

- No trailing whitespace at the end of lines
- Files must end with a single newline
- No multiple blank lines in a row

## File Organization

### Directory Structure

```
src/
├── API/              # Internal API classes
├── Alias/            # SSH alias handling
├── Command/          # Command classes
│   ├── BaseCmd.php
│   ├── ProcessorCmd.php
│   ├── ListProcessor.php
│   └── [Entity]/     # Entity-specific commands
├── Configuration/    # Configuration management
├── Formatter/        # Output formatters
└── SSH/              # SSH functionality
```

### File Naming

- Class files: PascalCase matching the class name
- One class per file
- Filename must match class name

## Testing Standards

### Test File Organization

```
tests/
├── Unit/             # Unit tests
├── Integration/      # Integration tests
└── Fixtures/         # Test fixtures and data
```

### Test Naming

Test methods should be descriptive:

```php
public function testResourceUpdateWithPartialData()
{
    // ...
}
```

### Relaxed Standards for Tests

Test files have relaxed rules for:
- Line length (can be longer for readability)
- Function comment requirements

## IDE Configuration

### PhpStorm / IntelliJ IDEA

1. Go to Settings → Editor → Code Style → PHP
2. Set to PSR-12
3. Enable "Import code style from phpcs.xml"

### VS Code

Install the following extensions:
- `phpcs` by Ioannis Kappas
- `phpcbf` by Per Soderlind

Add to `.vscode/settings.json`:

```json
{
    "phpcs.enable": true,
    "phpcs.standard": "phpcs.xml.dist",
    "phpcbf.enable": true,
    "phpcbf.standard": "phpcs.xml.dist"
}
```

## Continuous Integration

PHP_CodeSniffer checks should be run in CI/CD pipelines before merging:

```bash
# In your CI pipeline
composer cs:check
```

## Resources

- [PSR-12: Extended Coding Style](https://www.php-fig.org/psr/psr-12/)
- [PHP_CodeSniffer Documentation](https://github.com/squizlabs/PHP_CodeSniffer/wiki)
- [PHP The Right Way](https://phptherightway.com/)
