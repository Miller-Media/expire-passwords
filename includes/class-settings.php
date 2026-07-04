<?php
/**
 * Settings page for Expire User Passwords.
 *
 * @package Expire-User-Passwords
 */

namespace MillerMedia\ExpireUserPasswords;

if ( ! defined( 'ABSPATH' ) ) {

	exit;

}

/**
 * Settings page handler.
 */
final class Expire_User_Passwords_Settings {

	/**
	 * Class constructor.
	 */
	public function __construct() {

		add_action( 'admin_menu', array( $this, 'submenu_page' ) );
		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_post_force_reset_all', array( $this, 'handle_force_reset_all' ) );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_filter( 'plugin_action_links', array( $this, 'plugin_link' ), 10, 2 );
	}

	/**
	 * Add custom submenu page under the Users menu.
	 *
	 * @action admin_menu
	 */
	public function submenu_page() {

		add_submenu_page(
			'users.php',
			esc_html__( 'Expire User Passwords', 'expire-user-passwords' ),
			esc_html__( 'Expire User Passwords', 'expire-user-passwords' ),
			apply_filters( 'eup_submenu_access', 'manage_options' ),
			'Expire_User_passwords',
			array( $this, 'render_submenu_page' )
		);
	}

	/**
	 * Add a settings link to the plugin on the plugin page
	 *
	 * @action plugin_action_links
	 *
	 * @param string[] $actions     An array of plugin action links. By default this can include 'activate', 'delete', 'network_only', ....
	 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
	 *
	 * @return string[]
	 */
	public function plugin_link( array $actions, string $plugin_file ): array {
		if ( EXPIRE_USER_PASSWORDS_PLUGIN !== $plugin_file ) {
			return $actions; // wrong plugin.
		}

		$href          = admin_url( 'users.php?page=Expire_User_passwords' );
		$settings_link = '<a href="' . $href . '">' . __( 'Settings' ) . '</a>'; // phpcs:ignore WordPress.WP.I18n.MissingArgDomain
		array_unshift( $actions, $settings_link );

		return $actions;
	}

	/**
	 * Content for the custom submenu page under the Users menu.
	 *
	 * @see $this->submenu_page()
	 */
	public function render_submenu_page() {

		?>
		<div class="wrap">

			<h2><?php esc_html_e( 'Expire User Passwords', 'expire-user-passwords' ); ?></h2>

			<form method="post" action="options.php">
				<?php

				settings_fields( 'user_expass_settings_page' );

				do_settings_sections( 'user_expass_settings_page' );

				submit_button();

				?>
			</form>

		</div>
		<?php
	}

	/**
	 * Register custom setting sections and fields.
	 *
	 * @action admin_init
	 */
	public function init() {

		register_setting(
			'user_expass_settings_page',
			'user_expass_settings',
			array(
				'sanitize_callback' => array( $this, 'sanitize_settings' ),
			)
		);

		add_settings_section(
			'user_expass_settings_page_section',
			null,
			array( $this, 'render_section' ),
			'user_expass_settings_page'
		);

		add_settings_field(
			'user_expass_settings_field_limit',
			esc_html__( 'Require password reset every', 'expire-user-passwords' ),
			array( $this, 'render_field_limit' ),
			'user_expass_settings_page',
			'user_expass_settings_page_section'
		);

		add_settings_field(
			'user_expass_settings_field_roles',
			esc_html__( 'For users in these roles', 'expire-user-passwords' ),
			array( $this, 'render_field_roles' ),
			'user_expass_settings_page',
			'user_expass_settings_page_section'
		);

		add_settings_field(
			'user_expass_settings_field_start_date',
			esc_html__( 'Start enforcing from date', 'expire-user-passwords' ),
			array( $this, 'render_field_start_date' ),
			'user_expass_settings_page',
			'user_expass_settings_page_section'
		);

		add_settings_field(
			'user_expass_settings_field_apply_to_all',
			esc_html__( 'Apply start date to all users', 'expire-user-passwords' ),
			array( $this, 'render_field_apply_to_all' ),
			'user_expass_settings_page',
			'user_expass_settings_page_section'
		);

		add_settings_field(
			'user_expass_settings_field_email',
			esc_html__( 'Reset via email', 'expire-user-passwords' ),
			array( $this, 'render_field_email' ),
			'user_expass_settings_page',
			'user_expass_settings_page_section'
		);

		add_settings_field(
			'user_expass_settings_field_delete_data',
			esc_html__( 'Remove all plugin data when deleted', 'expire-user-passwords' ),
			array( $this, 'render_field_delete_data' ),
			'user_expass_settings_page',
			'user_expass_settings_page_section'
		);

		add_settings_field(
			'user_expass_settings_field_force_reset',
			esc_html__( 'Force Password Reset', 'expire-user-passwords' ),
			array( $this, 'render_field_force_reset' ),
			'user_expass_settings_page',
			'user_expass_settings_page_section'
		);
	}

	/**
	 * Sanitize settings values before saving.
	 *
	 * @param array $input Raw input values.
	 * @return array Sanitized values.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = array();
		if ( isset( $input['limit'] ) ) {
			$sanitized['limit'] = absint( $input['limit'] );
		}
		if ( isset( $input['roles'] ) && is_array( $input['roles'] ) ) {
			$sanitized['roles'] = array_map( 'absint', $input['roles'] );
		}
		if ( isset( $input['send_email'] ) ) {
			$sanitized['send_email'] = sanitize_text_field( $input['send_email'] );
		}
		if ( isset( $input['delete_data_on_uninstall'] ) ) {
			$sanitized['delete_data_on_uninstall'] = absint( $input['delete_data_on_uninstall'] );
		}
		if ( isset( $input['start_date'] ) ) {
			$date = sanitize_text_field( $input['start_date'] );
			if ( ! empty( $date ) && strtotime( $date ) ) {
				$sanitized['start_date'] = $date;
			}
		}
		if ( isset( $input['apply_start_date_to_all'] ) ) {
			$sanitized['apply_start_date_to_all'] = absint( $input['apply_start_date_to_all'] );
		}
		return $sanitized;
	}

	/**
	 * Content for the custom settings section.
	 *
	 * @see $this->init()
	 */
	public function render_section() {

		printf(
			'<p>%s</p>',
			esc_html__( 'Require certain users to change their passwords on a regular basis.', 'expire-user-passwords' )
		);
	}

	/**
	 * Content for the limit setting field.
	 *
	 * @see $this->init()
	 */
	public function render_field_limit() {

		$options = (array) get_option( 'user_expass_settings', array() );
		$value   = isset( $options['limit'] ) ? $options['limit'] : null;

		printf(
			'<input type="number" min="1" max="365" maxlength="3" name="user_expass_settings[limit]" placeholder="%s" value="%s"> %s',
			esc_attr( Expire_User_Passwords::$default_limit ),
			esc_attr( $value ),
			esc_html__( 'days', 'expire-user-passwords' )
		);
	}

	/**
	 * Content for the roles setting field.
	 *
	 * @see $this->init()
	 */
	public function render_field_roles() {

		$options = (array) get_option( 'user_expass_settings', array() );
		$roles   = get_editable_roles();

		foreach ( $roles as $role => $role_data ) {

			$name  = sanitize_key( $role );
			$value = ( ! $options ) ? ( 'administrator' === $role ? 0 : 1 ) : ( empty( $options['roles'][ $name ] ) ? 0 : 1 );

			printf(
				'<p><input type="checkbox" name="user_expass_settings[roles][%1$s]" id="user_expass_settings[roles][%1$s]" %2$s value="1"><label for="user_expass_settings[roles][%1$s]">%3$s</label></p>',
				esc_attr( $name ),
				checked( $value, 1, false ),
				esc_html( $role_data['name'] )
			);

		}
	}

	/**
	 * Content for the roles setting field.
	 *
	 * @see $this->init()
	 */
	public function render_field_email() {
		$options    = (array) get_option( 'user_expass_settings', array() );
		$send_email = '1';
		if ( isset( $options['send_email'] ) ) {
			$send_email = $options['send_email'];
		}

		echo '<p><label>';
		echo '<input type="radio" name="user_expass_settings[send_email]" id="user_expass_settings[send_email]" value="1"' . checked( $send_email, '1', false ) . '>';
		echo esc_html__( 'Send an email with the password reset link.', 'expire-user-passwords' );
		echo '</label></p>';

		echo '<p><label>';
		echo '<input type="radio" name="user_expass_settings[send_email]" id="user_expass_settings[send_email]" value="0"' . checked( $send_email, '0', false ) . '>';
		echo esc_html__( 'Reset password directly on the login screen.', 'expire-user-passwords' );
		echo '</label></p>';
	}

	/**
	 * Content for the delete data on uninstall setting field.
	 */
	public function render_field_delete_data() {
		$options     = (array) get_option( 'user_expass_settings', array() );
		$delete_data = ! empty( $options['delete_data_on_uninstall'] ) ? 1 : 0;

		printf(
			'<p><label><input type="checkbox" name="user_expass_settings[delete_data_on_uninstall]" value="1" %s> %s</label></p>',
			checked( $delete_data, 1, false ),
			esc_html__( 'Check this box if you want all plugin settings and data to be removed when the plugin is deleted.', 'expire-user-passwords' )
		);
	}

	/**
	 * Content for the start date setting field.
	 */
	public function render_field_start_date() {
		$options    = (array) get_option( 'user_expass_settings', array() );
		$start_date = isset( $options['start_date'] ) ? $options['start_date'] : '';

		printf(
			'<input type="date" name="user_expass_settings[start_date]" value="%s">',
			esc_attr( $start_date )
		);
		echo '<p class="description">' . esc_html__( 'Set a start date from which password age is calculated for users who have never reset their password.', 'expire-user-passwords' ) . '</p>';
	}

	/**
	 * Content for the apply to all setting field.
	 */
	public function render_field_apply_to_all() {
		$options      = (array) get_option( 'user_expass_settings', array() );
		$apply_to_all = ! empty( $options['apply_start_date_to_all'] ) ? 1 : 0;

		printf(
			'<p><label><input type="checkbox" name="user_expass_settings[apply_start_date_to_all]" value="1" %s> %s</label></p>',
			checked( $apply_to_all, 1, false ),
			esc_html__( 'Apply the start date to all users, not just those who have never reset their password.', 'expire-user-passwords' )
		);
	}

	/**
	 * Render the force reset button field.
	 */
	public function render_field_force_reset() {
		$url = wp_nonce_url(
			admin_url( 'admin-post.php?action=force_reset_all' ),
			'force_reset_all'
		);

		printf(
			'<a href="%s" class="button button-secondary" onclick="return confirm(\'%s\');">%s</a>',
			esc_url( $url ),
			esc_attr__( 'Are you sure you want to force all users to reset their password?', 'expire-user-passwords' ),
			esc_html__( 'Force Password Reset Now', 'expire-user-passwords' )
		);
		echo '<p class="description">' . esc_html__( 'Force all users in expirable roles to reset their password on next login.', 'expire-user-passwords' ) . '</p>';
	}

	/**
	 * Handle the force reset all action.
	 *
	 * @action admin_post_force_reset_all
	 */
	public function handle_force_reset_all() {
		check_admin_referer( 'force_reset_all' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'expire-user-passwords' ) );
		}

		$roles = Expire_User_Passwords::get_roles();
		$users = get_users( array( 'role__in' => $roles ) );

		foreach ( $users as $user ) {
			Expire_User_Passwords::force_password_reset( $user->ID );
		}

		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => 'Expire_User_passwords',
					'message' => 'force_reset_success',
				),
				admin_url( 'users.php' )
			)
		);
		exit;
	}

	/**
	 * Show admin notices.
	 *
	 * @action admin_notices
	 */
	public function admin_notices() {
		if ( isset( $_GET['page'], $_GET['message'] ) && 'Expire_User_passwords' === $_GET['page'] && 'force_reset_success' === $_GET['message'] ) {
			echo '<div class="notice notice-success is-dismissable"><p>' . esc_html__( 'Password reset has been forced for all users.', 'expire-user-passwords' ) . '</p></div>';
		}
	}
}
