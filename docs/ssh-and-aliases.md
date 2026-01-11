# SSH and Aliases in MODX CLI

MODX CLI now supports running commands on remote servers via SSH and using aliases to simplify working with multiple MODX installations.

## SSH Mode

The SSH mode allows you to run MODX CLI commands on a remote server without having to SSH into the server manually. This is useful for automating tasks across multiple servers.

### Basic Usage

To run a command on a remote server, use the `--ssh` option:

```bash
modx --ssh=user@example.com:/path/to/modx system:info
```

This will execute the `system:info` command on the remote server at `example.com` using the specified user and path.

### Connection String Format

The SSH connection string follows this format:

```
[<user>@]<host>[:<port>][<path>]
```

Where:
- `user` is the SSH username (optional, defaults to current user)
- `host` is the hostname or IP address
- `port` is the SSH port (optional, defaults to 22)
- `path` is the path to the MODX installation on the remote server (optional)

Examples:
- `--ssh=example.com` - Connect to example.com as the current user
- `--ssh=user@example.com` - Connect to example.com as the specified user
- `--ssh=user@example.com:2222` - Connect to example.com on port 2222
- `--ssh=user@example.com:/var/www/html` - Connect to example.com and change to the specified directory

### SSH Config Aliases

You can also use SSH aliases defined in your `~/.ssh/config` file:

```bash
modx --ssh=myserver system:info
```

This will use the connection details for the `myserver` alias from your SSH config.

## Aliases

Aliases provide a way to define shortcuts for commonly used MODX installations. This is especially useful when working with multiple environments (development, staging, production).

### Configuration

Aliases are defined in YAML configuration files:

- Global aliases: `~/.modx/config.yml`
- Project-specific aliases: `modx-cli.yml` in the project directory

### Defining Aliases

Aliases are defined with an `@` prefix:

```yaml
@prod:
  ssh: user@production-server.com:/path/to/modx
@staging:
  ssh: user@staging-server.com:/path/to/modx
@dev:
  ssh: user@dev-server.com:/path/to/modx
```

You can also define local aliases that point to a specific MODX base path:

```yaml
@local:
  base_path: /var/www/modx
```

### Using Aliases

To use an alias, simply prefix the command with the alias name:

```bash
modx @prod system:info
```

This will run the `system:info` command on the production server.

Local aliases work the same way:
```bash
modx @local resource:list
```

### Alias Groups

You can also define alias groups to run commands on multiple servers:

```yaml
@all:
  - @prod
  - @staging
  - @dev
```

Then you can run a command on all servers:

```bash
modx @all system:clear-cache
```

This will run the `system:clear-cache` command on all servers defined in the `@all` group.

## Example Configuration

Here's an example of a complete configuration file:

```yaml
# Global aliases
@prod:
  ssh: user@production-server.com:/var/www/html
@staging:
  ssh: user@staging-server.com:/var/www/html
@dev:
  ssh: user@dev-server.com:/var/www/html

# Groups
@all:
  - @prod
  - @staging
  - @dev

@testing:
  - @staging
  - @dev
```

## Requirements

- SSH access to the remote server
- MODX CLI installed on the remote server
- The remote MODX CLI should be accessible as `modx` in the PATH
