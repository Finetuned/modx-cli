# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2025-01-03

### Added
- Initial release of the MODX 3 CLI
- Support for MODX 3.0.0 and higher
- Command system based on Symfony Console
- Configuration system for managing MODX instances
- Basic commands:
  - `version` - Display the CLI version
  - `system:info` - Get general system information
  - `system:clearcache` - Clear the MODX cache
  - `resource:getlist` - Get a list of resources
- Bash completion script
- Support for building a PHAR file
