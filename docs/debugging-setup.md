# MODX CLI Debugging Setup

This document explains how to use the dynamic VS Code debugging solution for MODX CLI commands.

## Overview

The debugging setup provides a dynamic way to debug MODX CLI commands interactively, similar to web server debugging. Instead of hardcoded arguments in VS Code launch configurations, you can enter commands dynamically during the debugging session.

## Files

- `debug_interactive.php` - Interactive debug wrapper that prompts for commands
- `.vscode/launch.json` - VS Code debugging configurations

## Debugging Configurations

### 🐛 Interactive MODX CLI Debugger (Recommended)

**Usage:**
1. Set breakpoints in your MODX CLI code
2. Start debugging with "🐛 Interactive MODX CLI Debugger" configuration
3. Enter the command you want to debug when prompted
4. Step through the code with VS Code debugger

**Features:**
- Interactive command input
- Supports all MODX CLI commands
- Proper argument parsing with quote handling
- Debug information display
- Uses integrated terminal for better interaction

### 🚀 Quick Debug with Environment Variable

**Usage:**
1. Set the `MODX_DEBUG_COMMAND` environment variable (default: `package:list`)
2. Start debugging with "🚀 Quick Debug with Environment Variable" configuration
3. The predefined command runs automatically

**To change the command:**
Edit the `MODX_DEBUG_COMMAND` value in `.vscode/launch.json`:
```json
"env": {
    "MODX_DEBUG_COMMAND": "your-command-here --with-args"
}
```

## Input Modes

The debug wrapper supports three input modes:

### 1. Interactive Mode (Default)
```bash
Enter MODX CLI command: package:list-remote
```

### 2. Environment Variable Mode
```bash
export MODX_DEBUG_COMMAND="package:upgradeable"
php debug_interactive.php
```

### 3. Direct Arguments Mode
```bash
php debug_interactive.php package:list --limit=10
```

## Example Commands to Debug

```bash
# Package management
package:list
package:upgradeable
package:list-remote
package:install FormIt

# Template Variables
tv:create --name=testTV --type=text --caption="Test TV"
tv:update --name=testTV --description="Updated description" 1
tv:list --category=Content

# Resources
resource:create --pagetitle="Test Page" --content="Hello World"
resource:list --parent=0 --limit=5
resource:get 1

# Templates
template:list
template:get 1
template:create --name="TestTemplate" --content="<html>[[*content]]</html>"

# Chunks and Snippets
chunk:list
snippet:list
```

## Debug Information Display

The wrapper shows helpful debug information:
- Command being executed
- Argument count
- Working directory
- PHP version
- Xdebug status
- Debugger connection status

## Troubleshooting

### Xdebug Not Working
1. Ensure Xdebug is installed: `php -m | grep xdebug`
2. Check Xdebug configuration in `php.ini`:
   ```ini
   zend_extension=xdebug
   xdebug.mode=debug
   xdebug.start_with_request=yes
   xdebug.client_host=127.0.0.1
   xdebug.client_port=9003
   ```
3. Restart VS Code and try again

### Commands Not Found
- Ensure you're in the correct MODX installation directory
- Check that the MODX CLI is properly configured
- Verify MODX database connection

### Breakpoints Not Hit
1. Ensure breakpoints are set in the correct files
2. Check that the debugger is listening (VS Code should show "Listening for Xdebug")
3. Verify the command path leads to your breakpoint code

## Legacy Configurations

The launch.json also includes legacy debugging configurations for backward compatibility:
- "Debug MODX via Wrapper (Legacy)" - Uses the old debug_wrapper.php
- "Listen for Xdebug (CLI)" - Direct debugging of bin/modx

These are kept for reference but the interactive debugger is recommended for new debugging sessions.

## Benefits

1. **Dynamic Command Input** - No need to edit launch.json for different commands
2. **Interactive Workflow** - Similar to web debugging experience
3. **Comprehensive Support** - Works with all MODX CLI commands
4. **Better Debugging Experience** - Integrated terminal and proper argument handling
5. **Debug Information** - Shows environment details for troubleshooting
