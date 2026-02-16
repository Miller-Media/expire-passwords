<?php
/**
 * Uninstall handler for Expire User Passwords.
 *
 * @package Expire_User_Passwords
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

$options = (array) get_option( 'user_expass_settings', array() );
$delete_data = ! empty( $options['delete_data_on_uninstall'] );

if ( ! $delete_data ) {
	return;
}

// Delete plugin options.
delete_option( 'user_expass_settings' );
delete_option( 'expass_activated_on' );

// Clean up user meta.
global $wpdb;
$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => 'user_expass_password_reset' ) );
$wpdb->delete( $wpdb->usermeta, array( 'meta_key' => 'expire-user-passwords_review_dismissed' ) );
