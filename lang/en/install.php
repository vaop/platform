<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Installer Language Lines
    |--------------------------------------------------------------------------
    */

    // Layout
    'title' => 'Install VAOP',
    'header' => 'VAOP Installer',
    'footer' => 'VAOP Platform',

    // Steps
    'steps' => [
        'welcome' => 'Welcome',
        'requirements' => 'Requirements',
        'database' => 'Database',
        'environment' => 'Environment',
        'admin' => 'Admin',
        'finalize' => 'Finalize',
    ],

    // Navigation
    'back' => 'Back',
    'continue' => 'Continue',
    'get_started' => 'Get Started',

    // Welcome
    'welcome' => [
        'title' => 'Welcome to VAOP',
        'description' => 'Thank you for choosing VAOP for your virtual airline. This wizard will guide you through the installation process.',
        'before_begin' => 'Before you begin',
        'checklist' => [
            'database' => 'Make sure you have created a database for VAOP',
            'credentials' => 'Have your database credentials ready',
            'php_version' => 'Ensure PHP 8.4 or higher is installed',
        ],
    ],

    // Requirements
    'requirements' => [
        'title' => 'System Requirements',
        'description' => 'Please ensure your server meets the following requirements before continuing.',
        'php_version' => 'PHP Version',
        'php_required' => 'PHP :version or higher',
        'current_version' => 'Current: :version',
        'extensions' => 'PHP Extensions',
        'directories' => 'Directory Permissions',
        'writable' => 'Writable',
        'not_writable' => 'Not writable',
        'missing' => 'Missing',
        'fix_issues' => 'Fix Issues to Continue',
    ],

    // Database
    'database' => [
        'title' => 'Database Configuration',
        'description' => 'Enter your database connection details. Make sure the database already exists.',
        'type' => 'Database Type',
        'mysql' => 'MySQL / MariaDB',
        'host' => 'Host',
        'port' => 'Port',
        'name' => 'Database Name',
        'username' => 'Username',
        'password' => 'Password',
        'test_connection' => 'Test Connection',
        'testing' => 'Testing...',
        'connection_success' => 'Connection successful! Server version: :version',
        'connection_failed' => 'Failed to test connection',
        'errors' => [
            'access_denied' => 'Access denied. Please check username and password.',
            'unknown_database' => 'Database does not exist. Please create it first.',
            'connection_refused' => 'Could not connect to database server. Please check host and port.',
            'host_not_found' => 'Database host not found. Please check the hostname.',
        ],
    ],

    // Environment
    'environment' => [
        'title' => 'Environment Setup',
        'description' => 'Configure your application settings.',
        'airline_name' => 'Airline Name',
        'airline_name_placeholder' => 'My Virtual Airline',
        'app_url' => 'Application URL',
        'app_url_placeholder' => 'https://example.com',
        'app_url_hint' => 'The full URL where VAOP will be accessible (without trailing slash)',
        'timezone' => 'Timezone',
    ],

    // Admin
    'admin' => [
        'title' => 'Create Admin Account',
        'description' => 'Create the first administrator account for your virtual airline.',
        'name' => 'Name',
        'name_placeholder' => 'Admin User',
        'email' => 'Email Address',
        'email_placeholder' => 'admin@example.com',
        'password' => 'Password',
        'password_placeholder' => 'Minimum 8 characters',
        'password_confirm' => 'Confirm Password',
        'password_confirm_placeholder' => 'Confirm your password',
    ],

    // Finalize
    'finalize' => [
        'title' => 'Finalize Installation',
        'ready_description' => 'Ready to complete the installation. This will:',
        'will_create_tables' => 'Create the database tables (:count migrations)',
        'will_create_admin' => 'Create the admin user (:email)',
        'will_finalize' => 'Finalize the installation',
        'install_button' => 'Install VAOP',
        'installing' => 'Installing...',
        'running_migrations' => 'Running migrations (:completed/:total)...',
        'creating_admin' => 'Creating admin user...',
        'optimizing' => 'Optimizing application...',
        'completing' => 'Completing installation...',
        'do_not_close' => 'Please do not close this window. The page will automatically continue.',
    ],

    // Complete
    'complete' => [
        'title' => 'Installation Complete!',
        'description' => 'VAOP has been successfully installed. You can now log in with your admin account.',
        'go_home' => 'Go to Homepage',
    ],

];
