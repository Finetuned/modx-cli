# MODX CLI

A command-line interface for MODX 3, built with Symfony Console.

## Requirements

- PHP 7.4 or higher
- MODX 3.0.0 or higher

## Installation

### Via Composer

```bash
composer global require modx/cli
```

### Manual Installation

1. Clone the repository:

```bash
git clone https://github.com/finetuned/modx-cli.git
cd modx-cli
```

2. Install dependencies:

```bash
composer install
```

3. Make the CLI executable:

```bash
chmod +x bin/modx
```

4. Create a symbolic link to make the CLI available globally:

```bash
sudo ln -s $(pwd)/bin/modx /usr/local/bin/modx
```

## Usage

### Basic Usage

```bash
modx [command] [options]
```

### Available Commands

When a MODX instance is configured and set as default, many commands become available, including:

- `version` - Display the CLI version
- `system:info` - Get general system information
- `system:clearcache` - Clear the MODX cache
- `resource:getlist` - Get a list of resources
- `resource:create` - Create a MODX resource
- `resource:update` - Update a MODX resource
- `user:getlist` - Get a list of users
- `template:getlist` - Get a list of templates
- And many more

To see all available commands, run:

```bash
modx list
```

### Examples

Display the CLI version:

```bash
modx version
```

Get system information:

```bash
modx system:info
```

Clear the MODX cache:

```bash
modx system:clearcache
```

Get a list of resources:

```bash
modx resource:getlist
```

Get a list of resources with filters:

```bash
modx resource:getlist --parent=1 --context=web --published=1
```

### Working with Multiple MODX Instances

Most commands in the MODX CLI require a MODX instance to be available. To see all available commands, you need to configure at least one MODX instance and set it as the default.

To add a MODX instance:

```bash
modx config:add mysite --base_path=/path/to/modx/
```

To set a MODX instance as the default:

```bash
modx config:set-default mysite
```

You can also specify a MODX instance to run a command on:

```bash
modx --site=mysite system:info
```

### SSH and Aliases

MODX CLI supports running commands on remote servers via SSH and using aliases to simplify working with multiple MODX installations.

#### SSH Mode

Run commands on a remote server:

```bash
modx --ssh=user@example.com:/path/to/modx system:info
```

#### Aliases

Define aliases in `~/.modx/config.yml` or `modx-cli.yml` in your project directory:

```yaml
@prod:
  ssh: user@production-server.com:/path/to/modx
@staging:
  ssh: user@staging-server.com:/path/to/modx
```

Use aliases to run commands:

```bash
modx @prod system:info
```

Define alias groups to run commands on multiple servers:

```yaml
@all:
  - @prod
  - @staging
```

```bash
modx @all system:clear-cache
```

For more information, see [SSH and Aliases Documentation](docs/ssh-and-aliases.md).

## Bash Completion

To enable bash completion, add the following to your `.bashrc` or `.bash_profile`:

```bash
source /path/to/modx_completion.sh
```

## Building the PHAR

To build the PHAR file:

```bash
composer install --no-dev
box compile
```

This will create a `modx-cli.phar` file in the root directory.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

MIT
