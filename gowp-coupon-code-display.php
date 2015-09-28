<?php

/*
Plugin Name: GoWP Coupon Code Display
Description: Display a single coupon code to a visitor, removing it from the list of available coupon codes
Version:     1.0
Author:      GoWP
Author URI:  https://www.gowp.com
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: gowp
*/

class GoWP_Coupon_Code_Display {
	function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu_item' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );
		add_shortcode( 'gowpccd', array( $this, 'shortcode' ) );
	}
	function add_menu_item() {
		add_menu_page(
			"Coupon Codes Display",
			"Coupon Codes",
			"manage_options",
			"gowpccd",
			array( $this, 'settings_page'),
			"dashicons-tickets-alt"
		);
	}
	function settings_init() {
		add_settings_section(
			"gowpccd_settings",
			"Settings",
			array( $this, "settings_description" ),
			"gowpccd"
		);
		add_settings_field(
			"gowpccd_coupon_codes",
			"Coupon Codes",
			array( $this, "field_description" ),
			"gowpccd",
			"gowpccd_settings"
		);
		register_setting(
			"gowpccd",
			"gowpccd_coupon_codes",
			array( $this, "sanitize_coupon_codes" )
		);
	}
	function settings_description() {
		?>
			<p>Type or paste your coupon codes below, each code separated by a space or on a new line.</p>
			<p>Use the <code>[gowpccd]</code> shortcode on any page/post/etc to display one of the codes to a visitor. Each code will be removed after being displayed.</p>
		<?php
	}
	function field_description() {
		$codes = get_option( 'gowpccd_coupon_codes' );
		?>
			<textarea cols="40" rows="10" name="gowpccd_coupon_codes"><?php echo $codes; ?></textarea>
		<?php
	}
	function sanitize_coupon_codes( $value ) {
		$value = preg_replace( '/\s+/', "\n", $value );
		$value = explode( "\n", $value );
		$value = array_unique( $value );
		$value = implode( "\n", $value );
		return $value;
	}
	function settings_page() {
		?>
		<div class="wrap">
			<h1>Coupon Code Display</h1>
			<form method="POST" action="options.php">
				<?php settings_fields( 'gowpccd' ); ?>
				<?php do_settings_sections( 'gowpccd' ); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
	function shortcode( $atts ) {
		$codes = get_option( 'gowpccd_coupon_codes' );
		$codes = explode( "\n", $codes );
		$code = array_shift( $codes );
		update_option( 'gowpccd_coupon_codes', implode( "\n", $codes ) );
		return $code;
	}
}
new GoWP_Coupon_Code_Display;