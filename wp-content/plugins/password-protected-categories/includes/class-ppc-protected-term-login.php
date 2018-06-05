<?php
// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class handles the display and processing of the category login shortcode.
 *
 * @package   Password_Protected_Categories
 * @author    Barn2 Media <info@barn2.co.uk>
 * @license   GPL-3.0
 * @link      https://barn2.co.uk
 * @copyright 2016-2018 Barn2 Media Ltd
 */
class PPC_Protected_Term_Login {

	private static $form_id = 1;

	private static $login_status;

	public static function handle_login() {
		// Handle login when form is submitted
		add_action( 'template_redirect', array( __CLASS__, 'process_login' ) );
	}

	public static function process_login() {
		if ( 'POST' !== $_SERVER['REQUEST_METHOD'] || ! filter_input( INPUT_POST, 'ppc_login', FILTER_VALIDATE_INT ) ) {
			return;
		}

		if ( ! ( $password = filter_input( INPUT_POST, 'post_password', FILTER_SANITIZE_STRING ) ) ) {
			self::$login_status = 'no_password';
			return;
		}

		$term_visibilities		 = array();
		$redirect_to_category	 = false;

		if ( is_category() || is_tax() ) {
			// Submitting from catetory or taxonomy archive
			$term_visibilities[] = PPC_Util::get_term_visibility( get_queried_object() );
		} elseif ( is_singular() ) {
			// Submitting from single post, page or attachment
			$term_visibilities = PPC_Util::get_the_term_visibility();
		}

		if ( ! $term_visibilities ) {
			// Submitting from page with [category_login] shortcode or from sidebar widget, so get all term visibilities
			$term_visibilities		 = PPC_Util::to_term_visibilities( PPC_Util::get_password_protected_terms() );
			$redirect_to_category	 = true;
		}

		if ( ! $term_visibilities ) {
			self::$login_status = 'no_categories_found';
			return;
		}

		foreach ( $term_visibilities as $term_vis ) {

			if ( $term = $term_vis->check_password( $password ) ) {
				// Valid password so set password cookie, then redirect
				self::set_password_cookie( $term, $password );
				self::update_cache( $term );

				if ( $redirect_to_category ) {
					wp_safe_redirect( get_term_link( $term ) );
				} else {
					// Redirect back to same page form was submitted from
					wp_safe_redirect( add_query_arg( null, null ) );
				}
				return;
			}
		}

		// If we got this far, the password must be wrong
		self::$login_status = 'invalid_password';
	}

	public static function display_password_form( $message = '' ) {
		echo self::get_password_form( $message );
	}

	public static function get_password_form( $message = '' ) {
		$form_message	 = $message ? wpautop( $message ) : '';
		$form_message	 .= self::get_form_login_message();

		$password_label	 = PPC_Util::get_option( 'form_label' );
		$placeholder	 = '';

		if ( $password_label ) {
			if ( PPC_Util::get_option( 'form_label_placeholder' ) ) {
				$placeholder	 = ' placeholder="' . esc_attr( $password_label ) . '"';
				$password_label	 = '';
			} else {
				$password_label .= ' ';
			}
		}

		$post_id		 = get_the_ID();
		$label_id		 = 'pwbox-' . ( $post_id ? $post_id : rand() );
		$container_class = apply_filters( 'ppc_category_login_form_container', 'ppc-login-form-container' );
		$form_class		 = apply_filters( 'ppc_login_form_class', 'ppc-login-form post-password-form' );
		$action			 = add_query_arg( null, null ); // the current page

		ob_start();
		do_action( 'ppc_before_category_login_form' );
		?>
		<div class="<?php echo esc_attr( $container_class ); ?>">
			<form action="<?php echo esc_url( $action ); ?>" class="<?php echo esc_attr( $form_class ); ?>" method="post">
				<?php echo $form_message; ?>
				<input type="hidden" name="ppc_login" value="<?php echo esc_attr( self::$form_id ); ?>" />
				<p>
					<label class="ppc-password-label" for="<?php echo esc_attr( $label_id ); ?>"><?php echo $password_label; ?><input name="post_password" id="<?php echo esc_attr( $label_id ); ?>" type="password" size="25"<?php echo $placeholder; ?> /></label>
					<input type="submit" name="Submit" value="<?php echo esc_attr( PPC_Util::get_option( 'form_button' ) ); ?>" />
				</p>
			</form>
		</div>
		<?php
		do_action( 'ppc_after_category_login_form' );

		self::$form_id ++;

		return apply_filters( 'ppc_category_login_form', ob_get_clean() );
	}

	private static function get_form_login_message() {

		if ( ! ( $submitted_form_id = filter_input( INPUT_POST, 'ppc_login', FILTER_VALIDATE_INT ) ) ) {
			return '';
		}

		if ( self::$form_id !== $submitted_form_id ) {
			return '';
		}

		$error = false;

		switch ( self::$login_status ) {
			case 'no_password':
				$error	 = __( 'Please enter a password.', 'password-protected-categories' );
				break;
			case 'no_categories_found':
				$error	 = __( 'There are no password protected categories available.', 'password-protected-categories' );
				break;
			case 'invalid_password':
				$error	 = __( 'Incorrect password, please try again.', 'password-protected-categories' );
				break;
		}

		return $error ? sprintf( '<p class="ppc-login-error">%s</p>', $error ) : '';
	}

	private static function set_password_cookie( $term, $password ) {
		require_once ABSPATH . WPINC . '/class-phpass.php';
		$hasher = new PasswordHash( 8, true );

		$password_expires = PPC_Util::get_option( 'password_expires' );

		if ( ! $password_expires ) {
			$password_expires = 10;
		}

		$expire	 = apply_filters( 'ppc_category_password_expires', time() + $password_expires * DAY_IN_SECONDS );
		$referer = wp_get_referer();

		if ( $referer ) {
			$secure = ( 'https' === parse_url( $referer, PHP_URL_SCHEME ) );
		} else {
			$secure = false;
		}

		$cookie_value = "{$term->term_id}|{$term->taxonomy}|" . $hasher->HashPassword( wp_unslash( $password ) );
		setcookie( PPC_COOKIE_PREFIX . COOKIEHASH, $cookie_value, $expire, COOKIEPATH, COOKIE_DOMAIN, $secure );
	}

	private static function update_cache( $term ) {
		$visibility_cache = PPC_Util::get_term_visibility_cache();

		if ( isset( $visibility_cache[$term->term_id] ) ) {
			unset( $visibility_cache[$term->term_id] );
			PPC_Util::update_term_visibility_cache( $visibility_cache );
		}
	}

}
// class PPC_Protected_Term_Login
