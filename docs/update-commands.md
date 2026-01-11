# Update Commands Documentation

This document describes the enhanced update functionality in MODX CLI that allows for partial updates without requiring all fields to be specified.

## Overview

All update commands in MODX CLI have been enhanced to support partial updates. This means you only need to specify the ID of the object you want to update and the fields you want to change. The CLI automatically fetches the existing object data to populate any required fields that aren't specified.

## Enhanced Update Commands

The following update commands have been improved:

- `category:update` - Update MODX categories
- `chunk:update` - Update MODX chunks
- `context:update` - Update MODX contexts
- `context:setting:update` - Update context settings
- `context:permissions:update` - Update context permissions
- `ns:update` - Update MODX namespaces
- `resource:update` - Update MODX resources
- `snippet:update` - Update MODX snippets
- `source:update` - Update media sources
- `template:update` - Update MODX templates  
- `tv:update` - Update MODX template variables

## How It Works

### Before the Enhancement

Previously, update commands required you to specify all required fields, even if you only wanted to change one field:

```bash
# This would fail because --name was required
modx chunk:update 5 --description="New description"
```

### After the Enhancement

Now you can update any field without specifying all required fields:

```bash
# This now works - the CLI fetches existing data automatically
modx chunk:update 5 --description="New description"
```

## Technical Implementation

The enhancement works by:

1. **Pre-populating existing data**: Before running the processor, the CLI fetches the existing object and pre-populates all its current values
2. **Selective overrides**: Only the fields you specify in the command are overridden
3. **Type conversion**: Boolean and integer fields are properly converted
4. **Error handling**: Clear error messages if the object doesn't exist
5. **Critical field validation**: For resources, ensures essential fields like `class_key`, `context_key`, and `content_type` are always properly set
6. **Safety defaults**: Automatically applies safe defaults for missing critical fields (e.g., `class_key` defaults to 'modDocument')

## Examples

### Chunk Updates

Update only the description:
```bash
modx chunk:update 5 --description="Updated description"
```

Update multiple fields:
```bash
modx chunk:update 5 --description="New description" --snippet="<p>New content</p>" --category=2
```

Update boolean fields:
```bash
modx chunk:update 5 --locked=1 --static=0
```

### Template Updates

Update only the template name:
```bash
modx template:update 3 --templatename="New Template Name"
```

Update content and category:
```bash
modx template:update 3 --content="<html>New content</html>" --category=1
```

### Snippet Updates

Update snippet code:
```bash
modx snippet:update 7 --snippet="<?php return 'Hello World';"
```

Update name and description:
```bash
modx snippet:update 7 --name="MySnippet" --description="Updated snippet"
```

### Template Variable Updates

Update default value:
```bash
modx tv:update 10 --default_text="New default"
```

Update multiple properties:
```bash
modx tv:update 10 --caption="New Caption" --description="Updated TV" --type="textarea"
```

Update template associations (comma-separated):
```bash
modx tv:update 10 --templates="1,2,3"
```

### Resource Updates

Update page title:
```bash
modx resource:update 123 --pagetitle="New Page Title"
```

Update multiple fields:
```bash
modx resource:update 123 --pagetitle="New Title" --content="New content" --published=1
```

Change parent and template:
```bash
modx resource:update 123 --parent=5 --template=2
```

Update alias (this was previously failing with null classKey error):
```bash
modx resource:update 6 --alias="about-us"
```

### Context Updates

Update context description:
```bash
modx context:update web --description="Public site context"
```

### Media Source Updates

Update source name:
```bash
modx source:update 2 --name="Uploads Source"
```

#### Resource-Specific Enhancements

Resource updates have been enhanced with special handling for critical MODX processor requirements:

- **Automatic class_key handling**: Ensures `class_key` is always set (defaults to 'modDocument')
- **Context validation**: Ensures `context_key` is always set (defaults to 'web')
- **Content type handling**: Ensures `content_type` is always set (defaults to 1)
- **Comprehensive field mapping**: Pre-populates all essential resource fields including alias, content, hidemenu, searchable, and cacheable

This resolves previous issues where resource updates would fail with "null classKey" errors when updating specific fields.

## Boolean Field Handling

Boolean fields (like `published`, `locked`, `static`, etc.) accept various formats:

- `1` or `0`
- `true` or `false`  
- `yes` or `no`
- `on` or `off`

Examples:
```bash
modx resource:update 123 --published=true
modx resource:update 123 --published=1
modx resource:update 123 --published=yes
```

## Error Handling

If an object doesn't exist, you'll get a clear error message:

```bash
modx chunk:update 999 --description="test"
# Output: Chunk with ID 999 not found
```

## JSON Output

All update commands support JSON output for scripting:

```bash
modx chunk:update 5 --description="New description" --json
```

## Backward Compatibility

These enhancements are fully backward compatible. Existing scripts that specify all fields will continue to work exactly as before.

## Benefits

1. **Improved usability**: No need to remember and specify all required fields
2. **Reduced errors**: Less chance of accidentally overwriting fields with empty values
3. **Better workflow**: More intuitive command-line experience
4. **Consistent behavior**: All update commands work the same way
5. **Clear feedback**: Better error messages when operations fail

## Related Commands

- See [List Commands](list-commands.md) for information about pagination support
- See [SSH and Aliases](ssh-and-aliases.md) for remote command execution
- See [Internal API](internal-api.md) for programmatic usage
