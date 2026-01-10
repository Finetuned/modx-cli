# CLI Help Output

Generated via `bin/modx help <command>` for each command.

## crawl

```
Description:
  Crawl resources to prime their caches

Usage:
  crawl [options] [--] <from>

Arguments:
  from                       The context key or resource ID to crawl from. Use "all" to process all web contexts.

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## download

```
Description:
  Download a MODX Revolution release

Usage:
  download [options] [--] [<version> [<path>]]

Arguments:
  version                    The version you want to download [default: "latest"]
  path                       The path to download the file to [default: "/Users/julianweaver/.modx/releases/"]

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -a, --advanced             Whether or not you want an advanced release
  -k, --sdk                  Whether or not you want the SDK version
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## find

```
Description:
  Search within this MODX instance using the "uberbar" search

Usage:
  find [options] [--] <query>

Arguments:
  query                        The request to perform the search against

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## install

```
Description:
  Install MODX here

Usage:
  install [options] [--] [<source> [<config>]]

Arguments:
  source                     Path to MODX source (unused while command is disabled) [default: ""]
  config                     Path to configuration file (unused while command is disabled) [default: ""]

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## run-sequence

```
Description:
  Run a sequence of commands with various execution options

Usage:
  run-sequence [options] [--] [<command>]

Arguments:
  command                          The command to execute

Options:
      --json                       Output results in JSON format
      --ssh=SSH                    Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
      --command_sets=COMMAND_SETS  JSON string containing command sets to execute
  -h, --help                       Display help for the given command. When no command is given display help for the list command
  -q, --quiet                      Do not output any message
  -V, --version                    Display this application version
      --ansi|--no-ansi             Force (or disable --no-ansi) ANSI output
  -n, --no-interaction             Do not ask any interactive question
  -s, --site[=SITE]                An instance name to execute the command to
      --log-level=LOG-LEVEL        Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE          Write logs to specified file
  -v|vv|vvv, --verbose             Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
  This command allows you to run multiple commands in sequence with various execution options.
```

## version

```
Description:
  Display the CLI version

Usage:
  version [options]

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## category:create

```
Description:
  Create a MODX category

Usage:
  category:create [options] [--] <category>

Arguments:
  category                     The name of the category

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --parent=PARENT          The parent ID of the category [default: 0]
      --rank=RANK              The rank of the category [default: 0]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## category:get

```
Description:
  Get a MODX category

Usage:
  category:get [options] [--] <id>

Arguments:
  id                           The ID of the category to get

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --format=FORMAT          Output format (table, json) [default: "table"]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## category:list

```
Description:
  Get a list of categories in MODX

Usage:
  category:list [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## category:remove

```
Description:
  Remove a MODX category

Usage:
  category:remove [options] [--] <id>

Arguments:
  id                           The ID of the category to remove

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --force                  Force removal without confirmation
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## category:update

```
Description:
  Update a MODX category

Usage:
  category:update [options] [--] <id>

Arguments:
  id                           The ID of the category to update

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --category=CATEGORY      The name of the category
      --parent=PARENT          The parent ID of the category
      --rank=RANK              The rank of the category
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## chunk:create

```
Description:
  Create a MODX chunk

Usage:
  chunk:create [options] [--] <name>

Arguments:
  name                           The name of the chunk

Options:
      --json                     Output results in JSON format
      --ssh=SSH                  Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES    An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS          An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET              An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                  An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --description=DESCRIPTION  The description of the chunk [default: ""]
      --category=CATEGORY        The category ID of the chunk [default: 0]
      --snippet=SNIPPET          The content of the chunk [default: ""]
      --locked=LOCKED            Whether the chunk is locked (1 or 0) [default: 0]
      --static=STATIC            Whether the chunk is static (1 or 0) [default: 0]
      --static_file=STATIC_FILE  The static file path for the chunk [default: ""]
  -h, --help                     Display help for the given command. When no command is given display help for the list command
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi|--no-ansi           Force (or disable --no-ansi) ANSI output
  -n, --no-interaction           Do not ask any interactive question
  -s, --site[=SITE]              An instance name to execute the command to
      --log-level=LOG-LEVEL      Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE        Write logs to specified file
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## chunk:get

```
Description:
  Get a MODX chunk

Usage:
  chunk:get [options] [--] <id>

Arguments:
  id                           The ID of the chunk to get

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --format=FORMAT          Output format (table, json) [default: "table"]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## chunk:list

```
Description:
  Get a list of chunks in MODX

Usage:
  chunk:list [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
      --category=CATEGORY      Filter by category ID
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## chunk:remove

```
Description:
  Remove a MODX chunk

Usage:
  chunk:remove [options] [--] <id>

Arguments:
  id                           The ID of the chunk to remove

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --force                  Force removal without confirmation
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## chunk:update

```
Description:
  Update a MODX chunk

Usage:
  chunk:update [options] [--] <id>

Arguments:
  id                             The ID of the chunk to update

Options:
      --json                     Output results in JSON format
      --ssh=SSH                  Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES    An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS          An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET              An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                  An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --name=NAME                The name of the chunk
      --description=DESCRIPTION  The description of the chunk
      --category=CATEGORY        The category ID of the chunk
      --snippet=SNIPPET          The content of the chunk
      --locked=LOCKED            Whether the chunk is locked (1 or 0)
      --static=STATIC            Whether the chunk is static (1 or 0)
      --static_file=STATIC_FILE  The static file path for the chunk
  -h, --help                     Display help for the given command. When no command is given display help for the list command
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi|--no-ansi           Force (or disable --no-ansi) ANSI output
  -n, --no-interaction           Do not ask any interactive question
  -s, --site[=SITE]              An instance name to execute the command to
      --log-level=LOG-LEVEL      Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE        Write logs to specified file
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## config:add

```
Description:
  Add a MODX instance to the configuration

Usage:
  config:add [options] [--] <name>

Arguments:
  name                       The name of the instance

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
      --base_path=BASE_PATH  The base path of the MODX instance
      --default              Set this instance as the default
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

Help:
  This command adds a MODX instance to the configuration, allowing you to run commands on it.
```

## config:edit

```
Description:
  Edit a MODX instance in the configuration

Usage:
  config:edit [options] [--] <name>

Arguments:
  name                       The name of the instance to edit

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
      --base_path=BASE_PATH  The base path of the MODX instance
      --default              Set this instance as the default
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## config:exclude-command

```
Description:
  Exclude a command from the available commands

Usage:
  config:exclude-command [options] [--] <class>

Arguments:
  class                      The command class to exclude

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## config:get-default

```
Description:
  Get the default MODX instance

Usage:
  config:get-default [options]

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## config:get-exclude-command

```
Description:
  Get the list of excluded commands

Usage:
  config:get-exclude-command [options]

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## config:list

```
Description:
  List MODX instances in the configuration

Usage:
  config:list [options]

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## config:rename

```
Description:
  Rename a MODX instance in the configuration

Usage:
  config:rename [options] [--] <old_name> <new_name>

Arguments:
  old_name                   The current name of the instance
  new_name                   The new name of the instance

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## config:rm

```
Description:
  Remove a MODX instance from the configuration

Usage:
  config:rm [options] [--] <name>

Arguments:
  name                       The name of the instance to remove

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## config:rm-default

```
Description:
  Remove the default MODX instance

Usage:
  config:rm-default [options]

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## config:rm-exclude-command

```
Description:
  Remove a command from the excluded commands

Usage:
  config:rm-exclude-command [options] [--] <class>

Arguments:
  class                      The command class to remove from the excluded commands

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## config:set-default

```
Description:
  Set a MODX instance as the default

Usage:
  config:set-default [options] [--] <name>

Arguments:
  name                       The name of the instance to set as default

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## config:wipe-exclude-command

```
Description:
  Wipe all excluded commands

Usage:
  config:wipe-exclude-command [options]

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## context:create

```
Description:
  Create a MODX context

Usage:
  context:create [options] [--] <key>

Arguments:
  key                            The context key (unique identifier)

Options:
      --json                     Output results in JSON format
      --ssh=SSH                  Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES    An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS          An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET              An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                  An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --name=NAME                The name of the context [default: ""]
      --description=DESCRIPTION  The description of the context [default: ""]
      --rank=RANK                The rank/order of the context [default: 0]
  -h, --help                     Display help for the given command. When no command is given display help for the list command
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi|--no-ansi           Force (or disable --no-ansi) ANSI output
  -n, --no-interaction           Do not ask any interactive question
  -s, --site[=SITE]              An instance name to execute the command to
      --log-level=LOG-LEVEL      Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE        Write logs to specified file
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## context:get

```
Description:
  Get a MODX context by key

Usage:
  context:get [options] [--] <key>

Arguments:
  key                          The context key

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## context:geturls

```
Description:
  Get a list of context URLs in MODX

Usage:
  context:geturls [options]

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## context:list

```
Description:
  Get a list of contexts in MODX

Usage:
  context:list [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## context:permissions

```
Description:
  List context access permissions for a context

Usage:
  context:permissions [options] [--] <context>

Arguments:
  context                      The context key

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
      --usergroup=USERGROUP    Filter by user group ID
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## context:permissions:create

```
Description:
  Create a context access permission

Usage:
  context:permissions:create [options] [--] <context> <usergroup> <policy>

Arguments:
  context                      The context key
  usergroup                    The user group ID
  policy                       The access policy ID

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --authority=AUTHORITY    The authority level [default: 0]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## context:permissions:remove

```
Description:
  Remove a context access permission

Usage:
  context:permissions:remove [options] [--] <context> <id>

Arguments:
  context                      The context key
  id                           The access control entry ID

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --force                  Force removal without confirmation
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## context:permissions:update

```
Description:
  Update a context access permission

Usage:
  context:permissions:update [options] [--] <context> <id>

Arguments:
  context                      The context key
  id                           The access control entry ID

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --usergroup=USERGROUP    The user group ID
      --policy=POLICY          The access policy ID
      --authority=AUTHORITY    The authority level
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## context:remove

```
Description:
  Remove a MODX context

Usage:
  context:remove [options] [--] <key>

Arguments:
  key                          The context key

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --force                  Force removal without confirmation
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## context:setting:create

```
Description:
  Create a context setting

Usage:
  context:setting:create [options] [--] <context> <key>

Arguments:
  context                        The context key
  key                            The setting key

Options:
      --json                     Output results in JSON format
      --ssh=SSH                  Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES    An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS          An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET              An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                  An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --value=VALUE              The setting value
      --area=AREA                The setting area/category
      --namespace=NAMESPACE      The setting namespace [default: "core"]
      --xtype=XTYPE              The setting xtype [default: "textfield"]
      --name=NAME                The setting name
      --description=DESCRIPTION  The setting description
  -h, --help                     Display help for the given command. When no command is given display help for the list command
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi|--no-ansi           Force (or disable --no-ansi) ANSI output
  -n, --no-interaction           Do not ask any interactive question
  -s, --site[=SITE]              An instance name to execute the command to
      --log-level=LOG-LEVEL      Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE        Write logs to specified file
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## context:setting:get

```
Description:
  Get a context setting

Usage:
  context:setting:get [options] [--] <context> <key>

Arguments:
  context                      The context key
  key                          The setting key

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## context:setting:list

```
Description:
  Get a list of context settings in MODX

Usage:
  context:setting:list [options] [--] <context_key>

Arguments:
  context_key                  The context key

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## context:setting:remove

```
Description:
  Remove a context setting

Usage:
  context:setting:remove [options] [--] <context> <key>

Arguments:
  context                      The context key
  key                          The setting key

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --force                  Force removal without confirmation
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## context:setting:update

```
Description:
  Update a context setting

Usage:
  context:setting:update [options] [--] <context> <key>

Arguments:
  context                      The context key
  key                          The setting key

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --value=VALUE            The setting value
      --area=AREA              The setting area/category
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## context:update

```
Description:
  Update a MODX context

Usage:
  context:update [options] [--] <key>

Arguments:
  key                            The context key

Options:
      --json                     Output results in JSON format
      --ssh=SSH                  Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES    An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS          An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET              An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                  An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --name=NAME                The name of the context
      --description=DESCRIPTION  The description of the context
      --rank=RANK                The rank/order of the context
  -h, --help                     Display help for the given command. When no command is given display help for the list command
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi|--no-ansi           Force (or disable --no-ansi) ANSI output
  -n, --no-interaction           Do not ask any interactive question
  -s, --site[=SITE]              An instance name to execute the command to
      --log-level=LOG-LEVEL      Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE        Write logs to specified file
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## extra:add-component

```
Description:
  Add a component to MODX

Usage:
  extra:add-component [options] [--] <namespace>

Arguments:
  namespace                      The namespace of the component

Options:
      --json                     Output results in JSON format
      --ssh=SSH                  Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
      --path=PATH                The path of the component
      --assets_path=ASSETS_PATH  The assets path of the component
  -f, --force                    Force creation without confirmation
  -h, --help                     Display help for the given command. When no command is given display help for the list command
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi|--no-ansi           Force (or disable --no-ansi) ANSI output
  -n, --no-interaction           Do not ask any interactive question
  -s, --site[=SITE]              An instance name to execute the command to
      --log-level=LOG-LEVEL      Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE        Write logs to specified file
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## extra:components

```
Description:
  Get a list of components in MODX

Usage:
  extra:components [options]

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## extra:list

```
Description:
  Get a list of extras in MODX

Usage:
  extra:list [options]

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## extra:remove-component

```
Description:
  Remove a component from MODX

Usage:
  extra:remove-component [options] [--] <namespace>

Arguments:
  namespace                  The namespace of the component

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -f, --force                Force removal without confirmation
      --files                Remove files as well
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## menu:list

```
Description:
  Get a list of menus in MODX

Usage:
  menu:list [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## misc:list-columns

```
Description:
  List columns in a database table in MODX

Usage:
  misc:list-columns [options] [--] <table>

Arguments:
  table                      The name of the table

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## ns:create

```
Description:
  Create a namespace in MODX

Usage:
  ns:create [options] [--] <name>

Arguments:
  name                           The name of the namespace

Options:
      --json                     Output results in JSON format
      --ssh=SSH                  Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES    An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS          An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET              An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                  An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --path=PATH                The path of the namespace
      --assets_path=ASSETS_PATH  The assets path of the namespace
  -h, --help                     Display help for the given command. When no command is given display help for the list command
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi|--no-ansi           Force (or disable --no-ansi) ANSI output
  -n, --no-interaction           Do not ask any interactive question
  -s, --site[=SITE]              An instance name to execute the command to
      --log-level=LOG-LEVEL      Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE        Write logs to specified file
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## ns:list

```
Description:
  Get a list of namespaces in MODX

Usage:
  ns:list [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## ns:remove

```
Description:
  Remove a namespace in MODX

Usage:
  ns:remove [options] [--] <name>

Arguments:
  name                         The name of the namespace to remove

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --force                  Force removal without confirmation
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## ns:update

```
Description:
  Update a namespace in MODX

Usage:
  ns:update [options] [--] <name>

Arguments:
  name                           The name of the namespace to update

Options:
      --json                     Output results in JSON format
      --ssh=SSH                  Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES    An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS          An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET              An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                  An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --path=PATH                The path of the namespace
      --assets_path=ASSETS_PATH  The assets path of the namespace
  -h, --help                     Display help for the given command. When no command is given display help for the list command
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi|--no-ansi           Force (or disable --no-ansi) ANSI output
  -n, --no-interaction           Do not ask any interactive question
  -s, --site[=SITE]              An instance name to execute the command to
      --log-level=LOG-LEVEL      Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE        Write logs to specified file
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## package:download

```
Description:
  Download specific package versions to core/packages

Usage:
  package:download [options] [--] <signature>

Arguments:
  signature                  Package signature to download (e.g., pdotools-3.0.2-pl)

Options:
      --force[=FORCE]        Overwrite existing downloads
      --verify[=VERIFY]      Verify download integrity
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## package:install

```
Description:
  Install a package in MODX

Usage:
  package:install [options] [--] <signature>

Arguments:
  signature                    The signature of the package to install

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --force                  Force installation without confirmation
      --no-download            Disable auto-download if package is not found
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## package:list

```
Description:
  Get a list of packages in MODX

Usage:
  package:list [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## package:provider:add

```
Description:
  Add a package provider in MODX

Usage:
  package:provider:add [options] [--] <name> <service_url>

Arguments:
  name                           The name of the provider
  service_url                    The service URL of the provider

Options:
      --json                     Output results in JSON format
      --ssh=SSH                  Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES    An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS          An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET              An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                  An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --username=USERNAME        The username for the provider
      --password=PASSWORD        The password for the provider
      --description=DESCRIPTION  The description of the provider
  -h, --help                     Display help for the given command. When no command is given display help for the list command
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi|--no-ansi           Force (or disable --no-ansi) ANSI output
  -n, --no-interaction           Do not ask any interactive question
  -s, --site[=SITE]              An instance name to execute the command to
      --log-level=LOG-LEVEL      Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE        Write logs to specified file
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## package:provider:categories

```
Description:
  Get a list of categories from a provider in MODX

Usage:
  package:provider:categories [options] [--] <provider>

Arguments:
  provider                     The ID of the provider

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## package:provider:info

```
Description:
  Get information about a package provider in MODX

Usage:
  package:provider:info [options] [--] <id>

Arguments:
  id                           The ID of the provider

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --format=FORMAT          Output format (table, json) [default: "table"]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## package:provider:list

```
Description:
  Get a list of package providers in MODX

Usage:
  package:provider:list [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## package:provider:packages

```
Description:
  Get a list of packages from a provider in MODX

Usage:
  package:provider:packages [options] [--] <provider>

Arguments:
  provider                     The ID of the provider

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
      --query=QUERY            Search query
      --category=CATEGORY      Filter by category
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## package:upgradeable

```
Description:
  Get a list of upgradeable packages in MODX

Usage:
  package:upgradeable [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## plugin:disabled

```
Description:
  Get a list of disabled plugins in MODX

Usage:
  plugin:disabled [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## plugin:list

```
Description:
  Get a list of plugins in MODX

Usage:
  plugin:list [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## registry:message:list

```
Description:
  Get a list of registry messages in MODX

Usage:
  registry:message:list [options] [--] <topic>

Arguments:
  topic                        The topic of the messages

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
      --register=REGISTER      The register to use [default: "db"]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## registry:queue:list

```
Description:
  Get a list of registry queues in MODX

Usage:
  registry:queue:list [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
      --register=REGISTER      The register to use [default: "db"]
      --topic=TOPIC            The topic to filter by
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## registry:read

```
Description:
  Read messages from a MODX registry register

Usage:
  registry:read [options] [--] <topic>

Arguments:
  topic                                The topic to read from

Options:
      --json                           Output results in JSON format
      --ssh=SSH                        Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES          An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS                An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET                    An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                        An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --register=REGISTER              Registry name to use [default: "db"]
      --format=FORMAT                  Output format (json, html_log, raw) [default: "json"]
      --register_class=REGISTER_CLASS  Custom registry class (optional)
      --poll_limit=POLL_LIMIT          Number of poll cycles [default: 1]
      --poll_interval=POLL_INTERVAL    Interval between polls [default: 1]
      --time_limit=TIME_LIMIT          Time limit for polling [default: 10]
      --message_limit=MESSAGE_LIMIT    Maximum messages to read [default: 200]
      --keep                           Keep messages after reading
      --include_keys                   Include message keys in the output
      --show_filename                  Include message filename metadata
  -h, --help                           Display help for the given command. When no command is given display help for the list command
  -q, --quiet                          Do not output any message
  -V, --version                        Display this application version
      --ansi|--no-ansi                 Force (or disable --no-ansi) ANSI output
  -n, --no-interaction                 Do not ask any interactive question
  -s, --site[=SITE]                    An instance name to execute the command to
      --log-level=LOG-LEVEL            Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE              Write logs to specified file
  -v|vv|vvv, --verbose                 Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## registry:send

```
Description:
  Send a message to a MODX registry register

Usage:
  registry:send [options] [--] <topic> <message>

Arguments:
  topic                                The topic to send to
  message                              The message to send

Options:
      --json                           Output results in JSON format
      --ssh=SSH                        Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES          An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS                An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET                    An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                        An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --register=REGISTER              Registry name to use [default: "db"]
      --register_class=REGISTER_CLASS  Custom registry class (optional)
      --message_key=MESSAGE_KEY        Optional message key
      --message_format=MESSAGE_FORMAT  Message format (string, json) [default: "string"]
      --delay=DELAY                    Delay in seconds [default: 0]
      --ttl=TTL                        Time-to-live in seconds [default: 0]
      --kill                           Kill the register after sending
  -h, --help                           Display help for the given command. When no command is given display help for the list command
  -q, --quiet                          Do not output any message
  -V, --version                        Display this application version
      --ansi|--no-ansi                 Force (or disable --no-ansi) ANSI output
  -n, --no-interaction                 Do not ask any interactive question
  -s, --site[=SITE]                    An instance name to execute the command to
      --log-level=LOG-LEVEL            Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE              Write logs to specified file
  -v|vv|vvv, --verbose                 Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## registry:topic:list

```
Description:
  Get a list of registry topics in MODX

Usage:
  registry:topic:list [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
      --register=REGISTER      The register to use [default: "db"]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## resource:create

```
Description:
  Create a MODX resource

Usage:
  resource:create [options] [--] <pagetitle>

Arguments:
  pagetitle                      The page title of the resource

Options:
      --json                     Output results in JSON format
      --ssh=SSH                  Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES    An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS          An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET              An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                  An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --parent=PARENT            The parent ID of the resource [default: 0]
      --template=TEMPLATE        The template ID of the resource [default: 0]
      --published=PUBLISHED      Whether the resource is published (1 or 0) [default: 1]
      --hidemenu=HIDEMENU        Whether the resource is hidden from the menu (1 or 0) [default: 0]
      --content=CONTENT          The content of the resource [default: ""]
      --alias=ALIAS              The alias of the resource [default: ""]
      --context_key=CONTEXT_KEY  The context key of the resource [default: "web"]
  -h, --help                     Display help for the given command. When no command is given display help for the list command
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi|--no-ansi           Force (or disable --no-ansi) ANSI output
  -n, --no-interaction           Do not ask any interactive question
  -s, --site[=SITE]              An instance name to execute the command to
      --log-level=LOG-LEVEL      Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE        Write logs to specified file
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## resource:delete

```
Description:
  Delete a MODX resource (move to trash)

Usage:
  resource:delete [options] [--] <id>

Arguments:
  id                           The ID of the resource to delete

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --force                  Force deletion without confirmation
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## resource:erase

```
Description:
  Erase a MODX resource (permanently delete from trash)

Usage:
  resource:erase [options] [--] <id>

Arguments:
  id                           The ID of the resource to erase

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --force                  Force erase without confirmation
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## resource:get

```
Description:
  Get a MODX resource

Usage:
  resource:get [options] [--] <id>

Arguments:
  id                           The ID of the resource to get

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --format=FORMAT          Output format (table, json) [default: "table"]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## resource:list

```
Description:
  Get a list of resources

Usage:
  resource:list [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
      --parent=PARENT          Filter by parent ID
      --context=CONTEXT        Filter by context key
      --published=PUBLISHED    Filter by published status (1 or 0)
      --hidemenu=HIDEMENU      Filter by hidemenu status (1 or 0)
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## resource:update

```
Description:
  Update a MODX resource

Usage:
  resource:update [options] [--] <id>

Arguments:
  id                             The ID of the resource to update

Options:
      --json                     Output results in JSON format
      --ssh=SSH                  Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES    An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS          An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET              An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                  An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --pagetitle=PAGETITLE      The page title of the resource
      --parent=PARENT            The parent ID of the resource
      --template=TEMPLATE        The template ID of the resource
      --published=PUBLISHED      Whether the resource is published (1 or 0)
      --hidemenu=HIDEMENU        Whether the resource is hidden from the menu (1 or 0)
      --content=CONTENT          The content of the resource
      --alias=ALIAS              The alias of the resource
      --context_key=CONTEXT_KEY  The context key of the resource
  -h, --help                     Display help for the given command. When no command is given display help for the list command
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi|--no-ansi           Force (or disable --no-ansi) ANSI output
  -n, --no-interaction           Do not ask any interactive question
  -s, --site[=SITE]              An instance name to execute the command to
      --log-level=LOG-LEVEL      Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE        Write logs to specified file
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## security:access:flush

```
Description:
  Flush permissions in MODX

Usage:
  security:access:flush [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --force                  Force flush without confirmation
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## security:rolegroup:addpolicy

```
Description:
  Add a policy template to a role group

Usage:
  security:rolegroup:addpolicy [options] [--] <group> <policy>

Arguments:
  group                      The role group name or ID
  policy                     The policy template name or ID

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## security:rolegroup:removepolicy

```
Description:
  Remove a policy template from a role group

Usage:
  security:rolegroup:removepolicy [options] [--] <group> <policy>

Arguments:
  group                      The role group name or ID
  policy                     The policy template name or ID

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## session:flush

```
Description:
  Flush all sessions in MODX

Usage:
  session:flush [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --force                  Force flush without confirmation
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## session:list

```
Description:
  Get a list of sessions in MODX

Usage:
  session:list [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## session:remove

```
Description:
  Remove a session in MODX

Usage:
  session:remove [options] [--] <id>

Arguments:
  id                         The ID (internalKey) of the session to remove

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -f, --force                Force removal without confirmation
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## snippet:create

```
Description:
  Create a MODX snippet

Usage:
  snippet:create [options] [--] <name>

Arguments:
  name                           The name of the snippet

Options:
      --json                     Output results in JSON format
      --ssh=SSH                  Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES    An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS          An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET              An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                  An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --description=DESCRIPTION  The description of the snippet [default: ""]
      --category=CATEGORY        The category ID of the snippet [default: 0]
      --snippet=SNIPPET          The PHP code of the snippet [default: ""]
      --locked=LOCKED            Whether the snippet is locked (1 or 0) [default: 0]
      --static=STATIC            Whether the snippet is static (1 or 0) [default: 0]
      --static_file=STATIC_FILE  The static file path for the snippet [default: ""]
  -h, --help                     Display help for the given command. When no command is given display help for the list command
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi|--no-ansi           Force (or disable --no-ansi) ANSI output
  -n, --no-interaction           Do not ask any interactive question
  -s, --site[=SITE]              An instance name to execute the command to
      --log-level=LOG-LEVEL      Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE        Write logs to specified file
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## snippet:get

```
Description:
  Get a MODX snippet

Usage:
  snippet:get [options] [--] <id>

Arguments:
  id                           The ID of the snippet to get

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --format=FORMAT          Output format (table, json) [default: "table"]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## snippet:list

```
Description:
  Get a list of snippets in MODX

Usage:
  snippet:list [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
      --category=CATEGORY      Filter by category ID
      --search=SEARCH          Search term to filter results
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## snippet:remove

```
Description:
  Remove a MODX snippet

Usage:
  snippet:remove [options] [--] <id>

Arguments:
  id                           The ID of the snippet to remove

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --force                  Force removal without confirmation
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## snippet:update

```
Description:
  Update a MODX snippet

Usage:
  snippet:update [options] [--] <id>

Arguments:
  id                             The ID of the snippet to update

Options:
      --json                     Output results in JSON format
      --ssh=SSH                  Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES    An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS          An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET              An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                  An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --name=NAME                The name of the snippet
      --description=DESCRIPTION  The description of the snippet
      --category=CATEGORY        The category ID of the snippet
      --snippet=SNIPPET          The PHP code of the snippet
      --locked=LOCKED            Whether the snippet is locked (1 or 0)
      --static=STATIC            Whether the snippet is static (1 or 0)
      --static_file=STATIC_FILE  The static file path for the snippet
  -h, --help                     Display help for the given command. When no command is given display help for the list command
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi|--no-ansi           Force (or disable --no-ansi) ANSI output
  -n, --no-interaction           Do not ask any interactive question
  -s, --site[=SITE]              An instance name to execute the command to
      --log-level=LOG-LEVEL      Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE        Write logs to specified file
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## source:create

```
Description:
  Create a MODX media source

Usage:
  source:create [options] [--] <name>

Arguments:
  name                                       The name of the media source

Options:
      --json                                 Output results in JSON format
      --ssh=SSH                              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES                An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS                      An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET                          An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                              An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --description=DESCRIPTION              The description of the media source [default: ""]
      --class_key=CLASS_KEY                  The class key of the media source [default: "MODX\Revolution\Sources\modFileMediaSource"]
      --source-properties=SOURCE-PROPERTIES  The properties of the media source (JSON format) [default: ""]
  -h, --help                                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                                Do not output any message
  -V, --version                              Display this application version
      --ansi|--no-ansi                       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction                       Do not ask any interactive question
  -s, --site[=SITE]                          An instance name to execute the command to
      --log-level=LOG-LEVEL                  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE                    Write logs to specified file
  -v|vv|vvv, --verbose                       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## source:get

```
Description:
  Get a MODX media source by ID

Usage:
  source:get [options] [--] <id>

Arguments:
  id                           The source ID

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## source:list

```
Description:
  Get a list of media sources in MODX

Usage:
  source:list [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## source:remove

```
Description:
  Remove a MODX media source

Usage:
  source:remove [options] [--] <id>

Arguments:
  id                           The source ID

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --force                  Force removal without confirmation
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## source:update

```
Description:
  Update a MODX media source

Usage:
  source:update [options] [--] <id>

Arguments:
  id                                         The source ID

Options:
      --json                                 Output results in JSON format
      --ssh=SSH                              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES                An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS                      An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET                          An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                              An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --name=NAME                            The name of the media source
      --description=DESCRIPTION              The description of the media source
      --class_key=CLASS_KEY                  The class key of the media source
      --source-properties=SOURCE-PROPERTIES  The properties of the media source (JSON format)
  -h, --help                                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                                Do not output any message
  -V, --version                              Display this application version
      --ansi|--no-ansi                       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction                       Do not ask any interactive question
  -s, --site[=SITE]                          An instance name to execute the command to
      --log-level=LOG-LEVEL                  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE                    Write logs to specified file
  -v|vv|vvv, --verbose                       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## system:clearcache

```
Description:
  Clear the MODX cache

Usage:
  system:clearcache [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## system:event:create

```
Description:
  Create a system event in MODX

Usage:
  system:event:create [options] [--] <name>

Arguments:
  name                         The name of the event

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --service=SERVICE        The service of the event [default: 1]
      --groupname=GROUPNAME    The group name of the event [default: ""]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## system:event:delete

```
Description:
  Delete a system event in MODX

Usage:
  system:event:delete [options] [--] <id>

Arguments:
  id                           The ID of the event

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --force                  Force deletion without confirmation
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## system:info

```
Description:
  Get general system information

Usage:
  system:info [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## system:locks:read

```
Description:
  Read a lock in MODX

Usage:
  system:locks:read [options] [--] [<key>]

Arguments:
  key                        The key of the lock

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -f, --format=FORMAT        Output format (table, json) [default: "table"]
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## system:locks:remove

```
Description:
  Remove a lock in MODX

Usage:
  system:locks:remove [options] [--] <key>

Arguments:
  key                        The key of the lock

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -f, --force                Force removal without confirmation
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## system:log:actions:list

```
Description:
  Get a list of action logs in MODX

Usage:
  system:log:actions:list [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
      --user=USER              Filter by user ID
      --action=ACTION          Filter by action
      --classKey=CLASSKEY      Filter by class key
      --item=ITEM              Filter by item
      --dateStart=DATESTART    Filter by start date (YYYY-MM-DD)
      --dateEnd=DATEEND        Filter by end date (YYYY-MM-DD)
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## system:log:actions:truncate

```
Description:
  Truncate action logs in MODX

Usage:
  system:log:actions:truncate [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --force                  Force truncation without confirmation
      --age=AGE                Truncate logs older than this many days [default: 0]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## system:log:clear

```
Description:
  Clear the MODX system log

Usage:
  system:log:clear [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## system:log:listen

```
Description:
  Listen to the MODX system log

Usage:
  system:log:listen [options]

Options:
      --json                 Output results in JSON format
      --ssh=SSH              Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -i, --interval=INTERVAL    Interval in seconds between checks [default: 1]
  -l, --limit=LIMIT          Number of log entries to display initially [default: 10]
  -h, --help                 Display help for the given command. When no command is given display help for the list command
  -q, --quiet                Do not output any message
  -V, --version              Display this application version
      --ansi|--no-ansi       Force (or disable --no-ansi) ANSI output
  -n, --no-interaction       Do not ask any interactive question
  -s, --site[=SITE]          An instance name to execute the command to
      --log-level=LOG-LEVEL  Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE    Write logs to specified file
  -v|vv|vvv, --verbose       Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## system:log:view

```
Description:
  View the MODX system log

Usage:
  system:log:view [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
      --level=LEVEL            Filter by log level (ERROR, WARN, INFO, DEBUG)
  -f, --format=FORMAT          Output format (table, colored) [default: "table"]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## system:refreshuris

```
Description:
  Refresh URIs in MODX

Usage:
  system:refreshuris [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## system:setting:list

```
Description:
  Get a list of system settings in MODX

Usage:
  system:setting:list [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
      --area=AREA              Filter by area
      --namespace=NAMESPACE    Filter by namespace
      --query=QUERY            Search query
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## system:snippet:list

```
Description:
  Get a list of snippets in MODX

Usage:
  system:snippet:list [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
      --category=CATEGORY      Filter by category ID
      --search=SEARCH          Search term to filter results
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## tv:create

```
Description:
  Create a MODX template variable

Usage:
  tv:create [options] [--] <name>

Arguments:
  name                             The name of the template variable

Options:
      --json                       Output results in JSON format
      --ssh=SSH                    Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES      An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS            An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET                An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                    An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --caption=CAPTION            The caption of the template variable [default: ""]
      --description=DESCRIPTION    The description of the template variable [default: ""]
      --category=CATEGORY          The category ID of the template variable [default: 0]
      --type=TYPE                  The input type of the template variable (text, textarea, richtext, etc.) [default: "text"]
      --default_text=DEFAULT_TEXT  The default value of the template variable [default: ""]
      --elements=ELEMENTS          The possible values for the template variable (for select, radio, etc.) [default: ""]
      --rank=RANK                  The rank of the template variable [default: 0]
      --display=DISPLAY            The display type of the template variable [default: "default"]
      --templates=TEMPLATES        Comma-separated list of template IDs to associate with the template variable [default: ""]
      --locked=LOCKED              Whether the template variable is locked (1 or 0) [default: 0]
      --static=STATIC              Whether the template variable is static (1 or 0) [default: 0]
      --static_file=STATIC_FILE    The static file path for the template variable [default: ""]
  -h, --help                       Display help for the given command. When no command is given display help for the list command
  -q, --quiet                      Do not output any message
  -V, --version                    Display this application version
      --ansi|--no-ansi             Force (or disable --no-ansi) ANSI output
  -n, --no-interaction             Do not ask any interactive question
  -s, --site[=SITE]                An instance name to execute the command to
      --log-level=LOG-LEVEL        Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE          Write logs to specified file
  -v|vv|vvv, --verbose             Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## tv:get

```
Description:
  Get a MODX template variable

Usage:
  tv:get [options] [--] <id>

Arguments:
  id                           The ID of the template variable to get

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --format=FORMAT          Output format (table, json) [default: "table"]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## tv:list

```
Description:
  Get a list of template variables in MODX

Usage:
  tv:list [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
      --category=CATEGORY      Filter by category ID
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## tv:remove

```
Description:
  Remove a MODX template variable

Usage:
  tv:remove [options] [--] <id>

Arguments:
  id                           The ID of the template variable to remove

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --force                  Force removal without confirmation
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## tv:update

```
Description:
  Update a MODX template variable

Usage:
  tv:update [options] [--] <id>

Arguments:
  id                               The ID of the template variable to update

Options:
      --json                       Output results in JSON format
      --ssh=SSH                    Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES      An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS            An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET                An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                    An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --name=NAME                  The name of the template variable
      --caption=CAPTION            The caption of the template variable
      --description=DESCRIPTION    The description of the template variable
      --category=CATEGORY          The category ID of the template variable
      --type=TYPE                  The input type of the template variable (text, textarea, richtext, etc.)
      --default_text=DEFAULT_TEXT  The default value of the template variable
      --elements=ELEMENTS          The possible values for the template variable (for select, radio, etc.)
      --rank=RANK                  The rank of the template variable
      --display=DISPLAY            The display type of the template variable
      --templates=TEMPLATES        Comma-separated list of template IDs to associate with the template variable
      --locked=LOCKED              Whether the template variable is locked (1 or 0)
      --static=STATIC              Whether the template variable is static (1 or 0)
      --static_file=STATIC_FILE    The static file path for the template variable
  -h, --help                       Display help for the given command. When no command is given display help for the list command
  -q, --quiet                      Do not output any message
  -V, --version                    Display this application version
      --ansi|--no-ansi             Force (or disable --no-ansi) ANSI output
  -n, --no-interaction             Do not ask any interactive question
  -s, --site[=SITE]                An instance name to execute the command to
      --log-level=LOG-LEVEL        Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE          Write logs to specified file
  -v|vv|vvv, --verbose             Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## template:create

```
Description:
  Create a MODX template

Usage:
  template:create [options] [--] <templatename>

Arguments:
  templatename                   The name of the template

Options:
      --json                     Output results in JSON format
      --ssh=SSH                  Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES    An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS          An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET              An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                  An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --description=DESCRIPTION  The description of the template [default: ""]
      --category=CATEGORY        The category ID of the template [default: 0]
      --content=CONTENT          The HTML content of the template [default: ""]
      --locked=LOCKED            Whether the template is locked (1 or 0) [default: 0]
      --static=STATIC            Whether the template is static (1 or 0) [default: 0]
      --static_file=STATIC_FILE  The static file path for the template [default: ""]
      --icon=ICON                The icon for the template [default: ""]
  -h, --help                     Display help for the given command. When no command is given display help for the list command
  -q, --quiet                    Do not output any message
  -V, --version                  Display this application version
      --ansi|--no-ansi           Force (or disable --no-ansi) ANSI output
  -n, --no-interaction           Do not ask any interactive question
  -s, --site[=SITE]              An instance name to execute the command to
      --log-level=LOG-LEVEL      Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE        Write logs to specified file
  -v|vv|vvv, --verbose           Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## template:get

```
Description:
  Get a MODX template

Usage:
  template:get [options] [--] <id>

Arguments:
  id                           The ID of the template to get

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --format=FORMAT          Output format (table, json) [default: "table"]
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## template:list

```
Description:
  Get a list of templates in MODX

Usage:
  template:list [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
      --category=CATEGORY      Filter by category ID
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## template:remove

```
Description:
  Remove a MODX template

Usage:
  template:remove [options] [--] <id>

Arguments:
  id                           The ID of the template to remove

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -f, --force                  Force removal without confirmation
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## template:update

```
Description:
  Update a MODX template

Usage:
  template:update [options] [--] <id>

Arguments:
  id                               The ID of the template to update

Options:
      --json                       Output results in JSON format
      --ssh=SSH                    Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES      An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS            An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET                An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                    An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --templatename=TEMPLATENAME  The name of the template
      --description=DESCRIPTION    The description of the template
      --category=CATEGORY          The category ID of the template
      --content=CONTENT            The HTML content of the template
      --locked=LOCKED              Whether the template is locked (1 or 0)
      --static=STATIC              Whether the template is static (1 or 0)
      --static_file=STATIC_FILE    The static file path for the template
      --icon=ICON                  The icon for the template
  -h, --help                       Display help for the given command. When no command is given display help for the list command
  -q, --quiet                      Do not output any message
  -V, --version                    Display this application version
      --ansi|--no-ansi             Force (or disable --no-ansi) ANSI output
  -n, --no-interaction             Do not ask any interactive question
  -s, --site[=SITE]                An instance name to execute the command to
      --log-level=LOG-LEVEL        Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE          Write logs to specified file
  -v|vv|vvv, --verbose             Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## user:list

```
Description:
  Get a list of users in MODX

Usage:
  user:list [options]

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
  -l, --limit=LIMIT            Number of items to return (default: 10) [default: 10]
      --start=START            Starting index for pagination (default: 0) [default: 0]
      --active=ACTIVE          Filter by active status (1 or 0)
      --blocked=BLOCKED        Filter by blocked status (1 or 0)
      --usergroup=USERGROUP    Filter by user group ID
      --query=QUERY            Search query
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```

## user:resetpassword

```
Description:
  Reset a user's password in MODX

Usage:
  user:resetpassword [options] [--] <id>

Arguments:
  id                           The ID of the user

Options:
      --json                   Output results in JSON format
      --ssh=SSH                Run command on a remote server via SSH: [<user>@]<host>[:<port>][<path>]
  -p, --properties=PROPERTIES  An array of properties to be sent to the processor, ie. --properties='key=value' --properties='another_key=value' (multiple values allowed)
  -o, --options=OPTIONS        An array of options to be sent to the processor, ie. --options='processors_path=value' --options='location=value' (multiple values allowed)
  -u, --unset=UNSET            An array of columns to hidden from results table, ie. --unset=id --unset=name (multiple values allowed)
  -a, --add=ADD                An array of columns to add to results table, ie. --add=column -a'other_column' (multiple values allowed)
      --password=PASSWORD      The new password
  -g, --generate               Generate a random password
  -h, --help                   Display help for the given command. When no command is given display help for the list command
  -q, --quiet                  Do not output any message
  -V, --version                Display this application version
      --ansi|--no-ansi         Force (or disable --no-ansi) ANSI output
  -n, --no-interaction         Do not ask any interactive question
  -s, --site[=SITE]            An instance name to execute the command to
      --log-level=LOG-LEVEL    Set log level (debug, info, notice, warning, error, critical, alert, emergency)
      --log-file=LOG-FILE      Write logs to specified file
  -v|vv|vvv, --verbose         Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
```
