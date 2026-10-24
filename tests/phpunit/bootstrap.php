<?php

namespace MillerMedia\ExpireUserPasswords\Tests;

if ( ! defined( 'ABSPATH' ) ) {
    define( 'ABSPATH', true );
}

if ( ! defined( 'DAY_IN_SECONDS' ) ) {
    define( 'DAY_IN_SECONDS', 86400 );
}

require_once __DIR__ . '/../../vendor/autoload.php';

\Brain\Monkey\setUp();

use Brain\Monkey\Functions;

Functions\when( 'plugin_basename' )->justReturn( 'expire-user-passwords/expire-user-passwords.php' );
Functions\when( 'plugin_dir_path' )->justReturn( __DIR__ . '/../../' );
Functions\when( 'plugin_dir_url' )->justReturn( 'http://example.com/wp-content/plugins/expire-user-passwords/' );
Functions\when( 'add_action' )->justReturn( true );
Functions\when( 'add_filter' )->justReturn( true );

require_once __DIR__ . '/../../expire-user-passwords.php';

\Brain\Monkey\tearDown();
