# Task 7: Standardize Command Naming Convention

## Prompt
Some commands use `:list` and other use `:getlist`. Update all commands using getlist to use list. Ensure to update all tests and output from calling modx list.

## Plan
1. Search for all occurrences of "getlist" in the codebase to identify which files need to be updated
2. Search for all occurrences of ":list" to see which commands are already using the new naming convention
3. Check if there are any tests that use "getlist" in their names or content
4. Update all command classes that use `:getlist` to use `:list` instead
5. Update any comments that reference `:getlist` to use `:list` for consistency
6. Update tests to check for the new command names
7. Run tests to ensure everything is working correctly
8. Run `modx list` to verify the updated command names in the output
9. Update the memory bank to document these changes

## Actions Taken

1. Identified 22 command classes that needed to be updated:
   - src/Command/Category/GetList.php
   - src/Command/Chunk/GetList.php
   - src/Command/Context/GetList.php
   - src/Command/Context/Setting/GetList.php
   - src/Command/Menu/GetList.php
   - src/Command/Ns/GetList.php
   - src/Command/Package/GetList.php
   - src/Command/Package/Provider/GetList.php
   - src/Command/Plugin/GetList.php
   - src/Command/Registry/Message/GetList.php
   - src/Command/Registry/Queue/GetList.php
   - src/Command/Registry/Topic/GetList.php
   - src/Command/Resource/GetList.php
   - src/Command/Session/GetList.php
   - src/Command/Snippet/GetList.php
   - src/Command/Source/GetList.php
   - src/Command/System/Log/Actions/GetList.php
   - src/Command/System/Setting/GetList.php
   - src/Command/System/Snippet/GetList.php
   - src/Command/Template/GetList.php
   - src/Command/TV/GetList.php
   - src/Command/User/GetList.php

2. Updated the `$name` property in each command class from `:getlist` to `:list`

3. Updated the comment in `src/Command/ListProcessor.php` to reflect the new naming convention:
   ```php
   /**
    * Command to deal with list processors (ie. list)
    */
   ```

4. Updated the test in `tests/ApplicationTest.php` to check for `resource:list` instead of `resource:getlist`:
   ```php
   $this->assertArrayHasKey('resource:list', $commands);
   ```

5. Ran the tests to ensure everything was working correctly:
   ```
   OK (3 tests 11 assertions)
   ```

6. Ran `modx list` to verify the updated command names in the output

7. Updated the memory bank:
   - Added the standardization to the "Recent changes" section in `activeContext.md`
   - Marked the task as completed in the "What's left to build" section in `progress.md`
   - Updated command references in the "Command Issues" section to use the new naming convention

## Summary

Successfully standardized the command naming convention by updating all commands that were using `:getlist` to use `:list` instead. This makes the CLI more intuitive and consistent, as all list-type commands now follow the same naming pattern. All tests are passing, and the `modx list` command shows the updated command names.

The memory bank has been updated to document these changes, including marking the task as completed in the progress tracking.
