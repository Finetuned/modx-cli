Using https://github.com/meltingmedia/MODX-Shell as an example implementation for MODX v2.x, create a command-line interface (CLI) for interacting with MODX 3, a content management system who's source code is located at https://github.com/modxcms/revolution. Your approach should involve using the latest Symfony Console components and PHPUnit to create and manage commands that can be executed from the terminal and unit tests for all code created.  The intended output is a php phar format with the title modx-cli.phar

Here are the key components and their roles:

1. **Command Registration**:
   - The `CommandRegistrar` class in CommandRegistrar.php is responsible for registering commands. It processes the command registration by iterating over the `Command` folder and looking for command classes.

2. **Command Definitions**:
   - Commands are defined as classes extending `BaseCmd` in the Command directory. For example, the `Add` command in Add.php and the `Rm` command in Rm.php provide functionality to add and remove configuration entries, respectively.

3. **Configuration Management**:
   - Configuration is managed through classes implementing the `ConfigurationInterface` in ConfigurationInterface.php. The `Base` class in Base.php provides basic methods for getting, setting, and removing configuration items.

4. **Application Initialization**:
   - The `Application` class in Application.php extends Symfony's `BaseApp` and initializes various configurations and commands. It also handles the execution context, such as specifying the MODX instance to operate on.

5. **Bash Completion**:
   - The modx_completion.sh script in modx_completion.sh provides bash completion support for the CLI commands. It dynamically generates completion options based on the available commands and their options.

6. **Command Execution**:
   - The modx script in modx is the entry point for executing the CLI commands. It initializes the application and handles command execution.

Overall, this repository leverages Symfony Console to provide a structured and extensible CLI for managing MODX instances, configurations, and components.

The main commands available in this repository can be found in the Command directory in the MODX-Shell repo given above. 

Here are some of the key commands:

1. **Configuration Commands**:
   - `config:add` - Adds a configuration entry.
   - `config:rm` - Removes a configuration entry.
   - `config:exclude` - Excludes a command class from available commands.

2. **System Commands**:
   - `system:refreshuris` - Refreshes resource URIs.
   - `system:info` - Retrieves general MODX information.
   - `system:actions:list` - Lists manager actions log.
   - `system:actions:clear` - Clears manager actions log.
   - `system:events:create` - Creates system events.
   - `system:events:delete` - Deletes system events.
   - `system:log:listen` - Listens to the MODX system log.

3. **Package Commands**:
   - `package:upgradeable` - Lists packages with available upgrades.
   - `package:provider:add` - Adds a package provider.

4. **User Commands**:
   - `user:resetpassword` - Resets a user password.

5. **Plugin Commands**:
   - `plugin:disable` - Disables a plugin.

6. **Extra Commands**:
   - `extra:components` - Lists commands added by third-party components.
   - `extra:extras` - Lists third-party commands.

7. **Tree Commands**:
   - `menu:list` - Lists menu items.

8. **Crawl Commands**:
   - `crawl` - Crawls (cURL) resources to prime the MODX cache.

9. **Context Commands**:
   - `context:urls` - Lists context URLs.

These commands are registered and managed by the `CommandRegistrar` class and can be executed using the `modx` CLI tool.


The `melting-media/modx-package` requirement in the composer.json file is a dependency that provides additional functionality for the MODX CLI. This package is likely used to interact with MODX packages, enabling the CLI to manage and manipulate MODX packages effectively. unfortunaltety this package is no longer available at the url given. Use the information below and the MODX3 package code to synthesize the missing package code and make it internal to the new cli.

Here is how modx-package is integrated and used:

1. **Dependency Declaration**:
   - The `melting-media/modx-package` package is declared as a required dependency in the composer.json file:
     ```json
     "require": {
         "melting-media/modx-package": "0.2.*",
         "symfony/console": "^3.1",
         "symfony/process": "^3.1",
         "symfony/finder": "^3.1"
     }
     ```

2. **Autoloading**:
   - The `autoload` section ensures that the classes from the `melting-media/modx-package` are autoloaded using PSR-4:
     ```json
     "autoload": {
         "psr-4": {
             "MODX\\Shell\\": "src/"
         }
     }
     ```

3. **Usage in Commands**:
   - The functionality provided by `melting-media/modx-package` is likely utilized within the command classes located in the Command directory. For example, commands related to package management, such as listing upgradeable packages or adding package providers, would leverage this dependency.

4. **Integration in CommandRegistrar**:
   - The `CommandRegistrar` class in CommandRegistrar.php would register commands that utilize the `melting-media/modx-package` functionality. This ensures that the commands are available for use in the CLI.

5. **Example Command**:
   - An example command that might use `melting-media/modx-package` is `package:upgradeable`, which lists packages with available upgrades. This command would interact with the MODX package management system to retrieve and display the relevant information.

By replicating the missing `melting-media/modx-package` as a dependency, the MODX CLI gains the ability to manage MODX packages, enhancing its functionality and providing users with powerful tools for package management.