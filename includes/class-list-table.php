<?php
namespace MillerMedia\ExpireUserPasswords;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

final class Expire_User_Passwords_List_Table {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		add_action( 'admin_head', array( $this, 'admin_css' ) );
		add_filter( 'manage_users_columns', array( $this, 'users_column' ) );
		add_action( 'manage_users_custom_column', array( $this, 'render_users_column' ), 10, 3 );
		add_filter( 'user_row_actions', array( $this, 'user_row_actions' ), 10, 2 );
		add_action( 'admin_footer-users.php', array( $this, 'force_reset_form' ) );
		add_action( 'admin_post_user_expass_force_reset_user', array( $this, 'handle_force_reset_user' ) );
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
	 * @param  array $columns
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
	 * @param  string $value
	 * @param  string $column_name
	 * @param  int    $user_id
	 *
	 * @return string
	 */
	public function render_users_column( $value, $column_name, $user_id ) {

		if ( 'user-expass' !== $column_name ) {

			return $value;

		}

		if (
			! Expire_User_Passwords::has_expirable_role( $user_id )
			||
			false === ( $reset = Expire_User_Passwords::get_user_meta( $user_id ) )
		) {

			return '&mdash;';

		}

		// translators: X amount of time ago
		$time_diff = sprintf( __( '%1$s ago', 'expire-user-passwords' ), human_time_diff( $reset, time() ) );
		$class     = Expire_User_Passwords::is_expired( $user_id ) ? 'user_expass-is-expired' : 'user-expass-not-expired';

		return sprintf(
			'<span class="%s">%s</span>',
			esc_attr( $class ),
			esc_html( $time_diff )
		);
	}

	/**
	 * Add a "Force Password Reset" row action for users in expirable roles.
	 *
	 * @filter user_row_actions
	 *
	 * @param  array    $actions Row actions.
	 * @param  \WP_User $user    User object.
	 *
	 * @return array
	 */
	public function user_row_actions( $actions, $user ) {

		if ( ! current_user_can( 'manage_options' ) || ! Expire_User_Passwords::has_expirable_role( $user ) ) {

			return $actions;

		}

		$actions['user_expass_force_reset'] = sprintf(
			'<a href="#" class="user-expass-force-reset" data-user-id="%d" data-nonce="%s">%s</a>',
			absint( $user->ID ),
			esc_attr( wp_create_nonce( 'user_expass_force_reset_user_' . $user->ID ) ),
			esc_html__( 'Force Password Reset', 'expire-user-passwords' )
		);

		return $actions;
	}

	/**
	 * Print the form used by the row action to POST a forced reset.
	 *
	 * @action admin_footer-users.php
	 */
	public function force_reset_form() {

		if ( ! current_user_can( 'manage_options' ) ) {

			return;

		}

		?>
		<form id="user-expass-force-reset-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="display:none;">
			<input type="hidden" name="action" value="user_expass_force_reset_user">
			<input type="hidden" name="user_id" value="">
			<input type="hidden" name="_wpnonce" value="">
		</form>
		<script>
		document.addEventListener( 'click', function ( event ) {
			var link = event.target.closest( '.user-expass-force-reset' );

			if ( ! link ) {
				return;
			}

			event.preventDefault();

			if ( ! window.confirm( '<?php echo esc_js( __( 'Are you sure you want to force this user to reset their password?', 'expire-user-passwords' ) ); ?>' ) ) {
				return;
			}

			var form = document.getElementById( 'user-expass-force-reset-form' );

			form.elements.user_id.value  = link.getAttribute( 'data-user-id' );
			form.elements._wpnonce.value = link.getAttribute( 'data-nonce' );
			form.submit();
		} );
		</script>
		<?php
	}

	/**
	 * Force a password reset for a single user.
	 *
	 * @action admin_post_user_expass_force_reset_user
	 */
	public function handle_force_reset_user() {

		if ( ! current_user_can( 'manage_options' ) ) {

			wp_die( esc_html__( 'You do not have permission to perform this action.', 'expire-user-passwords' ), 403 );

		}

		if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {

			wp_die( esc_html__( 'Invalid request.', 'expire-user-passwords' ), 405 );

		}

		$user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;

		check_admin_referer( 'user_expass_force_reset_user_' . $user_id );

		$count = Expire_User_Passwords::force_password_reset( $user_id ) ? 1 : 0;

		wp_safe_redirect( add_query_arg( 'user_expass_force_reset', $count, admin_url( 'users.php' ) ) );
		exit;
	}
}
