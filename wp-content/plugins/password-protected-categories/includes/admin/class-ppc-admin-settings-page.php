<?php
// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles our plugin settings page.
 *
 * @package   Password_Protected_Categories/Admin
 * @author    Barn2 Media <info@barn2.co.uk>
 * @license   GPL-3.0
 * @link      https://barn2.co.uk
 * @copyright 2016-2018 Barn2 Media Ltd
 */
class PPC_Admin_Settings_Page {

	const MENU_SLUG = 'protected_categories';

	const OPTION_GROUP = 'ppc_main_group';

	private $license;

	public function __construct( $license ) {
		$this->license = $license;

		// Link to settings from Plugins page.
		add_filter( 'plugin_action_links_' . PPC_PLUGIN_BASENAME, array( $this, 'add_plugin_settings_link' ) );
		add_filter( 'plugin_row_meta', array( $this, 'add_plugin_action_links' ), 10, 2 );

		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_plugin_settings_link( $links ) {
		$settings_link = '<a href="' . admin_url( sprintf( 'options-general.php?page=%s', PPC_Admin_Settings_Page::MENU_SLUG ) ) . '">' . __( 'Settings', 'password-protected-categories' ) . '</a>';
		array_unshift( $links, $settings_link );

		return $links;
	}

	public function add_plugin_action_links( $links, $file ) {
		if ( PPC_PLUGIN_BASENAME === $file ) {
			$link_fmt	 = '<a href="%1$s" aria-label="%2$s" target="_blank">%3$s</a>';
			$row_meta	 = array(
				'docs' => sprintf( $link_fmt, $this->barn2_url( 'kb-categories/ppc-kb/' ), esc_attr__( 'View documentation for Password Protected Categories', 'password-protected-categories' ), esc_html__( 'Docs', 'password-protected-categories' ) )
			);

			return array_merge( $links, $row_meta );
		}

		return (array) $links;
	}

	public function add_settings_page() {
		add_options_page(
			__( 'Password Protected Categories ', 'password-protected-categories' ), __( 'Protected Categories', 'password-protected-categories' ), 'manage_options', self::MENU_SLUG, array( $this, 'render_settings_page' )
		);
	}

	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php _e( 'Password Protected Categories', 'password-protected-categories' ); ?></h1>
			<form action="options.php" method="post">
				<?php
				// Output the hidden form fields (_wpnonce, etc)
				settings_fields( self::OPTION_GROUP );

				// Output the sections and their settings
				do_settings_sections( self::MENU_SLUG );
				?>
				<p class="submit">
					<input name="Submit" type="submit" name="submit" class="button button-primary" value="<?php esc_attr_e( 'Save Changes', 'password-protected-categories' ); ?>" />
				</p>
			</form>
		</div>
		<?php
	}

	public function register_settings() {

		register_setting( self::OPTION_GROUP, $this->license->license_key_option, array(
			'type'				 => 'string',
			'description'		 => 'Password Protected Categories - license key',
			'sanitize_callback'	 => array( $this->license, 'save' )
		) );
		register_setting( self::OPTION_GROUP, PPC_Util::OPTION_NAME, array(
			'type'				 => 'string', // array type not supported, so just use string
			'description'		 => 'Password Protected Categories - options',
			'sanitize_callback'	 => array( $this, 'save_options' )
		) );

		// Licence key section
		WP_Settings_API_Helper::add_settings_section(
			'ppc_license_key', self::MENU_SLUG, '', array( $this, 'section_description_license_key' ), array(
			array(
				'id'	 => $this->license->license_key_option,
				'title'	 => __( 'License Key', 'password-protected-categories' ),
				'type'	 => 'text',
				'desc'	 => $this->license->get_license_key_admin_message(),
			)
			)
		);

		// Main settings section
		$defaults = PPC_Util::default_options();

		WP_Settings_API_Helper::add_settings_section(
			'ppc_protection', self::MENU_SLUG, __( 'Category Protection', 'password-protected-categories' ), false, array(
			array(
				'id'				 => PPC_Util::get_option_name( 'password_expires' ),
				'title'				 => __( 'Password Expiration', 'password-protected-categories' ),
				'type'				 => 'number',
				'desc'				 => __( 'How long the category remains unlocked before the password expires.', 'password-protected-categories' ),
				'suffix'			 => __( 'days', 'password-protected-categories' ),
				'custom_attributes'	 => array(
					'min' => 1
				),
				'default'			 => $defaults['password_expires'],
			),
			array(
				'id'		 => PPC_Util::get_option_name( 'show_protected' ),
				'title'		 => __( 'Show Categories?', 'password-protected-categories' ),
				'type'		 => 'checkbox',
				'label'		 => __( 'Show password protected categories and posts to visitors', 'password-protected-categories' ),
				'desc'		 => __( 'Tick this to show protected categories in public areas of your site (e.g. sidebars and navigation menus). Untick to hide them from public view.', 'password-protected-categories' ),
				'default'	 => $defaults['show_protected'],
			)
		) );

		WP_Settings_API_Helper::add_settings_section(
			'ppc_login_form', self::MENU_SLUG, __( 'Login Form', 'password-protected-categories' ), array( $this, 'section_description_login_form' ), array(
			array(
				'id'		 => PPC_Util::get_option_name( 'form_title' ),
				'title'		 => _x( 'Title', 'title for the login page', 'password-protected-categories' ),
				'type'		 => 'text',
				'desc'		 => __( 'The title for the login page.', 'password-protected-categories' ),
				'default'	 => $defaults['form_title'],
			),
			array(
				'id'		 => PPC_Util::get_option_name( 'form_message' ),
				'title'		 => __( 'Form Message', 'password-protected-categories' ),
				'type'		 => 'textarea',
				'desc'		 => __( 'The message to appear above the login form. You can use HTML here.', 'password-protected-categories' ),
				'default'	 => $defaults['form_message'],
				'class'		 => 'regular-text',
				'rows'		 => 5
			),
			array(
				'id'		 => PPC_Util::get_option_name( 'form_label' ),
				'title'		 => __( 'Password Label', 'password-protected-categories' ),
				'type'		 => 'text',
				'desc'		 => __( 'The label shown next to the password box.', 'password-protected-categories' ),
				'default'	 => $defaults['form_label']
			),
			array(
				'id'		 => PPC_Util::get_option_name( 'form_label_placeholder' ),
				'title'		 => __( 'Placeholder?', 'password-protected-categories' ),
				'type'		 => 'checkbox',
				'label'		 => __( 'Use the password label as a placeholder', 'password-protected-categories' ),
				'desc'		 => __( 'A placeholder appears inside the box rather than next to it.', 'password-protected-categories' ),
				'default'	 => $defaults['form_label_placeholder']
			),
			array(
				'id'		 => PPC_Util::get_option_name( 'form_button' ),
				'title'		 => __( 'Button Text', 'password-protected-categories' ),
				'type'		 => 'text',
				'desc'		 => __( 'The text for the login button.', 'password-protected-categories' ),
				'default'	 => $defaults['form_button']
			),
		) );
	}

	public function section_description_license_key() {
		$this->settings_page_support_links();

		if ( filter_input( INPUT_GET, 'license_debug', FILTER_VALIDATE_BOOLEAN ) ) {
			echo '<input type="hidden" name="license_debug" value="1" />';
		}
		if ( $override = filter_input( INPUT_GET, 'license_override', FILTER_SANITIZE_STRING ) ) {
			echo '<input type="hidden" name="license_override" value="' . esc_attr( $override ) . '" />';
		}
	}

	public function section_description_login_form() {
		?>
		<p><?php _e( 'Use the settings below to customise the login form displayed on password protected categories and posts.', 'password-protected-categories' ); ?></p>
		<?php
	}

	public function settings_page_support_links() {
		?>
		<p>
			<?php
			echo $this->barn2_link( 'kb-categories/ppc-getting-started/', __( 'Getting Started', 'password-protected-categories' ) );
			echo ' | ';
			echo $this->barn2_link( 'kb-categories/ppc-kb/', __( 'Knowledge Base', 'password-protected-categories' ) );
			?>
		</p>
		<?php
	}

	public function save_options( $options ) {

		if ( ! empty( $options['form_message'] ) ) {
			$options['form_message'] = wp_kses_post( $options['form_message'] );
		}
		if ( ! empty( $options['form_label'] ) ) {
			$options['form_label'] = wp_strip_all_tags( $options['form_label'] );
		}
		if ( ! empty( $options['form_button'] ) ) {
			$options['form_button'] = wp_strip_all_tags( $options['form_button'] );
		}
		$options['password_expires'] = absint( $options['password_expires'] );

		if ( empty( $options['password_expires'] ) ) {
			$defaults					 = PPC_Util::default_options();
			$options['password_expires'] = $defaults['password_expires'];
		}

		return $options;
	}

	private static function barn2_url( $path ) {
		return esc_url( 'https://barn2.co.uk/' . ltrim( $path, '/' ) );
	}

	private static function barn2_link( $path, $link_text ) {
		return sprintf( '<a href="%s" target="_blank">%s</a>', self::barn2_url( $path ), $link_text );
	}

	private static function read_more( $path ) {
		return sprintf( ' %s', self::barn2_link( $path, __( 'Read more', 'password-protected-categories' ) ) );
	}

}