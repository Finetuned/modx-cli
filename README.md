# MODX CLI

A command-line interface for MODX 3, built with Symfony Console.

## Requirements

- PHP 7.4 or higher
- MODX 3.0.0 or higher

## Installation

### Via Composer

```bash
composer global require finetuned/modx-cli
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
- `resource:list` - Get a list of resources
- `resource:create` - Create a MODX resource
- `resource:update` - Update a MODX resource (supports partial updates)
- `chunk:list` - Get a list of chunks
- `chunk:create` - Create a MODX chunk
- `chunk:update` - Update a MODX chunk (supports partial updates)
- `template:list` - Get a list of templates
- `template:create` - Create a MODX template
- `template:update` - Update a MODX template (supports partial updates)
- `snippet:list` - Get a list of snippets
- `snippet:create` - Create a MODX snippet
- `snippet:update` - Update a MODX snippet (supports partial updates)
- `tv:list` - Get a list of template variables
- `tv:create` - Create a MODX template variable
- `tv:update` - Update a MODX template variable (supports partial updates)
- `user:list` - Get a list of users
- `package:list` - Get a list of packages (supports pagination)
- `crawl` - Crawl resources to prime their caches
- And many more

To see all available commands, run:

```bash
modx list
```

#### Command Features

**Update Commands**: All update commands now support partial updates - you only need to specify the ID and the fields you want to change. The CLI automatically fetches existing data to populate required fields.

**List Commands**: All list commands support pagination with `--limit` and `--start` options for navigating large datasets.

**JSON Output**: All commands support `--json` flag for machine-readable output.

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
modx resource:list
```

Get a list of resources with filters and pagination:

```bash
modx resource:list --parent=1 --context=web --published=1 --limit=20 --start=0
```

#### Update Command Examples

Update only the title of a resource (partial update):

```bash
modx resource:update 123 --pagetitle="New Title"
```

Update multiple fields of a chunk:

```bash
modx chunk:update 5 --description="Updated description" --snippet="<p>New content</p>"
```

Update a template variable with new default value:

```bash
modx tv:update 10 --default_text="New default value" --description="Updated TV"
```

#### Create Command Examples

Create a new resource with specific settings:

```bash
modx resource:create "My New Page" --parent=1 --template=2 --published=1
```

Create a new chunk:

```bash
modx chunk:create "MyChunk" --description="A new chunk" --snippet="<p>Chunk content</p>"
```

#### List Command Examples with Pagination

Get the first 10 packages:

```bash
modx package:list --limit=10 --start=0
```

Get the next 10 packages:

```bash
modx package:list --limit=10 --start=10
```

#### JSON Output Examples

Get resource data in JSON format:

```bash
modx resource:get 123 --json
```

Get a list of templates in JSON format:

```bash
modx template:list --json
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

## Documentation

- [Update Commands](docs/update-commands.md) - Detailed guide to the enhanced update functionality
- [List Commands](docs/list-commands.md) - Pagination and filtering for list commands
- [SSH and Aliases](docs/ssh-and-aliases.md) - Remote command execution and aliases
- [Internal API](docs/internal-api.md) - Programmatic usage and extending the CLI

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
