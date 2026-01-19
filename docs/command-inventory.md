# Command Inventory

Generated from source files in `src/Command/`.

## . Commands

### crawl
- **File**: `src/Command/Crawl.php`
- **Description**: Crawl resources to prime their caches
- **Arguments**:
  - `from` (required): The context key or resource ID to crawl from. Use "all" to process all web contexts.
- **Options**: none

### download
- **File**: `src/Command/Download.php`
- **Description**: Download a MODX Revolution release
- **Arguments**:
  - `version` (optional): The version you want to download
  - `path` (optional): The path to download the file to
- **Options**: none

### find
- **File**: `src/Command/Find.php`
- **Description**: Search within this MODX instance using the "uberbar" search
- **Arguments**:
  - `query` (required): The request to perform the search against
- **Options**: none

### install
- **File**: `src/Command/Install.php`
- **Description**: Install MODX here
- **Arguments**:
  - `source` (optional): Path to MODX source (unused while command is disabled)
  - `config` (optional): Path to configuration file (unused while command is disabled)
- **Options**: none

### run-sequence
- **File**: `src/Command/RunSequence.php`
- **Description**: Run a sequence of commands with various execution options
- **Arguments**: none
- **Options**: none

### version
- **File**: `src/Command/Version.php`
- **Description**: Display the CLI version
- **Arguments**: none
- **Options**: none

## Category Commands

### category:create
- **File**: `src/Command/Category/Create.php`
- **Description**: Create a MODX category
- **Arguments**:
  - `category` (required): The name of the category
- **Options**:
  - `--parent`  (VALUE_REQUIRED) (default: 0): The parent ID of the category
  - `--rank`  (VALUE_REQUIRED) (default: 0): The rank of the category

### category:get
- **File**: `src/Command/Category/Get.php`
- **Description**: Get a MODX category
- **Arguments**:
  - `id` (required): The ID of the category to get
- **Options**:
  - `--format` -f (VALUE_REQUIRED) (default: 'table'): Output format (table, json)

### category:list
- **File**: `src/Command/Category/GetList.php`
- **Description**: Get a list of categories in MODX
- **Arguments**: none
- **Options**: none

### category:remove
- **File**: `src/Command/Category/Remove.php`
- **Description**: Remove a MODX category
- **Arguments**:
  - `id` (required): The ID of the category to remove
- **Options**: none

### category:update
- **File**: `src/Command/Category/Update.php`
- **Description**: Update a MODX category
- **Arguments**:
  - `id` (required): The ID of the category to update
- **Options**: none

## Chunk Commands

### chunk:create
- **File**: `src/Command/Chunk/Create.php`
- **Description**: Create a MODX chunk
- **Arguments**:
  - `name` (required): The name of the chunk
- **Options**:
  - `--description`  (VALUE_REQUIRED) (default: ''): The description of the chunk
  - `--category`  (VALUE_REQUIRED) (default: 0): The category ID of the chunk
  - `--snippet`  (VALUE_REQUIRED) (default: ''): The content of the chunk
  - `--locked`  (VALUE_REQUIRED) (default: 0): Whether the chunk is locked (1 or 0)
  - `--static`  (VALUE_REQUIRED) (default: 0): Whether the chunk is static (1 or 0)
  - `--static_file`  (VALUE_REQUIRED) (default: ''): The static file path for the chunk

### chunk:get
- **File**: `src/Command/Chunk/Get.php`
- **Description**: Get a MODX chunk
- **Arguments**:
  - `id` (required): The ID of the chunk to get
- **Options**:
  - `--format` -f (VALUE_REQUIRED) (default: 'table'): Output format (table, json)

### chunk:list
- **File**: `src/Command/Chunk/GetList.php`
- **Description**: Get a list of chunks in MODX
- **Arguments**: none
- **Options**: none

### chunk:remove
- **File**: `src/Command/Chunk/Remove.php`
- **Description**: Remove a MODX chunk
- **Arguments**:
  - `id` (required): The ID of the chunk to remove
- **Options**: none

### chunk:update
- **File**: `src/Command/Chunk/Update.php`
- **Description**: Update a MODX chunk
- **Arguments**:
  - `id` (required): The ID of the chunk to update
- **Options**: none

## Config Commands

### config:add
- **File**: `src/Command/Config/Add.php`
- **Description**: Add a MODX instance to the configuration
- **Arguments**:
  - `name` (required): The name of the instance
- **Options**: none

### config:edit
- **File**: `src/Command/Config/Edit.php`
- **Description**: Edit a MODX instance in the configuration
- **Arguments**:
  - `name` (required): The name of the instance to edit
- **Options**: none

### config:exclude-command
- **File**: `src/Command/Config/ExcludeCommand.php`
- **Description**: Exclude a command from the available commands
- **Arguments**:
  - `class` (required): The command class to exclude
- **Options**: none

### config:get-default
- **File**: `src/Command/Config/GetDefault.php`
- **Description**: Get the default MODX instance
- **Arguments**: none
- **Options**: none

### config:get-exclude-command
- **File**: `src/Command/Config/GetExcludeCommand.php`
- **Description**: Get the list of excluded commands
- **Arguments**: none
- **Options**: none

### config:list
- **File**: `src/Command/Config/GetList.php`
- **Description**: List MODX instances in the configuration
- **Arguments**: none
- **Options**: none

### config:rename
- **File**: `src/Command/Config/Rename.php`
- **Description**: Rename a MODX instance in the configuration
- **Arguments**:
  - `old_name` (required): The current name of the instance
  - `new_name` (required): The new name of the instance
- **Options**: none

### config:rm
- **File**: `src/Command/Config/Rm.php`
- **Description**: Remove a MODX instance from the configuration
- **Arguments**:
  - `name` (required): The name of the instance to remove
- **Options**: none

### config:rm-default
- **File**: `src/Command/Config/RmDefault.php`
- **Description**: Remove the default MODX instance
- **Arguments**: none
- **Options**: none

### config:rm-exclude-command
- **File**: `src/Command/Config/RmExcludeCommand.php`
- **Description**: Remove a command from the excluded commands
- **Arguments**:
  - `class` (required): The command class to remove from the excluded commands
- **Options**: none

### config:set-default
- **File**: `src/Command/Config/SetDefault.php`
- **Description**: Set a MODX instance as the default
- **Arguments**:
  - `name` (required): The name of the instance to set as default
- **Options**: none

### config:wipe-exclude-command
- **File**: `src/Command/Config/WipeExcludeCommand.php`
- **Description**: Wipe all excluded commands
- **Arguments**: none
- **Options**: none

## Context Commands

### context:create
- **File**: `src/Command/Context/Create.php`
- **Description**: Create a MODX context
- **Arguments**:
  - `key` (required): The context key (unique identifier)
- **Options**:
  - `--name`  (VALUE_REQUIRED) (default: ''): The name of the context
  - `--description`  (VALUE_REQUIRED) (default: ''): The description of the context
  - `--rank`  (VALUE_REQUIRED) (default: 0): The rank/order of the context

### context:get
- **File**: `src/Command/Context/Get.php`
- **Description**: Get a MODX context by key
- **Arguments**:
  - `key` (required): The context key
- **Options**: none

### context:geturls
- **File**: `src/Command/Context/GetURLs.php`
- **Description**: Get a list of context URLs in MODX
- **Arguments**: none
- **Options**: none

### context:list
- **File**: `src/Command/Context/GetList.php`
- **Description**: Get a list of contexts in MODX
- **Arguments**: none
- **Options**: none

### context:permissions
- **File**: `src/Command/Context/Permissions/GetList.php`
- **Description**: List context access permissions for a context
- **Arguments**: none
- **Options**: none

### context:permissions:create
- **File**: `src/Command/Context/Permissions/Create.php`
- **Description**: Create a context access permission
- **Arguments**:
  - `context` (required): The context key
  - `usergroup` (required): The user group ID
  - `policy` (required): The access policy ID
- **Options**:
  - `--authority`  (VALUE_REQUIRED) (default: 0): The authority level

### context:permissions:remove
- **File**: `src/Command/Context/Permissions/Remove.php`
- **Description**: Remove a context access permission
- **Arguments**:
  - `context` (required): The context key
  - `id` (required): The access control entry ID
- **Options**: none

### context:permissions:update
- **File**: `src/Command/Context/Permissions/Update.php`
- **Description**: Update a context access permission
- **Arguments**:
  - `context` (required): The context key
  - `id` (required): The access control entry ID
- **Options**: none

### context:remove
- **File**: `src/Command/Context/Remove.php`
- **Description**: Remove a MODX context
- **Arguments**:
  - `key` (required): The context key
- **Options**: none

### context:setting:create
- **File**: `src/Command/Context/Setting/Create.php`
- **Description**: Create a context setting
- **Arguments**:
  - `context` (required): The context key
  - `key` (required): The setting key
- **Options**:
  - `--namespace`  (VALUE_REQUIRED) (default: 'core'): The setting namespace
  - `--xtype`  (VALUE_REQUIRED) (default: 'textfield'): The setting xtype

### context:setting:get
- **File**: `src/Command/Context/Setting/Get.php`
- **Description**: Get a context setting
- **Arguments**:
  - `context` (required): The context key
  - `key` (required): The setting key
- **Options**: none

### context:setting:list
- **File**: `src/Command/Context/Setting/GetList.php`
- **Description**: Get a list of context settings in MODX
- **Arguments**:
  - `context_key` (required): The context key
- **Options**: none

### context:setting:remove
- **File**: `src/Command/Context/Setting/Remove.php`
- **Description**: Remove a context setting
- **Arguments**:
  - `context` (required): The context key
  - `key` (required): The setting key
- **Options**: none

### context:setting:update
- **File**: `src/Command/Context/Setting/Update.php`
- **Description**: Update a context setting
- **Arguments**:
  - `context` (required): The context key
  - `key` (required): The setting key
- **Options**: none

### context:update
- **File**: `src/Command/Context/Update.php`
- **Description**: Update a MODX context
- **Arguments**:
  - `key` (required): The context key
- **Options**: none

## Extra Commands

### extra:add-component
- **File**: `src/Command/Extra/AddComponent.php`
- **Description**: Add a component to MODX
- **Arguments**:
  - `namespace` (required): The namespace of the component
- **Options**: none

### extra:components
- **File**: `src/Command/Extra/Components.php`
- **Description**: Get a list of components in MODX
- **Arguments**: none
- **Options**: none

### extra:list
- **File**: `src/Command/Extra/Extras.php`
- **Description**: Get a list of extras in MODX
- **Arguments**: none
- **Options**: none

### extra:remove-component
- **File**: `src/Command/Extra/RemoveComponent.php`
- **Description**: Remove a component from MODX
- **Arguments**:
  - `namespace` (required): The namespace of the component
- **Options**: none

## Menu Commands

### menu:list
- **File**: `src/Command/Menu/GetList.php`
- **Description**: Get a list of menus in MODX
- **Arguments**: none
- **Options**: none

## Misc Commands

### misc:list-columns
- **File**: `src/Command/Misc/ListColumns.php`
- **Description**: List columns in a database table in MODX
- **Arguments**:
  - `table` (required): The name of the table
- **Options**: none

## Ns Commands

### ns:create
- **File**: `src/Command/Ns/Create.php`
- **Description**: Create a namespace in MODX
- **Arguments**:
  - `name` (required): The name of the namespace
- **Options**: none

### ns:list
- **File**: `src/Command/Ns/GetList.php`
- **Description**: Get a list of namespaces in MODX
- **Arguments**: none
- **Options**: none

### ns:remove
- **File**: `src/Command/Ns/Remove.php`
- **Description**: Remove a namespace in MODX
- **Arguments**:
  - `name` (required): The name of the namespace to remove
- **Options**: none

### ns:update
- **File**: `src/Command/Ns/Update.php`
- **Description**: Update a namespace in MODX
- **Arguments**:
  - `name` (required): The name of the namespace to update
- **Options**: none

## Package Commands

### package:download
- **File**: `src/Command/Package/Download.php`
- **Description**: Download a package from the provider to MODX
- **Arguments**:
  - `signature` (required): The signature of the package to download
- **Options**: none

### package:install
- **File**: `src/Command/Package/Install.php`
- **Description**: Install a package in MODX
- **Arguments**:
  - `signature` (required): The signature of the package to install
- **Options**: none

### package:list
- **File**: `src/Command/Package/GetList.php`
- **Description**: Get a list of packages in MODX
- **Arguments**: none
- **Options**: none

### package:provider:add
- **File**: `src/Command/Package/Provider/Add.php`
- **Description**: Add a package provider in MODX
- **Arguments**:
  - `name` (required): The name of the provider
  - `service_url` (required): The service URL of the provider
- **Options**: none

### package:provider:categories
- **File**: `src/Command/Package/Provider/CategoriesList.php`
- **Description**: Get a list of categories from a provider in MODX
- **Arguments**:
  - `provider` (required): The ID of the provider
- **Options**: none

### package:provider:info
- **File**: `src/Command/Package/Provider/Info.php`
- **Description**: Get information about a package provider in MODX
- **Arguments**:
  - `id` (required): The ID of the provider
- **Options**:
  - `--format` -f (VALUE_REQUIRED) (default: 'table'): Output format (table, json)

### package:provider:list
- **File**: `src/Command/Package/Provider/GetList.php`
- **Description**: Get a list of package providers in MODX
- **Arguments**: none
- **Options**: none

### package:provider:packages
- **File**: `src/Command/Package/Provider/PackagesList.php`
- **Description**: Get a list of packages from a provider in MODX
- **Arguments**:
  - `provider` (required): The ID of the provider
- **Options**: none

### package:upgradeable
- **File**: `src/Command/Package/Upgradeable.php`
- **Description**: Get a list of upgradeable packages in MODX
- **Arguments**: none
- **Options**: none

## Plugin Commands

### plugin:disabled
- **File**: `src/Command/Plugin/DisabledPlugin.php`
- **Description**: Get a list of disabled plugins in MODX
- **Arguments**: none
- **Options**: none

### plugin:list
- **File**: `src/Command/Plugin/GetList.php`
- **Description**: Get a list of plugins in MODX
- **Arguments**: none
- **Options**: none

## Registry Commands

### registry:message:list
- **File**: `src/Command/Registry/Message/GetList.php`
- **Description**: Get a list of registry messages in MODX
- **Arguments**:
  - `topic` (required): The topic of the messages
- **Options**:
  - `--register`  (VALUE_REQUIRED) (default: 'db'): The register to use

### registry:queue:list
- **File**: `src/Command/Registry/Queue/GetList.php`
- **Description**: Get a list of registry queues in MODX
- **Arguments**: none
- **Options**:
  - `--register`  (VALUE_REQUIRED) (default: 'db'): The register to use

### registry:read
- **File**: `src/Command/Registry/Read.php`
- **Description**: Read messages from a MODX registry register
- **Arguments**:
  - `topic` (required): The topic to read from
- **Options**:
  - `--register`  (VALUE_REQUIRED) (default: 'db'): Registry name to use
  - `--format`  (VALUE_REQUIRED) (default: 'json'): Output format (json, html_log, raw)
  - `--poll_limit`  (VALUE_REQUIRED) (default: 1): Number of poll cycles
  - `--poll_interval`  (VALUE_REQUIRED) (default: 1): Interval between polls
  - `--time_limit`  (VALUE_REQUIRED) (default: 10): Time limit for polling
  - `--message_limit`  (VALUE_REQUIRED) (default: 200): Maximum messages to read

### registry:send
- **File**: `src/Command/Registry/Send.php`
- **Description**: Send a message to a MODX registry register
- **Arguments**:
  - `topic` (required): The topic to send to
  - `message` (required): The message to send
- **Options**:
  - `--register`  (VALUE_REQUIRED) (default: 'db'): Registry name to use
  - `--message_format`  (VALUE_REQUIRED) (default: 'string'): Message format (string, json)
  - `--delay`  (VALUE_REQUIRED) (default: 0): Delay in seconds
  - `--ttl`  (VALUE_REQUIRED) (default: 0): Time-to-live in seconds

### registry:topic:list
- **File**: `src/Command/Registry/Topic/GetList.php`
- **Description**: Get a list of registry topics in MODX
- **Arguments**: none
- **Options**:
  - `--register`  (VALUE_REQUIRED) (default: 'db'): The register to use

## Resource Commands

### resource:create
- **File**: `src/Command/Resource/Create.php`
- **Description**: Create a MODX resource
- **Arguments**:
  - `pagetitle` (required): The page title of the resource
- **Options**:
  - `--parent`  (VALUE_REQUIRED) (default: 0): The parent ID of the resource
  - `--template`  (VALUE_REQUIRED) (default: 0): The template ID of the resource
  - `--published`  (VALUE_REQUIRED) (default: 1): Whether the resource is published (1 or 0)
  - `--hidemenu`  (VALUE_REQUIRED) (default: 0): Whether the resource is hidden from the menu (1 or 0)
  - `--content`  (VALUE_REQUIRED) (default: ''): The content of the resource
  - `--alias`  (VALUE_REQUIRED) (default: ''): The alias of the resource
  - `--context_key`  (VALUE_REQUIRED) (default: 'web'): The context key of the resource

### resource:delete
- **File**: `src/Command/Resource/Delete.php`
- **Description**: Delete a MODX resource (move to trash)
- **Arguments**:
  - `id` (required): The ID of the resource to delete
- **Options**: none

### resource:erase
- **File**: `src/Command/Resource/Erase.php`
- **Description**: Erase a MODX resource (permanently delete from trash)
- **Arguments**:
  - `id` (required): The ID of the resource to erase
- **Options**: none

### resource:get
- **File**: `src/Command/Resource/Get.php`
- **Description**: Get a MODX resource
- **Arguments**:
  - `id` (required): The ID of the resource to get
- **Options**:
  - `--format` -f (VALUE_REQUIRED) (default: 'table'): Output format (table, json)

### resource:list
- **File**: `src/Command/Resource/GetList.php`
- **Description**: Get a list of resources
- **Arguments**: none
- **Options**: none

### resource:update
- **File**: `src/Command/Resource/Update.php`
- **Description**: Update a MODX resource
- **Arguments**:
  - `id` (required): The ID of the resource to update
- **Options**: none

## Security Commands

### security:access:flush
- **File**: `src/Command/Security/Access/FlushPermissions.php`
- **Description**: Flush permissions in MODX
- **Arguments**: none
- **Options**: none

### security:rolegroup:addpolicy
- **File**: `src/Command/Security/RoleGroup/AddPolicy.php`
- **Description**: Add a policy template to a role group
- **Arguments**:
  - `group` (required): The role group name or ID
  - `policy` (required): The policy template name or ID
- **Options**: none

### security:rolegroup:removepolicy
- **File**: `src/Command/Security/RoleGroup/RemovePolicy.php`
- **Description**: Remove a policy template from a role group
- **Arguments**:
  - `group` (required): The role group name or ID
  - `policy` (required): The policy template name or ID
- **Options**: none

## Session Commands

### session:flush
- **File**: `src/Command/Session/FlushSession.php`
- **Description**: Flush all sessions in MODX
- **Arguments**: none
- **Options**: none

### session:list
- **File**: `src/Command/Session/GetList.php`
- **Description**: Get a list of sessions in MODX
- **Arguments**: none
- **Options**: none

### session:remove
- **File**: `src/Command/Session/Remove.php`
- **Description**: Remove a session in MODX
- **Arguments**:
  - `id` (required): The ID (internalKey) of the session to remove
- **Options**: none

## Snippet Commands

### snippet:create
- **File**: `src/Command/Snippet/Create.php`
- **Description**: Create a MODX snippet
- **Arguments**:
  - `name` (required): The name of the snippet
- **Options**:
  - `--description`  (VALUE_REQUIRED) (default: ''): The description of the snippet
  - `--category`  (VALUE_REQUIRED) (default: 0): The category ID of the snippet
  - `--snippet`  (VALUE_REQUIRED) (default: ''): The PHP code of the snippet
  - `--locked`  (VALUE_REQUIRED) (default: 0): Whether the snippet is locked (1 or 0)
  - `--static`  (VALUE_REQUIRED) (default: 0): Whether the snippet is static (1 or 0)
  - `--static_file`  (VALUE_REQUIRED) (default: ''): The static file path for the snippet

### snippet:get
- **File**: `src/Command/Snippet/Get.php`
- **Description**: Get a MODX snippet
- **Arguments**:
  - `id` (required): The ID of the snippet to get
- **Options**:
  - `--format` -f (VALUE_REQUIRED) (default: 'table'): Output format (table, json)

### snippet:list
- **File**: `src/Command/Snippet/GetList.php`
- **Description**: Get a list of snippets in MODX
- **Arguments**: none
- **Options**: none

### snippet:remove
- **File**: `src/Command/Snippet/Remove.php`
- **Description**: Remove a MODX snippet
- **Arguments**:
  - `id` (required): The ID of the snippet to remove
- **Options**: none

### snippet:update
- **File**: `src/Command/Snippet/Update.php`
- **Description**: Update a MODX snippet
- **Arguments**:
  - `id` (required): The ID of the snippet to update
- **Options**: none

## Source Commands

### source:create
- **File**: `src/Command/Source/Create.php`
- **Description**: Create a MODX media source
- **Arguments**:
  - `name` (required): The name of the media source
- **Options**:
  - `--description`  (VALUE_REQUIRED) (default: ''): The description of the media source
  - `--class_key`  (VALUE_REQUIRED) (default: 'MODX\\Revolution\\Sources\\modFileMediaSource'): The class key of the media source
  - `--source-properties`  (VALUE_REQUIRED) (default: ''): The properties of the media source (JSON format)

### source:get
- **File**: `src/Command/Source/Get.php`
- **Description**: Get a MODX media source by ID
- **Arguments**:
  - `id` (required): The source ID
- **Options**: none

### source:list
- **File**: `src/Command/Source/GetList.php`
- **Description**: Get a list of media sources in MODX
- **Arguments**: none
- **Options**: none

### source:remove
- **File**: `src/Command/Source/Remove.php`
- **Description**: Remove a MODX media source
- **Arguments**:
  - `id` (required): The source ID
- **Options**: none

### source:update
- **File**: `src/Command/Source/Update.php`
- **Description**: Update a MODX media source
- **Arguments**:
  - `id` (required): The source ID
- **Options**: none

## System Commands

### system:clearcache
- **File**: `src/Command/System/ClearCache.php`
- **Description**: Clear the MODX cache
- **Arguments**: none
- **Options**: none

### system:event:create
- **File**: `src/Command/System/Events/Create.php`
- **Description**: Create a system event in MODX
- **Arguments**:
  - `name` (required): The name of the event
- **Options**:
  - `--service`  (VALUE_REQUIRED) (default: 1): The service of the event
  - `--groupname`  (VALUE_REQUIRED) (default: ''): The group name of the event

### system:event:delete
- **File**: `src/Command/System/Events/Delete.php`
- **Description**: Delete a system event in MODX
- **Arguments**:
  - `id` (required): The ID of the event
- **Options**: none

### system:info
- **File**: `src/Command/System/Info.php`
- **Description**: Get general system information
- **Arguments**: none
- **Options**: none

### system:locks:read
- **File**: `src/Command/System/Locks/Read.php`
- **Description**: Read a lock in MODX
- **Arguments**:
  - `key` (optional): The key of the lock
- **Options**:
  - `--format` -f (VALUE_REQUIRED) (default: 'table'): Output format (table, json)

### system:locks:remove
- **File**: `src/Command/System/Locks/Remove.php`
- **Description**: Remove a lock in MODX
- **Arguments**:
  - `key` (required): The key of the lock
- **Options**: none

### system:log:actions:list
- **File**: `src/Command/System/Log/Actions/GetList.php`
- **Description**: Get a list of action logs in MODX
- **Arguments**: none
- **Options**: none

### system:log:actions:truncate
- **File**: `src/Command/System/Log/Actions/Truncate.php`
- **Description**: Truncate action logs in MODX
- **Arguments**: none
- **Options**:
  - `--age`  (VALUE_REQUIRED) (default: 0): Truncate logs older than this many days

### system:log:clear
- **File**: `src/Command/System/Log/Clear.php`
- **Description**: Clear the MODX system log
- **Arguments**: none
- **Options**: none

### system:log:listen
- **File**: `src/Command/System/Log/Listen.php`
- **Description**: Listen to the MODX system log
- **Arguments**: none
- **Options**:
  - `--interval` -i (VALUE_REQUIRED) (default: 1): Interval in seconds between checks
  - `--limit` -l (VALUE_REQUIRED) (default: 10): Number of log entries to display initially

### system:log:view
- **File**: `src/Command/System/Log/View.php`
- **Description**: View the MODX system log
- **Arguments**: none
- **Options**:
  - `--format` -f (VALUE_REQUIRED) (default: 'table'): Output format (table, colored)

### system:refreshuris
- **File**: `src/Command/System/RefreshURIs.php`
- **Description**: Refresh URIs in MODX
- **Arguments**: none
- **Options**: none

### system:setting:list
- **File**: `src/Command/System/Setting/GetList.php`
- **Description**: Get a list of system settings in MODX
- **Arguments**: none
- **Options**: none

### system:snippet:list
- **File**: `src/Command/System/Snippet/GetList.php`
- **Description**: Get a list of snippets in MODX
- **Arguments**: none
- **Options**: none

## Tv Commands

### tv:create
- **File**: `src/Command/TV/Create.php`
- **Description**: Create a MODX template variable
- **Arguments**:
  - `name` (required): The name of the template variable
- **Options**:
  - `--caption`  (VALUE_REQUIRED) (default: ''): The caption of the template variable
  - `--description`  (VALUE_REQUIRED) (default: ''): The description of the template variable
  - `--category`  (VALUE_REQUIRED) (default: 0): The category ID of the template variable
  - `--type`  (VALUE_REQUIRED) (default: 'text'): The input type of the template variable (text, textarea, richtext, etc.)
  - `--default_text`  (VALUE_REQUIRED) (default: ''): The default value of the template variable
  - `--elements`  (VALUE_REQUIRED) (default: ''): The possible values for the template variable (for select, radio, etc.)
  - `--rank`  (VALUE_REQUIRED) (default: 0): The rank of the template variable
  - `--display`  (VALUE_REQUIRED) (default: 'default'): The display type of the template variable
  - `--templates`  (VALUE_REQUIRED) (default: ''): Comma-separated list of template IDs to associate with the template variable
  - `--locked`  (VALUE_REQUIRED) (default: 0): Whether the template variable is locked (1 or 0)
  - `--static`  (VALUE_REQUIRED) (default: 0): Whether the template variable is static (1 or 0)
  - `--static_file`  (VALUE_REQUIRED) (default: ''): The static file path for the template variable

### tv:get
- **File**: `src/Command/TV/Get.php`
- **Description**: Get a MODX template variable
- **Arguments**:
  - `id` (required): The ID of the template variable to get
- **Options**:
  - `--format` -f (VALUE_REQUIRED) (default: 'table'): Output format (table, json)

### tv:list
- **File**: `src/Command/TV/GetList.php`
- **Description**: Get a list of template variables in MODX
- **Arguments**: none
- **Options**: none

### tv:remove
- **File**: `src/Command/TV/Remove.php`
- **Description**: Remove a MODX template variable
- **Arguments**:
  - `id` (required): The ID of the template variable to remove
- **Options**: none

### tv:update
- **File**: `src/Command/TV/Update.php`
- **Description**: Update a MODX template variable
- **Arguments**:
  - `id` (required): The ID of the template variable to update
- **Options**: none

## Template Commands

### template:create
- **File**: `src/Command/Template/Create.php`
- **Description**: Create a MODX template
- **Arguments**:
  - `templatename` (required): The name of the template
- **Options**:
  - `--description`  (VALUE_REQUIRED) (default: ''): The description of the template
  - `--category`  (VALUE_REQUIRED) (default: 0): The category ID of the template
  - `--content`  (VALUE_REQUIRED) (default: ''): The HTML content of the template
  - `--locked`  (VALUE_REQUIRED) (default: 0): Whether the template is locked (1 or 0)
  - `--static`  (VALUE_REQUIRED) (default: 0): Whether the template is static (1 or 0)
  - `--static_file`  (VALUE_REQUIRED) (default: ''): The static file path for the template
  - `--icon`  (VALUE_REQUIRED) (default: ''): The icon for the template

### template:get
- **File**: `src/Command/Template/Get.php`
- **Description**: Get a MODX template
- **Arguments**:
  - `id` (required): The ID of the template to get
- **Options**:
  - `--format` -f (VALUE_REQUIRED) (default: 'table'): Output format (table, json)

### template:list
- **File**: `src/Command/Template/GetList.php`
- **Description**: Get a list of templates in MODX
- **Arguments**: none
- **Options**: none

### template:remove
- **File**: `src/Command/Template/Remove.php`
- **Description**: Remove a MODX template
- **Arguments**:
  - `id` (required): The ID of the template to remove
- **Options**: none

### template:update
- **File**: `src/Command/Template/Update.php`
- **Description**: Update a MODX template
- **Arguments**:
  - `id` (required): The ID of the template to update
- **Options**: none

## User Commands

### user:list
- **File**: `src/Command/User/GetList.php`
- **Description**: Get a list of users in MODX
- **Arguments**: none
- **Options**:
  - `--active` (VALUE_REQUIRED): Filter by active status (1 or 0)
  - `--blocked` (VALUE_REQUIRED): Filter by blocked status (1 or 0)
  - `--usergroup` (VALUE_REQUIRED): Filter by user group ID
  - `--query` (VALUE_REQUIRED): Search query
  - `--limit` -l (VALUE_REQUIRED) (default: 10): Number of items to return
  - `--start` (VALUE_REQUIRED) (default: 0): Starting index for pagination

### user:get
- **File**: `src/Command/User/Get.php`
- **Description**: Get detailed information about a MODX user
- **Arguments**:
  - `identifier` (required): The user ID or username
- **Options**: none

### user:create
- **File**: `src/Command/User/Create.php`
- **Description**: Create a MODX user
- **Arguments**:
  - `username` (required): The username for the new user
- **Options**:
  - `--email` (VALUE_REQUIRED): The email address for the user (required)
  - `--password` (VALUE_REQUIRED): The password for the user (will be generated if not provided)
  - `--fullname` (VALUE_REQUIRED) (default: ''): The full name of the user
  - `--active` (VALUE_REQUIRED) (default: 1): Active status (1 or 0)
  - `--blocked` (VALUE_REQUIRED) (default: 0): Blocked status (1 or 0)

### user:update
- **File**: `src/Command/User/Update.php`
- **Description**: Update a MODX user
- **Arguments**:
  - `identifier` (required): The user ID or username
- **Options**:
  - `--username` (VALUE_REQUIRED): The new username
  - `--email` (VALUE_REQUIRED): The new email address
  - `--fullname` (VALUE_REQUIRED): The full name
  - `--active` (VALUE_REQUIRED): Active status (1 or 0)
  - `--blocked` (VALUE_REQUIRED): Blocked status (1 or 0)

### user:remove
- **File**: `src/Command/User/Remove.php`
- **Description**: Remove a MODX user
- **Arguments**:
  - `identifier` (required): The user ID or username
- **Options**:
  - `--force` -f (VALUE_NONE): Force removal without confirmation

### user:activate
- **File**: `src/Command/User/Activate.php`
- **Description**: Activate a MODX user
- **Arguments**:
  - `identifier` (required): The user ID or username
- **Options**: none

### user:deactivate
- **File**: `src/Command/User/Deactivate.php`
- **Description**: Deactivate a MODX user
- **Arguments**:
  - `identifier` (required): The user ID or username
- **Options**: none

### user:block
- **File**: `src/Command/User/Block.php`
- **Description**: Block a MODX user
- **Arguments**:
  - `identifier` (required): The user ID or username
- **Options**: none

### user:unblock
- **File**: `src/Command/User/Unblock.php`
- **Description**: Unblock a MODX user
- **Arguments**:
  - `identifier` (required): The user ID or username
- **Options**: none

### user:resetpassword
- **File**: `src/Command/User/ResetPassword.php`
- **Description**: Reset a user's password in MODX
- **Arguments**:
  - `id` (required): The ID of the user
- **Options**:
  - `--password` (VALUE_REQUIRED): The new password
  - `--generate` -g (VALUE_NONE): Generate a random password
