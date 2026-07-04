<?php
/**
 * List table handler for Expire User Passwords.
 *
 * @package Expire-User-Passwords
 */

namespace MillerMedia\ExpireUserPasswords;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

/**
 * List table handler.
 */
final class Expire_User_Passwords_List_Table {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		add_action( 'admin_head', array( $this, 'admin_css' ) );
		add_filter( 'manage_users_columns', array( $this, 'users_column' ) );
		add_action( 'manage_users_custom_column', array( $this, 'render_users_column' ), 10, 3 );
		add_filter( 'user_row_actions', array( $this, 'user_row_actions' ), 10, 2 );
		add_action( 'admin_post_force_reset_user', array( $this, 'handle_force_reset_user' ) );
	}

	/**
	 * Print custom CSS styles for the users.php screen.
	 *
	 * @action admin_head
	 */
	public function admin_css() {

		$screen = get_current_screen();

		if ( ! isset( $screen->id ) || 'users' !== $screen->id ) {

			return;

		}

		?>
		<style type="text/css">
		.fixed .column-user-expass {
			width: 150px;
		}
		@media screen and (max-width: 782px) {
			.fixed .column-user-expass {
				display: none;
			}
		}
		.user-expass-is-expired {
			color: #a00;
		}
		</style>
		<?php
	}

	/**
	 * Add a custom column to the Users list table.
	 *
	 * @filter manage_users_columns
	 *
	 * @param  array $columns Column definitions.
	 *
	 * @return array
	 */
	public function users_column( $columns ) {

		$columns['user-expass'] = esc_html__( 'Password Reset', 'expire-user-passwords' );

		return $columns;
	}

	/**
	 * Add content to the custom column in the Users list table.
	 *
	 * @action manage_users_custom_column
	 *
	 * @param  string $value       Current column value.
	 * @param  string $column_name Column name.
	 * @param  int    $user_id     User ID.
	 *
	 * @return string
	 */
	public function render_users_column( $value, $column_name, $user_id ) {

		if ( 'user-expass' !== $column_name ) {

			return $value;

		}

		$reset = Expire_User_Passwords::get_user_meta( $user_id );

		if (
			! Expire_User_Passwords::has_expirable_role( $user_id )
			||
			false === $reset
		) {

			return '&mdash;';

		}

		/* translators: %s: Time ago. */
		$time_diff = sprintf( __( '%1$s ago', 'expire-user-passwords' ), human_time_diff( $reset, time() ) );
		$class     = Expire_User_Passwords::is_expired( $user_id ) ? 'user_expass-is-expired' : 'user-expass-not-expired';

		return sprintf(
			'<span class="%s">%s</span>',
			esc_attr( $class ),
			esc_html( $time_diff )
		);
	}

	/**
	 * Add force reset action link to user row actions.
	 *
	 * @filter user_row_actions
	 *
	 * @param  array   $actions Row actions.
	 * @param  WP_User $user    User object.
	 *
	 * @return array
	 */
	public function user_row_actions( $actions, $user ) {

		if ( ! Expire_User_Passwords::has_expirable_role( $user->ID ) ) {

			return $actions;

		}

		$url = wp_nonce_url(
			admin_url( 'admin-post.php?action=force_reset_user&user_id=' . $user->ID ),
			'force_reset_user_' . $user->ID
		);

		$actions['force_reset'] = sprintf(
			'<a href="%s" class="submitforce" onclick="return confirm(\'%s\');">%s</a>',
			esc_url( $url ),
			esc_attr__( 'Are you sure you want to force this user to reset their password?', 'expire-user-passwords' ),
			esc_html__( 'Force Reset', 'expire-user-passwords' )
		);

		return $actions;
	}

	/**
	 * Handle the force reset user action.
	 *
	 * @action admin_post_force_reset_user
	 */
	public function handle_force_reset_user() {

		$user_id = isset( $_GET['user_id'] ) ? absint( $_GET['user_id'] ) : 0;

		if ( ! $user_id || ! current_user_can( 'manage_options' ) ) {

			wp_die( esc_html__( 'You do not have permission to perform this action.', 'expire-user-passwords' ) );

		}

		check_admin_referer( 'force_reset_user_' . $user_id );

		Expire_User_Passwords::force_password_reset( $user_id );

		wp_safe_redirect( add_query_arg( 'message', 'force_reset_success', admin_url( 'users.php' ) ) );
		exit;
	}
}
