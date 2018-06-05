<?php
// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Implements the category/term login shortcode.
 *
 * @package
 * @author    Barn2 Media <info@barn2.co.uk>
 * @license   GPL-3.0
 * @link      https://barn2.co.uk
 * @copyright 2016-2018 Barn2 Media Ltd
 */
class PPC_Login_Shortcode {

	const SHORTCODE = 'category_login';

	public static function register_shortcode() {
		// Register shortcode
		add_shortcode( self::SHORTCODE, array( __CLASS__, 'do_login_shortcode' ) );
	}

	public static function do_login_shortcode( $atts = array() ) {
		$atts = shortcode_atts( array(
			'message' => PPC_Util::get_option( 'form_message' )
			), $atts, self::SHORTCODE );

		return PPC_Protected_Term_Login::get_password_form( $atts['message'] );
	}

}