<?php
/**
 * The main plugin file for Password Protected Categories.
 *
 * This file is included during the WordPress bootstrap process if the plugin is active.
 *
 * @wordpress-plugin
 * Plugin Name:       Password Protected Categories
 * Plugin URI:	      https://barn2.co.uk/wordpress-plugins/password-protected-categories/
 * Description:       Password protect any category or custom taxonomy, or make them completely private.
 * Version:           1.2.1
 * Author:            Barn2 Media
 * Author URI:        http://barn2.co.uk
 * Text Domain:       password-protected-categories
 * Domain Path:       /languages
 *
 * Copyright:		  2016-2018 Barn2 Media Ltd
 * License:           GNU General Public License v3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.html
 */
// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The main plugin class for Password Protected Categories. Implemented as a singleton.
 *
 * @package   Password_Protected_Categories
 * @author    Barn2 Media <info@barn2.co.uk>
 * @license   GPL-3.0
 * @link      https://barn2.co.uk
 * @copyright 2016-2018 Barn2 Media Ltd
 */
class Password_Protected_Categories_Plugin {

	const NAME = 'Password Protected Categories';

	const VERSION = '1.2.1';

	const FILE = __FILE__;

	public $term_protector;

	public $sitemaps;

	/**
	 * Our plugin license manager.
	 */
	public $license;

	/**
	 * The singleton instance.
	 */
	private static $_instance;

	public function __construct() {
		$this->define_constants();
		$this->includes();
		$this->init_hooks();

		// Instantiate updater / license checker - 2nd arg *must* match plugin name used in EDD
		$this->license = new Barn2_Plugin_License( self::FILE, self::NAME, self::VERSION, 'ppc' );
	}

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function define_constants() {
		if ( ! defined( 'PPC_INCLUDES_DIR' ) ) {
			define( 'PPC_INCLUDES_DIR', plugin_dir_path( self::FILE ) . 'includes/' );
		}
		if ( ! defined( 'PPC_PLUGIN_BASENAME' ) ) {
			define( 'PPC_PLUGIN_BASENAME', plugin_basename( self::FILE ) );
		}
		if ( ! defined( 'PPC_COOKIE_PREFIX' ) ) {
			define( 'PPC_COOKIE_PREFIX', 'wp-postpass_' );
		}
	}

	private function includes() {
		require_once PPC_INCLUDES_DIR . 'license/class-b2-plugin-license.php';
		require_once PPC_INCLUDES_DIR . 'class-ppc-util.php';
		require_once PPC_INCLUDES_DIR . 'class-ppc-term-visibility.php';
		require_once PPC_INCLUDES_DIR . 'class-ppc-xml-sitemaps.php';

		if ( is_admin() ) {
			require_once PPC_INCLUDES_DIR . 'lib/class-wp-settings-api-helper.php';
			require_once PPC_INCLUDES_DIR . 'admin/class-ppc-admin-settings-page.php';
			require_once PPC_INCLUDES_DIR . 'admin/class-ppc-admin-term-visibility-field.php';
		}
		if ( $this->is_front_end() ) {
			require_once PPC_INCLUDES_DIR . 'class-ppc-login-shortcode.php';
			require_once PPC_INCLUDES_DIR . 'class-ppc-term-protector.php';
			require_once PPC_INCLUDES_DIR . 'class-ppc-protected-term-login.php';
		}
	}

	private function init_hooks() {
		// Init core plugin classes
		add_action( 'init', array( $this, 'init' ) );

		// Register styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );
	}

	public function init() {
		$this->load_textdomain();

		if ( is_admin() ) {
			new PPC_Admin_Settings_Page( $this->license );
		}

		if ( $this->check_min_requirements() && $this->license->is_valid() ) {
			// Sitemap hooks could be front-end or admin
			$this->sitemaps = new PPC_XML_Sitemaps();

			if ( is_admin() ) {
				// Add our visibility field to all categories and custom taxonomies
				new PPC_Admin_Term_Visibility_Field();
			}
			if ( $this->is_front_end() ) {
				// Protect categories and posts on the front-end
				$this->term_protector = new PPC_Term_Protector();

				// Handle the password form login
				PPC_Protected_Term_Login::handle_login();

				// Register the category login shortcode
				PPC_Login_Shortcode::register_shortcode();
			}
		}
	}

	private function load_textdomain() {
		load_plugin_textdomain( 'password-protected-categories', false, dirname( PPC_PLUGIN_BASENAME ) . '/languages' );
	}

	public function register_scripts() {
		$suffix = $this->get_script_suffix();
		wp_enqueue_style( 'ppc-style', plugins_url( "assets/css/ppc{$suffix}.css", self::FILE ), false, self::VERSION );
	}

	public function register_admin_scripts( $hook ) {
		$suffix = $this->get_script_suffix();

		if ( in_array( $hook, array( 'edit-tags.php', 'term.php' ) ) ) {
			wp_enqueue_style( 'ppc-admin', plugins_url( "assets/css/ppc-admin{$suffix}.css", self::FILE ), false, self::VERSION );
			wp_enqueue_script( 'ppc-admin', plugins_url( "assets/js/ppc-admin{$suffix}.js", self::FILE ), array( 'jquery' ), self::VERSION, true );

			wp_localize_script( 'ppc-admin', 'ppc_params', array(
				'confirm_delete' => __( 'Are you sure you want to remove this password?', 'password-protected-categories' )
			) );
		}
	}

	private function check_min_requirements() {
		global $wp_version;

		if ( version_compare( $wp_version, '4.4', '<' ) ) {
			if ( is_admin() ) {
				add_action( 'admin_notices', array( $this, 'invalid_wp_notice' ) );
			}
			return false;
		}
		return true;
	}

	public function invalid_wp_notice() {
		global $pagenow;

		if ( 'plugins.php' === $pagenow || ( 'options-general.php' === $pagenow && PPC_Admin_Settings_Page::MENU_SLUG === filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING ) ) ) {
			$message = sprintf(
				__( 'Warning: %1$s requires WordPress version 4.4 or greater. Please %2$supdate%3$s your WordPress installation.', 'password-protected-categories' ), '<strong>' . self::NAME . '</strong>', sprintf( '<a href="%s">', esc_url( self_admin_url( 'update-core.php' ) ) ), '</a>'
			);
			printf( '<div class="notice notice-error is-dismissible"><p>%1$s</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">%s</span></button></div>', $message, __( 'Dismiss this notice.', 'password-protected-categories' ) );
		}
	}

	private function is_front_end() {
		return ! is_admin() || defined( 'DOING_AJAX' );
	}

	private function get_script_suffix() {
		return defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	}

}
// class Password_Protected_Categories_Plugin

/**
 * @deprecated 1.2.1 - Renamed Password_Protected_Categories_Plugin
 */
if ( ! class_exists( 'Password_Protected_Categories' ) ) {

	class Password_Protected_Categories extends Password_Protected_Categories_Plugin {

	}
}

/*
 * Load the plugin
 */
Password_Protected_Categories_Plugin::instance();
