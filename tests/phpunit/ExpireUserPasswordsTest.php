<?php

namespace MillerMedia\ExpireUserPasswords\Tests;

use Brain\Monkey\Functions;
use MillerMedia\ExpireUserPasswords\Expire_User_Passwords;

class ExpireUserPasswordsTest extends TestCase {

    /** @test */
    public function get_limit_returns_default_when_no_option_set(): void {
        Functions\when( 'get_option' )->justReturn( [] );

        $this->assertSame( 90, Expire_User_Passwords::get_limit() );
    }

    /** @test */
    public function get_limit_returns_custom_value_from_settings(): void {
        $this->mockSettings( [ 'limit' => 30 ] );

        $this->assertSame( 30, Expire_User_Passwords::get_limit() );
    }

    /** @test */
    public function get_limit_caps_at_365(): void {
        $this->mockSettings( [ 'limit' => '1000' ] );

        $this->assertSame( 90, Expire_User_Passwords::get_limit() );
    }

    /** @test */
    public function get_limit_returns_filtered_default(): void {
        Functions\when( 'get_option' )->justReturn( [] );
        Expire_User_Passwords::$default_limit = 45;

        $this->assertSame( 45, Expire_User_Passwords::get_limit() );
    }

    /** @test */
    public function get_effective_reset_date_returns_user_meta_when_no_start_date(): void {
        $this->mockSettings( [ 'start_date' => '' ] );
        $this->mockUserMeta( '1700000000' );

        $result = Expire_User_Passwords::get_effective_reset_date( 1 );

        $this->assertSame( 1700000000, $result );
    }

    /** @test */
    public function get_effective_reset_date_returns_start_date_when_user_never_reset(): void {
        $this->mockSettings( [ 'start_date' => '2026-01-15' ] );
        $this->mockUserMeta( false );

        $result = Expire_User_Passwords::get_effective_reset_date( 1 );

        $this->assertSame( strtotime( '2026-01-15' ), $result );
    }

    /** @test */
    public function get_effective_reset_date_returns_start_date_when_apply_all_and_reset_is_older(): void {
        $this->mockSettings( [
            'start_date'              => '2026-06-01',
            'apply_start_date_to_all' => 1,
        ] );
        $this->mockUserMeta( '1700000000' );

        $result = Expire_User_Passwords::get_effective_reset_date( 1 );

        $this->assertSame( strtotime( '2026-06-01' ), $result );
    }

    /** @test */
    public function get_effective_reset_date_returns_user_meta_when_apply_all_and_reset_is_newer(): void {
        $reset = strtotime( '2026-06-15' );
        $this->mockSettings( [
            'start_date'              => '2026-06-01',
            'apply_start_date_to_all' => 1,
        ] );
        $this->mockUserMeta( (string) $reset );

        $result = Expire_User_Passwords::get_effective_reset_date( 1 );

        $this->assertSame( $reset, $result );
    }

    /** @test */
    public function is_expired_returns_false_after_reset_when_start_date_plus_limit_has_passed(): void {
        // Start Date + limit is in the past, but the user just reset their password.
        $this->mockSettings( [
            'limit'                   => 30,
            'start_date'              => gmdate( 'Y-m-d', time() - ( 60 * DAY_IN_SECONDS ) ),
            'apply_start_date_to_all' => 1,
        ] );
        $this->mockUserMeta( (string) ( time() - 10 ) );

        $this->assertFalse( Expire_User_Passwords::is_expired( 1 ) );
    }

    /** @test */
    public function is_expired_returns_true_when_start_date_plus_limit_has_passed_and_reset_is_older(): void {
        $this->mockSettings( [
            'limit'                   => 30,
            'start_date'              => gmdate( 'Y-m-d', time() - ( 60 * DAY_IN_SECONDS ) ),
            'apply_start_date_to_all' => 1,
        ] );
        $this->mockUserMeta( (string) ( time() - ( 90 * DAY_IN_SECONDS ) ) );

        $this->assertTrue( Expire_User_Passwords::is_expired( 1 ) );
    }

    /** @test */
    public function get_effective_reset_date_returns_user_meta_when_not_apply_all(): void {
        $this->mockSettings( [
            'start_date'              => '2026-06-01',
            'apply_start_date_to_all' => 0,
        ] );
        $this->mockUserMeta( '1700000000' );

        $result = Expire_User_Passwords::get_effective_reset_date( 1 );

        $this->assertSame( 1700000000, $result );
    }

    /** @test */
    public function get_roles_defaults_to_all_except_admin(): void {
        Functions\when( 'get_option' )->justReturn( [] );
        Functions\when( 'get_editable_roles' )->justReturn( [
            'administrator' => [ 'name' => 'Administrator' ],
            'editor'        => [ 'name' => 'Editor' ],
            'subscriber'    => [ 'name' => 'Subscriber' ],
        ] );

        $roles = Expire_User_Passwords::get_roles();

        $this->assertNotContains( 'administrator', $roles );
        $this->assertContains( 'editor', $roles );
        $this->assertContains( 'subscriber', $roles );
    }

    /** @test */
    public function get_expiration_returns_false_for_no_expirable_role(): void {
        $this->mockSettings( [ 'roles' => [ 'administrator' => 1 ] ] );
        $this->mockUserMeta( '1700000000' );

        Functions\when( 'get_user_by' )->alias( function ( $field, $value ) {
            return (object) [
                'ID'    => (int) $value,
                'roles' => [ 'subscriber' ],
            ];
        } );

        $this->assertFalse( Expire_User_Passwords::get_expiration( 1 ) );
    }

    /** @test */
    public function get_expiration_returns_false_when_no_reset_date(): void {
        $this->mockSettings( [] );
        $this->mockUserMeta( false );

        Functions\when( 'get_user_by' )->alias( function ( $field, $value ) {
            return (object) [
                'ID'    => (int) $value,
                'roles' => [ 'editor' ],
            ];
        } );

        $this->assertFalse( Expire_User_Passwords::get_expiration( 1 ) );
    }

    /** @test */
    public function get_expiration_calculates_correctly(): void {
        $this->mockSettings( [ 'limit' => 30 ] );
        $this->mockUserMeta( '1700000000' );

        Functions\when( 'get_user_by' )->alias( function ( $field, $value ) {
            return (object) [
                'ID'    => (int) $value,
                'roles' => [ 'editor' ],
            ];
        } );

        $expected = gmdate( 'Y-m-d', strtotime( '@1700000000 + 30 days' ) );
        $result   = Expire_User_Passwords::get_expiration( 1, 'Y-m-d' );

        $this->assertSame( $expected, $result );
    }

    /** @test */
    public function is_expired_returns_false_when_password_not_expired(): void {
        $future = time() - 10;
        $this->mockSettings( [ 'limit' => 90 ] );
        $this->mockUserMeta( (string) $future );

        Functions\when( 'get_user_by' )->alias( function ( $field, $value ) {
            return (object) [
                'ID'    => (int) $value,
                'roles' => [ 'editor' ],
            ];
        } );

        $this->assertFalse( Expire_User_Passwords::is_expired( 1 ) );
    }

    /** @test */
    public function is_expired_returns_true_when_password_expired(): void {
        $past = time() - ( 91 * 86400 );
        $this->mockSettings( [ 'limit' => 90 ] );
        $this->mockUserMeta( (string) $past );

        Functions\when( 'get_user_by' )->alias( function ( $field, $value ) {
            return (object) [
                'ID'    => (int) $value,
                'roles' => [ 'editor' ],
            ];
        } );

        $this->assertTrue( Expire_User_Passwords::is_expired( 1 ) );
    }

    /** @test */
    public function has_expirable_role_returns_true_for_expirable_role(): void {
        $this->mockSettings( [ 'roles' => [ 'editor' => 1 ] ] );

        Functions\when( 'get_user_by' )->alias( function ( $field, $value ) {
            return (object) [
                'ID'    => (int) $value,
                'roles' => [ 'editor' ],
            ];
        } );

        $this->assertTrue( Expire_User_Passwords::has_expirable_role( 1 ) );
    }

    /** @test */
    public function has_expirable_role_returns_false_for_non_expirable_role(): void {
        $this->mockSettings( [ 'roles' => [ 'administrator' => 1 ] ] );

        Functions\when( 'get_user_by' )->alias( function ( $field, $value ) {
            return (object) [
                'ID'    => (int) $value,
                'roles' => [ 'subscriber' ],
            ];
        } );

        $this->assertFalse( Expire_User_Passwords::has_expirable_role( 1 ) );
    }

    /** @test */
    public function has_expirable_role_returns_false_for_invalid_user(): void {
        $this->mockSettings( [ 'roles' => [ 'editor' => 1 ] ] );

        Functions\when( 'get_user_by' )->justReturn( false );
        Functions\when( 'get_current_user_id' )->justReturn( 999 );

        $this->assertFalse( Expire_User_Passwords::has_expirable_role( null ) );
    }
}
