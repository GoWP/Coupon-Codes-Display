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
			array( $this, "gowpccd_coupon_codes_render" ),
			"gowpccd",
			"gowpccd_settings"
		);
		register_setting(
			"gowpccd",
			"gowpccd_coupon_codes",
			array( $this, "sanitize_coupon_codes" )
		);
		add_settings_field(
			"gowpccd_restrictions",
			"Restrictions",
			array( $this, "gowpccd_restrictions_render" ),
			"gowpccd",
			"gowpccd_settings"
		);
		register_setting(
			"gowpccd",
			"gowpccd_restrictions"
		);
	}
	function settings_description() {
		?>
			<p>Type or paste your coupon codes below, each code separated by a space or on a new line.</p>
			<p>Use the <code>[gowpccd]</code> shortcode on any page/post/etc to display one of the codes to a visitor. Each code will be removed after being displayed.</p>
		<?php
	}
	function gowpccd_coupon_codes_render() {
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
	function gowpccd_restrictions_render() {
		$restrictions = get_option( "gowpccd_restrictions" );
		?>
			<p>Restrict the distribution of coupon codes.</p>
			<p><label><input type="checkbox" name="gowpccd_restrictions[]" value="ip" <?php checked( in_array( 'ip', $restrictions ) ); ?>> One coupon code per IP address</label></p>
		<?php
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
	function get_ip_address() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) { //check ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) { //to check ip is pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
	function shortcode( $atts ) {
		$restrictions = get_option( "gowpccd_restrictions" );
		if ( in_array( 'ip', $restrictions ) ) {
			$ip = $this->get_ip_address();
			$restricted = get_option( "gowpccd_restricted_ips" );
			if ( in_array( $ip, $restricted ) ) {
				return "IP restricted";
			} else {
				$time = microtime( TRUE );
				$restricted[$time] = $ip;
				update_option( "gowpccd_restricted_ips", $restricted, "no" );
				print_r( $restricted );
			}
		}
		$codes = get_option( 'gowpccd_coupon_codes' );
		$codes = explode( "\n", $codes );
		$code = array_shift( $codes );
		update_option( 'gowpccd_coupon_codes', implode( "\n", $codes ) );
		return $code;
	}
}
new GoWP_Coupon_Code_Display;