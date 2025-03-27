# Active Context
## Current work focus

- Get all existing commands working properly
- Add a --json option to all commands that return data so that they return the data in JSON format.
- Add --ssh functionality like the WP-CLI
- Add an internal API like WP-CLI
- self update
- TAB completions
- context is missing all CRUD commands
- source is missing all CRUD commands
- Add a ascii art version of the modx logo similar to that shown when running Composer 

## Recent changes
- Corrected duplicate properties declarations in Command/Snippet/Create.php and Command/Snippet/Update.php
- Added missing CRUD functionality to Category, Chunk, Snippet, Template and TV
- Corrected namespacing and reference issues in tests/

## Next steps

### Fix Broken Commands
- crawl: raises an error
- ns:getlist does not return a list of namespaces
- ns:create does not create a namespace
- ns:update cannot be tested unteil ns:getlist and ns:create are fixed
- ns:remove cannot be tested until ns:getlist and ns:create are fixed
- extra:list does not show version numbers (package:getlist does display version numnbers).
- tv:update --description test description 6 raises an error: too many arguments to"tv:update", expected argumentd "id"
- tv:update --description "test description" 6 raises an error: name  : tv_err ns_name


## Active decisions and considerations

- TODO