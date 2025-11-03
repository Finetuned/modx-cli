# End-to-End Integration Tests

This directory contains end-to-end integration tests that validate complete workflows and multi-step scenarios in MODX CLI. These tests combine multiple commands and operations to ensure complex user workflows function correctly.

## Test Files

### 1. PackageUpgradeWorkflowTest.php (8 tests)
Tests package management workflows:
- **testPackageListExecutesSuccessfully** - Verify package:list command execution
- **testPackageListReturnsValidJson** - Test JSON output format
- **testPackageSearchExecutes** - Test package search functionality
- **testPackageWorkflowSimulation** - Simulate complete package workflow
- **testPackageInvalidOperationHandling** - Error handling for invalid operations
- **testPackageListWithFilters** - Test filtering with limit parameter
- **testMultiStepPackageDiscovery** - Multi-step list → search workflow
- **testPackageWorkflowAutomationFormat** - JSON format for automation

### 2. MultiStepScenarioTest.php (8 tests)
Tests complex multi-step workflows combining multiple commands:

#### Content Creation & Organization
- **testContentCreationWorkflow** - Create category, chunk, and snippet in hierarchy
- **testContentRelationshipWorkflow** - Create template and TV, test relationships
- **testUpdateCascadeWorkflow** - Update parent category, verify children maintain relationships

#### Batch Operations & Data Integrity
- **testBatchCreationAndCategoryRelationship** - Test MODX's category deletion behavior
  - Creates multiple categories with elements
  - Deletes a category
  - **Validates MODX behavior: Elements become uncategorized (category=0), NOT cascade deleted**
  - Verifies elements persist after category deletion

#### Workflow Integration
- **testConfigurationChangeWorkflow** - Full config CRUD workflow (add → verify → remove)
- **testMixedCommandIntegration** - Standard + custom command interoperability
- **testErrorRecoveryWorkflow** - Duplicate creation prevention and error recovery
- **testDataConsistencyChain** - Data persistence across create → get → update → verify

## Total Test Coverage
**16 end-to-end tests** validating complete workflows and multi-step scenarios.

## Test Architecture

All end-to-end tests extend `BaseIntegrationTest`, providing:
- Real CLI command execution via Symfony Process
- Database query helpers for state verification
- JSON output parsing
- Environment-aware test skipping
- Automatic cleanup in tearDown()

## Running the Tests

### Run All End-to-End Tests
```bash
MODX_INTEGRATION_TESTS=1 vendor/bin/phpunit tests/Integration/EndToEnd/
```

### Run Individual Test Files
```bash
# Package workflow tests
MODX_INTEGRATION_TESTS=1 vendor/bin/phpunit tests/Integration/EndToEnd/PackageUpgradeWorkflowTest.php

# Multi-step scenario tests
MODX_INTEGRATION_TESTS=1 vendor/bin/phpunit tests/Integration/EndToEnd/MultiStepScenarioTest.php
```

### Run with Verbose Output
```bash
MODX_INTEGRATION_TESTS=1 vendor/bin/phpunit tests/Integration/EndToEnd/ --verbose
```

### Run Specific Test Methods
```bash
# Run single scenario test
MODX_INTEGRATION_TESTS=1 vendor/bin/phpunit tests/Integration/EndToEnd/MultiStepScenarioTest.php --filter testContentCreationWorkflow
```

## Environment Configuration

Tests require the following environment variables (configured in `tests/Integration/.env`):

```bash
MODX_INTEGRATION_TESTS=1
MODX_TEST_INSTANCE_PATH="/path/to/modx/test/instance"
MODX_TEST_DB_HOST="mysql"
MODX_TEST_DB_NAME="cli-test"
MODX_TEST_DB_USER="root"
MODX_TEST_DB_PASS="password"
```

## Multi-Step Scenario Test Details

### Workflow Coverage

**Content Management Workflows:**
- Category hierarchy creation and organization
- Element (chunks, snippets) creation within categories
- Template and TV creation and relationships
- Hierarchical category updates
- Parent-child relationship maintenance

**Batch Operations:**
- Multiple category creation
- Element assignment to categories
- Category deletion behavior validation
- Orphaned element handling

**Configuration Management:**
- Config entry creation
- Config value updates
- Config retrieval and verification
- Config deletion

**Integration Testing:**
- Standard command execution
- Custom command execution
- Mixed command workflows
- Command interoperability validation

**Error Handling:**
- Duplicate element prevention
- Error recovery procedures
- Validation enforcement
- Data integrity protection

**Data Consistency:**
- Create-read-update-verify cycles
- Database state verification
- Command output consistency
- State persistence validation

### Important MODX Behavior Notes

**Category Deletion (testBatchCreationAndCategoryRelationship):**
In MODX, deleting a category does **NOT** cascade delete its elements. This test validates the correct behavior:
- Elements in deleted category become **uncategorized** (category = 0/null)
- Elements **persist** in the database
- Only the category itself is removed

This is actual MODX behavior and the test ensures CLI commands honor this design.

### Helper Methods

Multi-step tests include comprehensive helper methods:
- `findCategoryId()` - Locate category by name
- `findCategoryById()` - Locate category by ID
- `findChunkByName()` - Locate chunk by name
- `findTemplateId()` - Locate template by name
- `findTVId()` - Locate TV by name
- `assertChunkExists()` - Verify chunk with category
- `assertSnippetExists()` - Verify snippet with category
- `assertConfigExists()` - Verify config entry
- `assertChunkByNameExists()` - Verify chunk exists (any category)

## Test Execution Behavior

### When MODX Instance Not Configured
- Tests skip with message: "Integration tests are disabled"
- No false failures occur
- Test framework validates correctly

### When MODX Instance Configured
- Tests execute full multi-step workflows
- Database state verified at each step
- Command output validated
- Error scenarios tested
- Data consistency verified across operations

## Test Strategy

### Package Workflow Tests
1. **Read-Only Operations** - Safe package discovery operations
2. **JSON Format** - Automation-friendly output
3. **Search Functionality** - Package filtering and discovery
4. **Error Handling** - Invalid operation handling
5. **Multi-Step Flows** - List → Search → Info workflows

### Multi-Step Scenario Tests
1. **Content Creation** - Build hierarchical content structures
2. **Relationship Testing** - Verify element relationships
3. **Update Workflows** - Test cascading updates
4. **Batch Operations** - Multiple operations in sequence
5. **Mixed Commands** - Standard + custom command mixing
6. **Error Recovery** - Graceful error handling
7. **Data Integrity** - State consistency validation

## Benefits of End-to-End Tests

**Real-World Validation:**
- Tests actual user workflows
- Validates command interdependencies
- Ensures state consistency
- Catches integration issues

**Workflow Coverage:**
- Multi-step operations
- Complex scenarios
- Error conditions
- Edge cases

**Production Confidence:**
- Real CLI execution
- Actual database changes
- True MODX processor integration
- Environment-realistic testing

## Adding New End-to-End Tests

When adding new workflow tests:

1. **Extend BaseIntegrationTest**
```php
class MyWorkflowTest extends BaseIntegrationTest
{
    public function testMyWorkflow()
    {
        // Test implementation
    }
}
```

2. **Use Unique Identifiers**
```php
$name = 'IntegrationTest_' . uniqid();
```

3. **Verify Each Step**
```php
// Step 1: Create
$this->executeCommandSuccessfully(['category:create', $name]);

// Step 2: Verify
$categories = $this->executeCommandJson(['category:list']);
$this->assertCategoryExists($categories, $name);
```

4. **Clean Up in tearDown()**
```php
protected function tearDown(): void
{
    $this->queryDatabase('DELETE FROM modx_categories WHERE category LIKE ?', ['IntegrationTest_%']);
    parent::tearDown();
}
```

## Notes

- All tests use real CLI command execution via Symfony Process
- Tests verify actual database state changes
- No mocking - tests run against real MODX instance
- Automatic cleanup prevents test pollution
- Tests are safe for parallel execution (unique identifiers)
- Package tests are read-only (no installation occurs)

## Future Enhancements

Possible extensions to the end-to-end test suite:

1. **Resource Workflows** - Complete resource creation and management
2. **User Management Workflows** - User creation, role assignment, permissions
3. **Plugin Workflows** - Plugin installation and configuration
4. **Context Workflows** - Multi-context operations
5. **Performance Scenarios** - Large dataset operations
6. **Rollback Scenarios** - Error recovery and state restoration
7. **Migration Workflows** - Content import/export scenarios
8. **Remote Execution** - SSH/alias workflow integration
