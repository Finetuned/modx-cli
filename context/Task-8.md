# Task 8: Fix Version Display in `extra:list` Command

## Prompt
`bin/modx extra:list` does not return the version of each item whereas `bin/modx package:list` returns the version numbers. If it is not possible to directly get the version number from the extra, can it be obtained from the corresponding package?

## Plan
1. Analyze how `extra:list` and `package:list` commands retrieve and display version information
2. Identify why `extra:list` is not showing version numbers correctly
3. Determine if and how package version information can be used for extras
4. Implement a solution to display version numbers in `extra:list` output
5. Test the solution to ensure it works correctly
6. Update the memory bank to document these changes

## Actions Taken

1. Analyzed the implementation of both commands:
   - `package:list` (src/Command/Package/GetList.php) uses a MODX processor 'workspace/packages/getlist' which directly returns version information
   - `extra:list` (src/Command/Extra/Extras.php) was trying to match packages to namespaces by name, which wasn't always accurate

2. Identified the issue:
   - The original implementation in `Extras.php` was using a simple query to find packages by name:
     ```php
     $package = $this->modx->getObject('transport.modTransportPackage', array(
         'package_name' => $name
     ));
     ```
   - This assumes the package name exactly matches the namespace name, which isn't always the case

3. Implemented a more robust solution:
   - Added a method to get all packages using the same processor as `package:list` to create a lookup table
   - Created a method to find the correct package for a namespace using multiple matching strategies
   - Added fallback mechanisms for when the processor fails or no match is found

4. The key improvements in the new implementation:
   - Uses the same data source as `package:list` to ensure consistency
   - Tries multiple matching strategies:
     - Direct name matching
     - Partial name matching in both directions
     - Fallback to the original database query method
   - Creates a comprehensive lookup table with multiple entries for each package (name, lowercase name, signature without version)
   - Includes error handling and fallback mechanisms

5. Tested the solution to ensure it correctly displays version numbers for extras

6. Updated the memory bank to document these changes

## Summary

Successfully fixed the issue with `extra:list` not showing version numbers by implementing a more robust approach to matching extras (namespaces) with their corresponding packages. The new implementation uses the same data source as `package:list` and employs multiple matching strategies to ensure version information is displayed whenever possible.

This fix resolves one of the known issues listed in the progress.md file and improves the consistency between the `extra:list` and `package:list` commands.
