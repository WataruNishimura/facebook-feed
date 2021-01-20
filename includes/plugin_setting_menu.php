<?php

class FFD_SETTING_MENU {

	public function __construct() {
	}

	public function init() {
		add_action( 'admin_menu', array( __CLASS__, 'register_plugin_settings' ) );
		$this->show_form();
	}

	function register_plugin_settings() {
		register_setting( PLUGIN_SETTING_GROUP, PLUGIN_SETTING_GROUP . '_fb_app_id' );
		// register_setting( PLUGIN_SETTING_GROUP, PLUGIN_SETTING_GROUP . '_fb_app_secret' );
		
		add_settings_section( PLUGIN_SETTING_GROUP . '_section', 'Plugin Settings', array( __CLASS__, 'settings_description' ), 'facebook-feed-settings' );
		add_settings_field( PLUGIN_SETTING_GROUP . '_fb_app_id', 'Facebook App ID', array( __CLASS__, 'fb_app_id_setting' ), 'facebook-feed-settings', PLUGIN_SETTING_GROUP . '_section' );
	}

	// Settings Section
	function settings_description() {
		?>
	<p>This section is settings for this plugin.</p>
		<?php
	}

	// Facebook App ID form
	function fb_app_id_setting() {
		$plugin_settings = get_option( PLUGIN_SETTING_GROUP );
		?>
	<input type="text" name="<?php echo PLUGIN_SETTING_GROUP . '_fb_app_secret'; ?>" value="<?php esc_html( $plugin_settings['fb_app_id'] ); ?>">
		<?php
	}

	function show_form() {
		?>
	<form method="POST">
		<?php
		settings_fields( PLUGIN_SETTING_GROUP );
		do_settings_sections( 'facebook-feed-settings' );
		do_settings_fields( 'facebook-feed-settings', PLUGIN_SETTING_GROUP . '_section' );
		submit_button();
		?>
	</form>

		<?php
	}
}
