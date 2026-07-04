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
    public function get_effective_reset_date_returns_start_date_when_apply_all(): void {
        $this->mockSettings( [
            'start_date'              => '2026-06-01',
            'apply_start_date_to_all' => 1,
        ] );
        $this->mockUserMeta( '1700000000' );

        $result = Expire_User_Passwords::get_effective_reset_date( 1 );

        $this->assertSame( strtotime( '2026-06-01' ), $result );
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

    /** @test */
    public function force_password_reset_returns_false_for_invalid_user(): void {
        Functions\when( 'get_user_by' )->justReturn( false );

        $this->assertFalse( Expire_User_Passwords::force_password_reset( 999 ) );
    }

    /** @test */
    public function force_password_reset_sets_past_timestamp_and_destroys_sessions(): void {
        $this->mockSettings( [ 'limit' => 90, 'send_email' => '0' ] );
        $this->mockWpdb();
        Functions\when( 'get_user_by' )->alias( function ( $field, $value ) {
            return (object) [
                'ID'         => (int) $value,
                'roles'      => [ 'editor' ],
                'user_email' => 'user@example.com',
                'user_login' => 'testuser',
            ];
        } );

        $updated_meta = null;
        Functions\expect( 'update_user_meta' )
            ->once()
            ->with( 1, 'user_expass_password_reset', \Mockery::type( 'int' ) )
            ->andReturnUsing( function ( $user_id, $key, $value ) use ( &$updated_meta ) {
                $updated_meta = $value;
            } );

        Functions\expect( 'delete_user_meta' )
            ->times( 4 );

        $result = Expire_User_Passwords::force_password_reset( 1 );

        $this->assertTrue( $result );
        $this->assertNotNull( $updated_meta );
        $expected_past = time() - ( 90 * 86400 ) - 1;
        $this->assertLessThanOrEqual( time() - ( 90 * 86400 ) - 1, $updated_meta );
    }

    /** @test */
    public function force_password_reset_with_email_sends_notification(): void {
        $this->mockSettings( [ 'limit' => 90, 'send_email' => '1' ] );
        $this->mockWpdb();

        Functions\when( 'get_user_by' )->alias( function ( $field, $value ) {
            return (object) [
                'ID'         => (int) $value,
                'roles'      => [ 'editor' ],
                'user_email' => 'user@example.com',
                'user_login' => 'testuser',
            ];
        } );

        Functions\when( 'update_user_meta' )->justReturn( true );
        Functions\when( 'delete_user_meta' )->justReturn( true );

        Functions\expect( 'get_bloginfo' )
            ->times( 2 )
            ->with( 'name' )
            ->andReturn( 'Test Site' );

        Functions\expect( 'wp_mail' )
            ->once()
            ->with( 'user@example.com', \Mockery::type( 'string' ), \Mockery::type( 'string' ) );

        $this->assertTrue( Expire_User_Passwords::force_password_reset( 1 ) );
    }

    /** @test */
    public function force_password_reset_without_email_skips_notification(): void {
        $this->mockSettings( [ 'limit' => 90, 'send_email' => '0' ] );
        $this->mockWpdb();

        Functions\when( 'get_user_by' )->alias( function ( $field, $value ) {
            return (object) [
                'ID'         => (int) $value,
                'roles'      => [ 'editor' ],
                'user_email' => 'user@example.com',
                'user_login' => 'testuser',
            ];
        } );

        Functions\when( 'update_user_meta' )->justReturn( true );
        Functions\when( 'delete_user_meta' )->justReturn( true );

        Functions\expect( 'wp_mail' )
            ->never();

        $this->assertTrue( Expire_User_Passwords::force_password_reset( 1 ) );
    }
}
