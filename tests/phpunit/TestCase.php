<?php

namespace MillerMedia\ExpireUserPasswords\Tests;

use Brain\Monkey\Functions;
use MillerMedia\ExpireUserPasswords\Expire_User_Passwords;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase {

    protected function setUp(): void {
        parent::setUp();
        \Brain\Monkey\setUp();

        Functions\when( 'add_action' )->justReturn( true );
        Functions\when( 'add_filter' )->justReturn( true );
        Functions\when( '__' )->returnArg();
        Functions\when( '_n' )->returnArg();
        Functions\when( 'esc_html__' )->returnArg();
        Functions\when( 'esc_html_e' )->echoArg();
        Functions\when( 'esc_attr__' )->returnArg();
        Functions\when( 'esc_attr' )->returnArg();
        Functions\when( 'esc_html' )->returnArg();
        Functions\when( 'apply_filters' )->returnArg();
        Functions\when( 'is_user_logged_in' )->justReturn( false );
        Functions\when( 'get_current_user_id' )->justReturn( 1 );
        Functions\when( 'get_user_by' )->alias( function ( $field, $value ) {
            return (object) [
                'ID'            => (int) $value,
                'roles'         => [ 'editor' ],
                'data'          => (object) [ 'user_pass' => 'hashed_old_password' ],
                'user_email'    => 'user@example.com',
                'user_login'    => 'testuser',
            ];
        } );

        Expire_User_Passwords::$default_limit = 90;
    }

    protected function tearDown(): void {
        \Brain\Monkey\tearDown();
        parent::tearDown();
    }

    protected function mockSettings( array $overrides = [] ): void {
        $defaults = [
            'limit'                     => 90,
            'roles'                     => [ 'editor' => 1 ],
            'start_date'                => '',
            'apply_start_date_to_all'   => 0,
            'send_email'                => '1',
        ];

        Functions\when( 'get_option' )->alias( function ( $option, $default = false ) use ( $defaults, $overrides ) {
            if ( 'user_expass_settings' === $option ) {
                return array_merge( $defaults, $overrides );
            }
            return $default;
        } );
    }

    protected function mockUserMeta( $timestamp ): void {
        Functions\when( 'get_user_meta' )->alias( function ( $user_id, $key, $single ) use ( $timestamp ) {
            if ( 'user_expass_password_reset' === $key ) {
                return $timestamp;
            }
            return false;
        } );
    }

    protected function mockWpdb(): void {
        global $wpdb;
        $wpdb = \Mockery::mock( 'stdClass' );
        $wpdb->prefix = 'wp_';
        $wpdb->shouldReceive( 'delete' )->andReturn( true );
    }
}
