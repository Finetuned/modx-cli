# List Commands Documentation

This document describes the pagination and filtering capabilities of list commands in MODX CLI.

## Overview

All list commands in MODX CLI support pagination and filtering to help you navigate through large datasets efficiently. This is particularly useful when working with sites that have many resources, packages, or other MODX objects.

## Pagination Options

All list commands support the following pagination options:

- `--limit` (or `-l`) - Number of items to return (default: 10)
- `--start` - Starting index for pagination (default: 0)

Note: The `--start` option does not have a shortcut to avoid conflicts with the global `--ssh` option.

## Available List Commands

- `resource:list` - List MODX resources
- `chunk:list` - List MODX chunks
- `template:list` - List MODX templates
- `snippet:list` - List MODX snippets
- `tv:list` - List MODX template variables
- `category:list` - List MODX categories
- `user:list` - List MODX users
- `package:list` - List installed packages
- `ns:list` - List MODX namespaces
- `session:list` - List active sessions
- And many more...

## Basic Usage

### Default Listing

Get the first 10 items (default behavior):
```bash
modx resource:list
```

### Custom Page Size

Get the first 20 items:
```bash
modx resource:list --limit=20
```

### Pagination

Get items 11-20 (second page with 10 items per page):
```bash
modx resource:list --limit=10 --start=10
```

Get items 21-40 (third page with 20 items per page):
```bash
modx resource:list --limit=20 --start=20
```

## Practical Examples

### Package Management

List all packages in chunks of 5:
```bash
# First 5 packages
modx package:list --limit=5 --start=0

# Next 5 packages  
modx package:list --limit=5 --start=5

# Next 5 packages
modx package:list --limit=5 --start=10
```

### Resource Navigation

Navigate through resources in a large site:
```bash
# First page (1-25)
modx resource:list --limit=25 --start=0

# Second page (26-50)
modx resource:list --limit=25 --start=25

# Third page (51-75)
modx resource:list --limit=25 --start=50
```

### Template Variables

Browse through many template variables:
```bash
# Show 15 TVs starting from the 30th
modx tv:list --limit=15 --start=30
```

## Combining with Filters

Many list commands support additional filtering options that can be combined with pagination:

### Resource Filtering with Pagination

```bash
# Get published resources in chunks of 10
modx resource:list --published=1 --limit=10 --start=0

# Get resources from a specific parent with pagination
modx resource:list --parent=5 --limit=20 --start=0
```

### User Filtering with Pagination

```bash
# Get active users in pages of 15
modx user:list --active=1 --limit=15 --start=0
```

## JSON Output with Pagination

All list commands support JSON output, which includes pagination metadata:

```bash
modx resource:list --limit=5 --start=10 --json
```

Example JSON output:
```json
{
  "total": 150,
  "results": [
    {
      "id": 11,
      "pagetitle": "Page 11",
      "published": 1
    },
    // ... 4 more results
  ]
}
```

The JSON output includes:
- `total` - Total number of items available
- `results` - Array of items for the current page

## Pagination Information

When using table output (default), pagination information is displayed at the bottom:

```
┌─────────────────────┬──────────┐
│ displaying 10 item(s) │ of 150   │
│                     │          │
└─────────────────────┴──────────┘
```

This shows:
- How many items are displayed in the current view
- Total number of items available

## Performance Considerations

### Large Datasets

For large datasets, use reasonable page sizes:
- Small pages (5-10 items) for quick browsing
- Medium pages (20-50 items) for general use
- Large pages (100+ items) only when necessary

### Memory Usage

Larger page sizes use more memory. If you encounter memory issues, reduce the `--limit` value.

## Scripting with Pagination

### Bash Script Example

Process all resources in batches:

```bash
#!/bin/bash
LIMIT=20
START=0
TOTAL=0

# Get total count first
RESULT=$(modx resource:list --limit=1 --json)
TOTAL=$(echo $RESULT | jq '.total')

echo "Processing $TOTAL resources in batches of $LIMIT"

while [ $START -lt $TOTAL ]; do
    echo "Processing batch starting at $START"
    modx resource:list --limit=$LIMIT --start=$START --json | jq '.results[]'
    START=$((START + LIMIT))
done
```

### PHP Script Example

```php
<?php
$limit = 25;
$start = 0;
$total = 0;

// Get total count
$result = json_decode(shell_exec("modx resource:list --limit=1 --json"), true);
$total = $result['total'];

echo "Processing {$total} resources in batches of {$limit}\n";

while ($start < $total) {
    echo "Processing batch starting at {$start}\n";
    $cmd = "modx resource:list --limit={$limit} --start={$start} --json";
    $result = json_decode(shell_exec($cmd), true);
    
    foreach ($result['results'] as $resource) {
        // Process each resource
        echo "Processing resource: {$resource['pagetitle']}\n";
    }
    
    $start += $limit;
}
?>
```

## Tips and Best Practices

1. **Start Small**: Begin with small page sizes to get familiar with the data structure
2. **Use JSON for Scripting**: JSON output is easier to parse programmatically
3. **Check Total Count**: Use the total count to calculate how many pages you need
4. **Combine Filters**: Use filtering options to reduce the dataset before pagination
5. **Monitor Performance**: Large page sizes may impact performance on slower systems

## Troubleshooting

### No Results Returned

If a list command returns no results:
- Check if the start index is beyond the total count
- Verify any filters you're using are correct
- Try without pagination first to see if data exists

### Performance Issues

If list commands are slow:
- Reduce the `--limit` value
- Add more specific filters to reduce the dataset
- Check your MODX installation's performance

## Related Documentation

- [Update Commands](update-commands.md) - Information about enhanced update functionality
- [SSH and Aliases](ssh-and-aliases.md) - Running commands on remote servers
- [Internal API](internal-api.md) - Programmatic usage of the CLI
