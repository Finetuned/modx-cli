# TODO Priority List

This document tracks TODO/FIXME items found in the codebase, organized by priority.

## Maintenance

Update this list when new TODOs are introduced or removed. Convert P0–P2 items into GitHub issues as part of release planning.

## Priority Levels

- **P0 (Critical)**: Blocks functionality or causes bugs
- **P1 (High)**: Important for code quality or user experience
- **P2 (Medium)**: Nice to have, improves maintainability
- **P3 (Low)**: Future enhancements, not urgent

---

## P1 (High Priority)

### 1. User Authentication Context
**File**: `src/Application.php:354`
**Current**: `// @todo: ability to define a user (or anything else)`

**Description**: Allow commands to run with a specific MODX user context rather than always using 'mgr'.

**Impact**:
- Enables permission-based command execution
- Better security and access control
- Required for multi-user environments

**Recommendation**:
- Add `--user` option to BaseCmd
- Support user ID or username
- Initialize MODX with specified user context

**Estimated Effort**: 1-2 days

---

### 2. Complete Install Command
**File**: `src/Command/Install.php:75, 88`
**Current**: `// @TODO`

**Description**: The install command has incomplete implementation at two points.

**Impact**:
- Install command may not work properly
- Users cannot install MODX via CLI

**Recommendation**:
- Review MODX installation process
- Implement missing installation steps
- Add comprehensive tests

**Estimated Effort**: 3-5 days

---

### 3. GitHub Version Detection
**File**: `src/Command/Download.php:96`
**Current**: `// TODO : find a way to retrieve the latest version number released (github tags ?)`

**Description**: Download command needs to automatically detect the latest MODX version from GitHub.

**Impact**:
- Users must manually specify version numbers
- Harder to stay up-to-date

**Recommendation**:
- Use GitHub API to fetch latest release
- Parse tags to find latest stable version
- Add fallback for API rate limits

**Estimated Effort**: 1 day

**Implementation Approach**:
```php
// Use GitHub Releases API
$url = 'https://api.github.com/repos/modxcms/revolution/releases/latest';
$response = json_decode(file_get_contents($url), true);
$latestVersion = $response['tag_name'];
```

---

## P2 (Medium Priority)

### 4. Advanced Output Formatting
**File**: `src/Command/BaseCmd.php:56`
**Current**: `* @todo allow advanced output (using a method ?)`

**Description**: The help text currently only supports strings, not formatted/dynamic content.

**Impact**:
- Limited help text formatting
- Cannot generate dynamic help content

**Recommendation**:
- Support callable for `$help` property
- Allow returning formatted output
- Enable context-aware help text

**Estimated Effort**: 1 day

**Example**:
```php
protected $help = null;

protected function getHelp(): string
{
    return $this->formatHelp([
        'description' => 'Create a new resource',
        'examples' => [
            'Basic usage' => 'modx resource:create "Page Title"',
            'With parent' => 'modx resource:create "Page Title" --parent=1'
        ]
    ]);
}
```

---

### 5. Table Handling Refactor
**File**: `src/Command/ProcessorCmd.php:356`
**Current**: `// @todo find a cleaner way to handle this ? since all processors do not make use of tables`

**Description**: Table-related code is in ProcessorCmd but not all processors use tables.

**Impact**:
- Unnecessary code in base class
- Confusion about which methods apply to which commands

**Recommendation**:
- Create `TableProcessor` class extending `ListProcessor`
- Move table-specific methods there
- Update commands to extend appropriate base class

**Estimated Effort**: 2-3 days

**Refactor Plan**:
```
BaseCmd
  └── ProcessorCmd
        ├── GetProcessor (single item)
        ├── ListProcessor (basic lists)
        │     └── TableProcessor (table output)
        └── CrudProcessor (create/update/delete)
```

---

## P3 (Low Priority / Future Enhancements)

### 6. Complete Extension Tests
**File**: `tests/Configuration/ExtensionTest.php:72, 77`
**Current**: `// @TODO`

**Description**: Extension configuration tests are incomplete.

**Impact**:
- Missing test coverage for extension functionality
- Potential bugs in extension management

**Recommendation**:
- Add tests for extension add/remove/list operations
- Test invalid extension handling
- Test class existence checking

**Estimated Effort**: 1 day

---

### 7. Complete Instance Tests
**File**: `tests/Configuration/InstanceTest.php:70, 75`
**Current**: `// @TODO`

**Description**: Instance configuration tests are incomplete.

**Impact**:
- Missing test coverage for instance functionality
- Potential bugs in instance management

**Recommendation**:
- Add tests for instance add/remove/list operations
- Test default instance handling
- Test path resolution

**Estimated Effort**: 1 day

---

## Non-Critical Items

### Debug-Related Comments
These are informational and don't require action:

- `debug_wrapper.php:3` - File identifier comment
- `debug_interactive.php:*` - Debug-related comments (informational)
- `docs/debugging-setup.md:*` - Documentation (not code TODOs)
- `custom-commands/package-upgrade-functions.php:572` - Logging for debugging (informational)

### phpunit.xml Configuration
- `phpunit.xml.dist:10` - Configuration for TODO annotation strictness (intentional)

---

## Implementation Roadmap

### Week 1-2
1. ✅ Configure PHP_CodeSniffer
2. GitHub Version Detection (#3)
3. Advanced Output Formatting (#4)

### Week 3-4
4. User Authentication Context (#1)
5. Complete Install Command (#2)

### Week 5-6
6. Table Handling Refactor (#5)
7. Complete Extension Tests (#6)
8. Complete Instance Tests (#7)

---

## Converting TODOs to Issues

Once reviewed and approved, each P0-P2 TODO should become a GitHub issue with:

- Clear description of the problem
- Proposed solution
- Estimated effort
- Acceptance criteria
- Related files/code locations

Template:

```markdown
## Description
[Clear description from this document]

## Current Behavior
[What happens now]

## Desired Behavior
[What should happen]

## Proposed Solution
[Technical approach]

## Files to Modify
- `src/Application.php:354`

## Estimated Effort
1-2 days

## Acceptance Criteria
- [ ] Feature implemented
- [ ] Tests added
- [ ] Documentation updated
```

---

## Notes

- All TODOs should be converted to issues or resolved within the next quarter
- New TODOs should include an issue reference: `// TODO: Fix issue (#123)`
- Consider adding a pre-commit hook to prevent uncommitted TODOs without issue references
