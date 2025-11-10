# Running Tests in MODX-CLI

## Test Suite Overview

The project has three test suites configured in `phpunit.xml.dist`:

1. **default** - Unit tests only (695 tests)
   - Excludes integration tests
   - Fast execution
   - No external dependencies required

2. **integration** - Integration tests only (159 tests)
   - Requires MODX installation or mocked environment
   - Most tests are skipped unless `MODX_INTEGRATION_TESTS=1` is set
   
3. **all** - Complete test suite (854 tests)
   - Includes both unit and integration tests
   - Recommended for comprehensive testing

## Running Tests

### Run Unit Tests Only (Default)
```bash
./vendor/bin/phpunit
# or explicitly:
./vendor/bin/phpunit --testsuite default
```

### Run Integration Tests Only
```bash
./vendor/bin/phpunit --testsuite integration
```

### Run All Tests (Unit + Integration)
```bash
./vendor/bin/phpunit --testsuite all
```

## Running Tests with Coverage

### Generate Coverage for All Tests
```bash
XDEBUG_MODE=coverage ./vendor/bin/phpunit --testsuite all --coverage-html=var/coverage/html
```

### Generate Coverage for Unit Tests Only
```bash
XDEBUG_MODE=coverage ./vendor/bin/phpunit --testsuite default --coverage-html=var/coverage/html
```

### Quick Coverage Summary (Text Only)
```bash
XDEBUG_MODE=coverage ./vendor/bin/phpunit --testsuite all --coverage-text
```

### Generate Coverage Reports (HTML + Text + Clover)
```bash
XDEBUG_MODE=coverage ./vendor/bin/phpunit --testsuite all
```
This generates:
- HTML report: `var/coverage/html/index.html`
- Text report: `var/coverage/coverage.txt`
- Clover XML: `var/coverage/clover.xml`

## Test Count Reference

- **Unit Tests**: 695 tests
- **Integration Tests**: 159 tests (137 skipped without MODX_INTEGRATION_TESTS=1)
- **Total Tests**: 854 tests

## Troubleshooting

### Issue: Not all tests are running
**Solution**: Make sure you're using `--testsuite all` to run both unit and integration tests.

### Issue: Integration tests are skipped
**Reason**: Integration tests require a live MODX environment or the `MODX_INTEGRATION_TESTS=1` flag.
**Solution**: Set the environment variable:
```bash
MODX_INTEGRATION_TESTS=1 ./vendor/bin/phpunit --testsuite integration
```

### Issue: Coverage takes too long
**Solution**: Run coverage on unit tests only:
```bash
XDEBUG_MODE=coverage ./vendor/bin/phpunit --testsuite default
```

## View Coverage Reports

### Open HTML Coverage Report
```bash
open var/coverage/html/index.html
# or on Linux:
xdg-open var/coverage/html/index.html
```

### View Text Coverage Summary
```bash
cat var/coverage/coverage.txt
```
