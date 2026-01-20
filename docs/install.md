# MODX 3.x Installation Guide

This guide covers installing MODX 3.x using the `modx install` command.

## Table of Contents

- [Quick Start](#quick-start)
- [Installation Methods](#installation-methods)
- [Command Reference](#command-reference)
- [MODX 3.x Requirements](#modx-3x-requirements)
- [Configuration Files](#configuration-files)
- [Gitify Integration](#gitify-integration)
- [Troubleshooting](#troubleshooting)

## Quick Start

### Basic Installation

Install the latest MODX 3.x in the current directory:

```bash
modx install
```

Install a specific MODX 3.x version:

```bash
modx install --modx-version=3.0.5
```

### Installation with Automated Setup

Install and run automated setup using an XML configuration file:

```bash
modx install /var/www/mysite --setup --setup-config=examples/install-config.xml
```

### Installation to Specific Directory

```bash
modx install /path/to/project --modx-version=latest
```

## Installation Methods

### Composer Installation (Default)

The default installation method uses `composer create-project modx/revolution` to install MODX 3.x.

**Basic Usage:**

```bash
modx install [target-directory] [options]
```

**Options:**

- `--modx-version`: Specify MODX version (e.g., `3.0.5`, `latest`)
- `--composer-bin`: Custom composer executable (default: `composer`)
- `--composer-no-interaction`: Disable interactive prompts

**Examples:**

```bash
# Install latest MODX 3.x
modx install /var/www/mysite

# Install specific version
modx install /var/www/mysite --modx-version=3.0.5

# Use custom composer binary
modx install /var/www/mysite --composer-bin=/usr/local/bin/composer2

# Non-interactive installation
modx install /var/www/mysite --composer-no-interaction
```

### Custom Installer Integration

You can delegate installation to custom installers like Gitify or other tools.

**Usage:**

```bash
modx install [target] [config] \
  --installer=<name> \
  --installer-command="<command with {target} and {config} placeholders>"
```

**Placeholders:**

- `{target}`: Replaced with the target directory path
- `{config}`: Replaced with the config file path

**Example:**

```bash
modx install /var/www/mysite config.xml \
  --installer=gitify \
  --installer-command="gitify modx:install {target} --config={config}"
```

## Command Reference

### Arguments

- **target** (optional): Path to install MODX into (defaults to current directory)
- **config** (optional): Path to configuration file for custom installers or setup

### Options

#### Installation Options

- `--installer=INSTALLER`: Installer to use (`composer` or custom name) [default: `composer`]
- `--installer-command=COMMAND`: Custom installer command line (use `{target}` and `{config}` placeholders)
- `--modx-version=VERSION`: MODX version for composer (e.g., `3.0.5`, `latest`)
- `--composer-bin=PATH`: Composer executable or command [default: `composer`]
- `--composer-no-interaction`: Disable interactive prompts for composer

#### Setup Options

- `--setup`: Run MODX setup after installation
- `--setup-command=COMMAND`: Custom setup command (use `{target}` and `{config}` placeholders)
- `--setup-config=PATH`: Path to setup config file (defaults to install config argument)

#### Global Options

- `--json`: Output results in JSON format
- `--ssh=SSH`: Run command on remote server via SSH
- `--log-level=LEVEL`: Set log level (debug, info, warning, error, etc.)
- `--log-file=FILE`: Write logs to specified file

### Version Constraint Formats

The `--modx-version` option accepts several formats:

- **Latest**: `latest` or omit the option entirely
- **Specific version**: `3.0.5` (automatically normalized to `v3.0.5-pl`)
- **Full version**: `v3.0.5-pl`
- **Development version**: `3.1.0-dev`, `dev-master`

**Examples:**

```bash
# Install latest stable
modx install --modx-version=latest

# Install specific version
modx install --modx-version=3.0.5

# Install development version
modx install --modx-version=dev-master
```

## MODX 3.x Requirements

### System Requirements

- **PHP**: 8.0 or higher
- **Database**: 
  - MySQL 5.7.8+ 
  - MariaDB 10.2.2+
- **Web Server**: 
  - Apache 2.4+ with mod_rewrite
  - Nginx 1.18+

### Required PHP Extensions

- pdo
- pdo_mysql
- gd
- json
- zip
- simplexml
- curl
- mbstring
- fileinfo
- openssl

### Recommended PHP Settings

```ini
memory_limit = 256M
max_execution_time = 300
upload_max_filesize = 20M
post_max_size = 20M
```

### File Permissions

After installation, ensure these directories are writable:

- `core/cache/`
- `core/components/`
- `core/packages/`
- `core/export/`
- `assets/components/`

## Configuration Files

### XML Configuration Format

The setup configuration file uses XML format with the following structure:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<modx>
    <!-- Database Configuration -->
    <database_type>mysql</database_type>
    <database_server>localhost</database_server>
    <database>modx3_db</database>
    <database_user>modx_user</database_user>
    <database_password>secure_password</database_password>
    
    <!-- Database Character Sets -->
    <database_connection_charset>utf8mb4</database_connection_charset>
    <database_charset>utf8mb4</database_charset>
    <database_collation>utf8mb4_unicode_ci</database_collation>
    <table_prefix>modx_</table_prefix>
    
    <!-- Host Configuration -->
    <http_host>example.com</http_host>
    <https_port>443</https_port>
    
    <!-- MODX Settings -->
    <language>en</language>
    <cache_disabled>0</cache_disabled>
    
    <!-- Administrator Account -->
    <cmsadmin>admin</cmsadmin>
    <cmspassword>SecurePassword123!</cmspassword>
    <cmsadminemail>admin@example.com</cmsadminemail>
    
    <!-- Path Configuration -->
    <core_path>core/</core_path>
    <context_mgr_path>manager/</context_mgr_path>
    <context_mgr_url>/manager/</context_mgr_url>
    <context_connectors_path>connectors/</context_connectors_path>
    <context_connectors_url>/connectors/</context_connectors_url>
    <context_web_path>/</context_web_path>
    <context_web_url>/</context_web_url>
    
    <!-- Cleanup -->
    <remove_setup_directory>true</remove_setup_directory>
</modx>
```

### Configuration Fields

#### Database Fields

- `database_type`: Database type (always `mysql` for MODX 3.x)
- `database_server`: Database host (e.g., `localhost`, `127.0.0.1`, RDS endpoint)
- `database`: Database name
- `database_user`: Database username
- `database_password`: Database password
- `database_connection_charset`: Connection charset (recommended: `utf8mb4`)
- `database_charset`: Database charset (recommended: `utf8mb4`)
- `database_collation`: Database collation (recommended: `utf8mb4_unicode_ci`)
- `table_prefix`: Table prefix (default: `modx_`)

#### Host Fields

- `http_host`: Website hostname (without protocol)
- `https_port`: HTTPS port (default: `443`)

#### Path Fields

- `core_path`: Core directory path (default: `core/`)
- `context_mgr_path`: Manager directory path (default: `manager/`)
- `context_mgr_url`: Manager URL path (default: `/manager/`)
- `context_connectors_path`: Connectors directory path
- `context_connectors_url`: Connectors URL path
- `context_web_path`: Web root path
- `context_web_url`: Web root URL (default: `/`)

#### Security Considerations

**Custom Core Path:**

For enhanced security, you can move the core directory outside the web root:

```xml
<core_path>/var/modx-core/mysite-core/</core_path>
```

**Renamed Manager Directory:**

```xml
<context_mgr_path>admin/</context_mgr_path>
<context_mgr_url>/admin/</context_mgr_url>
```

### Example Configuration File

See `examples/install-config.xml` for a complete, production-ready example with detailed comments.

## Gitify Integration

### What is Gitify?

Gitify is a tool for version-controlling MODX data. It can also handle MODX installation and setup.

### Using Gitify as Installer

**Installation:**

```bash
composer global require modmore/gitify:^2
```

**Using with MODX CLI:**

```bash
modx install /var/www/mysite config.xml \
  --installer=gitify \
  --installer-command="gitify modx:install {target} --config={config}"
```

### Gitify Workflow

1. **Install MODX via Gitify:**

   ```bash
   cd /var/www/mysite
   gitify modx:install
   ```

2. **Initialize Gitify:**

   ```bash
   gitify init
   ```

3. **Extract MODX data to files:**

   ```bash
   gitify extract
   ```

4. **Build MODX from files:**

   ```bash
   gitify build
   ```

### Combining MODX CLI and Gitify

You can use MODX CLI for installation and Gitify for content management:

```bash
# Install MODX with composer
modx install /var/www/mysite --setup --setup-config=config.xml

# Initialize Gitify for version control
cd /var/www/mysite
gitify init
gitify extract
```

## Troubleshooting

### Common Issues

#### Composer Not Found

**Error:**
```
Composer installation failed
```

**Solution:**

Specify the full path to composer:

```bash
modx install --composer-bin=/usr/local/bin/composer
```

Or set the `COMPOSER_BIN` environment variable:

```bash
export COMPOSER_BIN=/usr/local/bin/composer
modx install
```

#### Permission Denied

**Error:**
```
Permission denied when creating directory
```

**Solution:**

Ensure the target directory is writable:

```bash
sudo chown -R $USER:$USER /var/www/mysite
modx install /var/www/mysite
```

Or create the directory first:

```bash
mkdir -p /var/www/mysite
modx install /var/www/mysite
```

#### Setup Failed

**Error:**
```
MODX setup failed
```

**Solutions:**

1. **Check database credentials in config file**
2. **Ensure database exists:**

   ```bash
   mysql -u root -p -e "CREATE DATABASE modx3_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   ```

3. **Verify database user permissions:**

   ```sql
   GRANT ALL PRIVILEGES ON modx3_db.* TO 'modx_user'@'localhost';
   FLUSH PRIVILEGES;
   ```

4. **Check PHP requirements:**

   ```bash
   php -v  # Should be 8.0+
   php -m  # Check for required extensions
   ```

#### Version Not Found

**Error:**
```
Could not find package modx/revolution with version 3.0.99
```

**Solution:**

Check available versions:

```bash
composer show modx/revolution --all
```

Use a valid version or `latest`:

```bash
modx install --modx-version=latest
```

#### Custom Installer Command Fails

**Error:**
```
Custom installer requires --installer-command
```

**Solution:**

Always provide `--installer-command` when using a custom installer:

```bash
modx install /path/to/site config.xml \
  --installer=gitify \
  --installer-command="gitify modx:install {target} --config={config}"
```

### Getting Help

#### Enable Debug Output

```bash
modx install -vvv --log-level=debug --log-file=install.log
```

#### Check Logs

After installation, check:

- Installation log file (if `--log-file` specified)
- MODX core error log: `core/cache/logs/error.log`
- Web server error logs

#### JSON Output for Scripting

```bash
modx install --json | jq .
```

### Best Practices

1. **Use configuration files** for automated/repeatable installations
2. **Test installations** in development environments first
3. **Back up** before installing in existing directories
4. **Secure configuration files** - never commit passwords to version control
5. **Verify PHP requirements** before installation
6. **Use strong passwords** for production installations
7. **Remove setup directory** after successful installation (automatic with `remove_setup_directory=true`)
8. **Set proper file permissions** after installation

## Advanced Usage

### Environment-Specific Installations

Create separate config files for each environment:

```bash
# Development
modx install /var/www/dev --setup --setup-config=config.dev.xml

# Staging
modx install /var/www/staging --setup --setup-config=config.staging.xml

# Production
modx install /var/www/production --setup --setup-config=config.prod.xml
```

### Automated CI/CD Pipeline

```bash
#!/bin/bash
set -e

# Install MODX
modx install /var/www/app \
  --modx-version=3.0.5 \
  --setup \
  --setup-config=config.xml \
  --composer-no-interaction \
  --json > install-result.json

# Check installation success
if jq -e '.success == true' install-result.json > /dev/null; then
  echo "MODX installed successfully"
  # Continue with deployment
else
  echo "MODX installation failed"
  jq '.message' install-result.json
  exit 1
fi
```

### Remote Installation via SSH

```bash
modx install /var/www/mysite \
  --setup \
  --setup-config=config.xml \
  --ssh=user@example.com:/var/www/mysite
```

## Related Documentation

- [MODX CLI Command Reference](command-inventory.md)
- [SSH and Aliases](ssh-and-aliases.md)
- [Gitify Documentation](https://docs.modmore.com/en/Open_Source/Gitify/)
- [MODX 3.x Documentation](https://docs.modx.com/3.x/)

## Support

For issues and questions:

- MODX CLI Issues: [GitHub Issues](https://github.com/modxcms/modx-cli/issues)
- MODX Community: [MODX Forums](https://community.modx.com/)
- Gitify Issues: [Gitify GitHub](https://github.com/modmore/Gitify/issues)
