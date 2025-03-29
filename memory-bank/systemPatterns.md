# System Patterns

## System architecture

The MODX CLI follows a command-based architecture pattern where each command is implemented as a separate class. The system uses Symfony Console components to handle the command-line interface aspects.

### Key Components:

1. **Application**: The main entry point that initializes the CLI application and registers commands.
2. **CommandRegistrar**: Responsible for discovering and registering available commands.
3. **Commands**: Individual command classes that implement specific functionality.
4. **Configuration**: Classes that handle configuration management for the CLI.
5. **Internal API**: A set of classes that provide an API for extending and customizing the CLI.

## Key technical decisions

1. **Use of Symfony Console**: Provides a robust framework for building CLI applications with features like command registration, input/output handling, and help documentation.
2. **Namespaced Command Structure**: Commands are organized in a hierarchical namespace structure (e.g., `system:setting:get`) for better organization and discoverability.
3. **Configuration Storage**: Configuration is stored in JSON files in the user's home directory for persistence between sessions.
4. **MODX Integration**: The CLI connects to MODX installations through their core path and uses MODX's internal API for operations.
5. **SSH and Alias Support**: The CLI can execute commands on remote servers via SSH and supports aliases for simplifying command execution across multiple environments.
6. **YAML Configuration**: YAML is used for alias configuration files, providing a more human-readable format for complex configuration structures.
7. **Mock Objects for Testing**: PHPUnit with mock objects is used for testing to avoid requiring an actual MODX installation.

## Design patterns in use

1. **Command Pattern**: Each CLI command is encapsulated in its own class with a standardized interface.
2. **Factory Pattern**: Used for creating command instances and other objects.
3. **Dependency Injection**: Components receive their dependencies through constructors or setters.
4. **Singleton Pattern**: Used for certain manager classes that should only have one instance.
5. **Strategy Pattern**: Different strategies for handling various types of commands or operations.
6. **Adapter Pattern**: Used to adapt between the CLI interface and MODX's internal APIs.
7. **Proxy Pattern**: Used in the SSH implementation to proxy commands to remote servers.
8. **Facade Pattern**: The SSH Handler provides a simplified interface to the complex SSH functionality.

## Component relationships

1. **Application → CommandRegistrar**: The Application uses the CommandRegistrar to discover and register commands.
2. **CommandRegistrar → Commands**: The CommandRegistrar loads and registers individual command classes.
3. **Commands → MODX**: Commands interact with MODX through its API to perform operations.
4. **Commands → Configuration**: Commands use Configuration classes to read and write configuration data.
5. **Configuration → File System**: Configuration classes read from and write to configuration files on disk.
6. **Application → SSH**: The Application uses SSH classes to execute commands on remote servers.
7. **Application → Alias**: The Application uses Alias classes to resolve aliases to their actual connection details.
8. **Alias → Configuration**: Alias classes use Configuration classes to read alias definitions from YAML files.
9. **SSH → Process**: SSH classes use Symfony Process to execute commands on remote servers.

## Internal API Architecture

The Internal API follows a modular design pattern inspired by WP-CLI's internal API. It provides a set of classes for extending and customizing the CLI:

1. **MODX_CLI**: A static class that provides the main entry point for the API, with methods for registering commands, running commands programmatically, and hooking into the command lifecycle.
2. **CommandRegistry**: Manages the registration and retrieval of commands.
3. **HookRegistry**: Manages the registration and execution of hooks.
4. **CommandRunner**: Handles running commands programmatically with various options.
5. **CommandPublisher**: Provides asynchronous command execution using a pub/sub pattern.
6. **ClosureCommand**: Wraps closures as commands that can be executed by the CLI.

### Key Features:

1. **Command Registration**: Register custom commands using closures or classes.
2. **Command Execution**: Run commands programmatically with various options.
3. **Hook System**: Register hooks to run before or after commands.
4. **Asynchronous Execution**: Run commands asynchronously using the CommandPublisher.
5. **Logging**: Log messages, warnings, errors, and success messages.

### Class Hierarchy:

```
Application
├── CommandRegistrar
├── Commands
│   ├── BaseCmd (abstract)
│   ├── Category Commands
│   ├── Chunk Commands
│   ├── Config Commands
│   ├── Context Commands
│   ├── Extra Commands
│   ├── Package Commands
│   ├── Resource Commands
│   ├── Snippet Commands
│   ├── System Commands
│   ├── Template Commands
│   └── TV Commands
├── Configuration
│   ├── ConfigurationInterface
│   ├── Base
│   ├── Component
│   ├── ExcludedCommands
│   ├── Extension
│   ├── Instance
│   └── Yaml
│       └── YamlConfig
├── SSH
│   ├── ConnectionParser
│   ├── CommandProxy
│   └── Handler
├── Alias
│   └── Resolver
└── API
    ├── MODX_CLI
    ├── CommandRegistry
    ├── HookRegistry
    ├── CommandRunner
    ├── CommandPublisher
    └── ClosureCommand
```
