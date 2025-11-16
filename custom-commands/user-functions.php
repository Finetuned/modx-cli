<?php

/**
 * Custom user utility functions for MODX CLI
 */

/**
 * Reset user password by username, email, or ID
 * 
 * This custom command wraps the built-in user:resetpassword command
 * and adds convenience by allowing username/email lookup
 *
 * @param array $args Positional arguments
 * @param array $assoc_args Named arguments (options)
 * @return int Exit code (0 = success, 1 = error)
 */
function userResetPassword($args, $assoc_args)
{
    // Get MODX instance
    $app = new \MODX\CLI\Application();
    $modx = $app->getMODX();
    
    if (!$modx) {
        MODX_CLI::error('MODX instance not available');
        return 1;
    }
    
    // Extract identifier options
    $username = isset($assoc_args['username']) ? $assoc_args['username'] : null;
    $email = isset($assoc_args['email']) ? $assoc_args['email'] : null;
    $id = isset($assoc_args['id']) ? $assoc_args['id'] : null;
    
    // Validate exactly one identifier provided
    $identifiers = array_filter([$username, $email, $id], function($val) {
        return $val !== null && $val !== '';
    });
    
    if (count($identifiers) === 0) {
        MODX_CLI::error('Please specify one of: --username, --email, or --id');
        MODX_CLI::log('Example: user:reset-password --username admin --generate');
        return 1;
    }
    
    if (count($identifiers) > 1) {
        MODX_CLI::error('Please specify only ONE of: --username, --email, or --id');
        return 1;
    }
    
    // Look up user by the provided identifier
    $user = null;
    $identifierType = '';
    
    if ($username !== null) {
        $user = $modx->getObject('modUser', ['username' => $username]);
        $identifierType = "username '{$username}'";
    } elseif ($email !== null) {
        // Look up user by email through Profile relationship
        $profile = $modx->getObject('modUserProfile', ['email' => $email]);
        if ($profile) {
            $user = $modx->getObject('modUser', $profile->get('internalKey'));
        }
        $identifierType = "email '{$email}'";
    } else {
        $user = $modx->getObject('modUser', $id);
        $identifierType = "ID '{$id}'";
    }
    
    if (!$user) {
        MODX_CLI::error("User with {$identifierType} not found");
        return 1;
    }
    
    $userId = $user->get('id');
    $userName = $user->get('username');
    
    MODX_CLI::log("Found user: {$userName} (ID: {$userId})");
    
    // Build command arguments for built-in user:resetpassword
    $cmd_args = [$userId];
    $cmd_options = [];
    
    // Pass through password options
    if (isset($assoc_args['password'])) {
        $cmd_options[] = '--password=' . escapeshellarg($assoc_args['password']);
    }
    
    if (isset($assoc_args['generate'])) {
        $cmd_options[] = '--generate';
    }
    
    // Build and execute the built-in command
    $cmd = 'user:resetpassword ' . $userId;
    if (!empty($cmd_options)) {
        $cmd .= ' ' . implode(' ', $cmd_options);
    }
    
    MODX_CLI::log("Executing: {$cmd}");
    
    // Run the built-in command
    return MODX_CLI::runcommand($cmd);
}
