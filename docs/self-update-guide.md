# Self-Update Guide

MODX CLI supports self-updating when installed as a Phar. Composer installs should be updated through Composer.

## Requirements
- Self-update works only when running a Phar install.
- Composer installs should be updated via Packagist: `finetuned/modx-cli`.

## Check for Updates
```bash
modx self-update --check
```

## Update to Latest Stable
```bash
modx self-update
```

## Update to Latest Pre-Release (Nightly)
```bash
modx self-update --nightly
```

## Update to a Specific Version
```bash
modx self-update --target-version=0.6.1
```
Alias:
```bash
modx self-update --to=0.6.1
```

## Dry Run (Preview Only)
```bash
modx self-update --dry-run
```

## JSON Output
```bash
modx self-update --check --json
```

## Notes on Version Flags
- `--version` and `-v` are global CLI options and are not valid for `self-update`.

## Composer Updates
If you installed MODX CLI via Composer, use:
```bash
composer global update finetuned/modx-cli
```

## Troubleshooting
- **Permissions**: Ensure the Phar file and its directory are writable.
- **Rate limits**: Set `GITHUB_TOKEN` to increase GitHub API limits.
- **Checksum**: If provided, the update verifies the checksum; failures indicate a corrupted download.
