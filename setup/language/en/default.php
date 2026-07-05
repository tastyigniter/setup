<?php

function lang($key)
{
    $docs = DOCS_TROUBLESHOOTING_URL;

    $lang = [
        'text_title' => 'TastyIgniter Setup',
        'text_subtitle' => 'Install TastyIgniter v4 on your server in a few minutes.',
        'text_installation' => 'Setup Wizard',

        'text_start_heading' => 'Get started',
        'text_start_sub_heading' => 'Welcome! We will check your server, then ask for your database details.',
        'text_database_heading' => 'Database',
        'text_database_sub_heading' => 'Enter your MySQL connection details. Use a new database or an existing TastyIgniter v3 database to upgrade.',
        'text_install_heading' => 'Installing',
        'text_install_sub_heading' => 'Sit tight while we download and configure TastyIgniter.',
        'text_complete_heading' => 'All done!',
        'text_complete_sub_heading' => 'TastyIgniter is ready. Complete your restaurant setup in the admin panel.',

        'text_license_heading' => 'License agreement',
        'text_license_sub_heading' => 'Please review and accept the MIT license to continue.',
        'text_requirement_heading' => 'Server requirements',
        'text_requirement_sub_heading' => 'We are checking that your server meets the TastyIgniter v4 requirements.',
        'text_mysql_version' => 'MySQL '.TI_MYSQL_VERSION.'+ or MariaDB '.TI_MARIADB_VERSION.'+ is required.',

        'text_license_agreed' => 'I have read and accept the MIT License agreement.',
        'text_view_license' => 'View license',
        'text_login_to_admin' => 'Open admin panel',
        'text_goto_storefront' => 'View storefront',
        'text_installation_success' => 'TastyIgniter has been installed successfully.',
        'text_docs_link' => 'Full installation guide',
        'text_security_warning' => 'For security, delete setup.php and the setup/ folder once you confirm everything works.',

        'text_checklist_delete' => 'Delete setup.php and the setup/ directory',
        'text_checklist_permissions' => 'Ensure storage/ and bootstrap/cache/ are writable',
        'text_checklist_cron' => 'Add a cron job for scheduled tasks',
        'text_checklist_admin' => 'Log in to /admin to configure your restaurant',
        'text_checklist_docs' => 'See the post-installation guide for docroot and cron help',

        'label_php_version' => 'PHP '.TI_PHP_VERSION.' or higher',
        'label_extensions' => 'Required PHP extensions',
        'label_pdo' => 'PDO MySQL extension',
        'label_curl' => 'cURL extension',
        'label_zip' => 'ZipArchive support',
        'label_network' => 'GitHub Releases connectivity',
        'label_writable' => 'Writable installation directory',
        'label_memory_limit' => 'Memory limit (256M+ recommended)',
        'label_max_execution_time' => 'Max execution time (120s+ recommended)',

        'hint_php_version' => 'Current version: '.PHP_VERSION,
        'hint_extensions' => 'Enable bcmath, ctype, dom, exif, gd, intl, json, mbstring, openssl, tokenizer, and xml. <a href="'.$docs.'" target="_blank" rel="noopener">Learn more</a>',
        'hint_pdo' => 'Enable the PDO and pdo_mysql extensions. <a href="'.$docs.'" target="_blank" rel="noopener">Learn more</a>',
        'hint_curl' => 'Enable the cURL extension. TastyIgniter uses it for outbound HTTP requests, and the installer needs it to download the release. <a href="'.$docs.'" target="_blank" rel="noopener">Learn more</a>',
        'hint_zip' => 'Enable the ZipArchive PHP extension. <a href="'.$docs.'" target="_blank" rel="noopener">Learn more</a>',
        'hint_network' => 'Your server must reach github.com to download the release package. <a href="'.$docs.'" target="_blank" rel="noopener">Learn more</a>',
        'hint_writable' => 'Set write permissions on this directory and your web root. <a href="'.$docs.'" target="_blank" rel="noopener">Learn more</a>',
        'hint_memory_limit' => 'Increase memory_limit in PHP settings for a smoother install. <a href="'.$docs.'" target="_blank" rel="noopener">Learn more</a>',
        'hint_max_execution_time' => 'Increase max_execution_time or ask your host to allow longer requests. <a href="'.$docs.'" target="_blank" rel="noopener">Learn more</a>',

        'label_database' => 'Database name',
        'label_hostname' => 'Hostname',
        'label_port' => 'Port',
        'label_username' => 'Username',
        'label_password' => 'Password',
        'label_prefix' => 'Table prefix',

        'help_hostname' => 'Usually localhost or 127.0.0.1 on shared hosting.',
        'help_username' => 'The MySQL user with access to your database.',
        'help_password' => 'The password for your MySQL user.',
        'help_dbprefix' => 'Leave as ti_ unless your host requires a different prefix.',

        'step_start' => 'Get started',
        'step_database' => 'Database',
        'step_install' => 'Installing',
        'step_complete' => 'Complete',

        'button_continue' => 'Continue',
        'button_install' => 'Install TastyIgniter',
        'button_retry' => 'Try again',
        'button_view_log' => 'View setup log',

        'install_prepare' => 'Preparing installation directory',
        'install_download' => 'Downloading TastyIgniter',
        'install_extract' => 'Extracting files',
        'install_config' => 'Writing configuration',
        'install_migrate' => 'Setting up database',
        'install_finalize' => 'Finishing installation',

        'alert_license_error' => 'Please accept the license agreement before continuing.',
        'alert_database_in_use' => 'Database "%s" contains tables that do not look like TastyIgniter. Use a different database or table prefix.',
        'alert_requirement_error' => 'Please resolve the failed requirements before continuing.',
        'alert_install_failed' => 'Installation failed. Check setup/setup.log or the troubleshooting guide.',
    ];

    return $lang[$key] ?? $key;
}
