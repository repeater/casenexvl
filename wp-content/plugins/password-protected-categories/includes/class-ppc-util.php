<?php
// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Utility functions for Password Protected Categories.
 *
 * @package   Password_Protected_Categories
 * @author    Barn2 Media <info@barn2.co.uk>
 * @license   GPL-3.0
 * @link      https://barn2.co.uk
 * @copyright 2016-2018 Barn2 Media Ltd
 */
class PPC_Util {

	const OPTION_NAME = 'ppc_options';

	private static $options = false;

	public static function default_options() {
		return array(
			'password_expires'		 => 10,
			'show_protected'		 => false,
			'form_title'			 => __( 'Login Required', 'password-protected-categories' ),
			'form_button'			 => __( 'Login', 'password-protected-categories' ),
			'form_label'			 => __( 'Password: ', 'password-protected-categories' ),
			'form_label_placeholder' => false,
			'form_message'			 => __( 'This content is password protected. To view it please enter your password below:', 'password-protected-categories' )
		);
	}

	/**
	 * Retrieve the plugin options.
	 *
	 * @return array The plugin options array
	 */
	public static function get_options() {
		if ( false === self::$options ) {
			self::$options = wp_parse_args( get_option( self::OPTION_NAME, array() ), self::default_options() );
		}
		return self::$options;
	}

	/**
	 * Update the plugin options.
	 *
	 * @param array $options The complete list of updated options.
	 */
	public static function update_options( $options ) {
		update_option( self::OPTION_NAME, $options );
	}

	/**
	 * Retrive a specific plugin option using the $option key specified.
	 *
	 * @param string $option The option key
	 * @return mixed The option if set, or the default option
	 */
	public static function get_option( $option ) {
		$options = self::get_options();
		$value	 = isset( $options[$option] ) ? $options[$option] : '';

		// Back-compat: old checkbox settings were saved as 'yes'/'no'
		if ( 'yes' === $value ) {
			$value = true;
		} elseif ( 'no' === $value ) {
			$value = false;
		}

		return $value;
	}

	/**
	 * Get the option name to use in the "name" attributes for form fields on the plugin settings page.
	 *
	 * @param string $option_key The option key to format
	 * @return The formatted option name
	 */
	public static function get_option_name( $option_key ) {
		return sprintf( "%s[%s]", self::OPTION_NAME, $option_key );
	}

	/**
	 * Returns the currently unlocked category or term (if any) as an array in the following format:
	 *
	 * array(
	 *     'term_id' => 12
	 *     'taxonomy' => 'category'
	 * )
	 *
	 * @return boolean|array The taxonomy array or false if no terms are currently unlocked.
	 */
	public static function get_unlocked_term() {
		//@todo Use unique cookie for each category
		if ( ! isset( $_COOKIE[PPC_COOKIE_PREFIX . COOKIEHASH] ) ) {
			return false;
		}

		$tax_term = explode( '|', $_COOKIE[PPC_COOKIE_PREFIX . COOKIEHASH], 3 );

		if ( 3 !== count( $tax_term ) ) {
			return false;
		}

		return array(
			'term_id'	 => (int) $tax_term[0],
			'taxonomy'	 => $tax_term[1],
			'password'	 => $tax_term[2]
		);
	}

	/**
	 * Wrapper function to get_terms() to handle parameter change in WP 4.5.
	 *
	 * @global string $wp_version
	 * @param string $args The $args to pass to get_terms()
	 * @return array An array of WP_Term objects or an empty array if none found
	 */
	public static function get_terms( $args = array() ) {
		global $wp_version;

		if ( empty( $args['taxonomy'] ) ) {
			$args['taxonomy'] = '';
		}
		// Arguments for get_terms() changed in WP 4.5
		if ( version_compare( $wp_version, '4.5', '>=' ) ) {
			$terms = get_terms( $args );
		} else {
			$tax	 = $args['taxonomy'];
			unset( $args['taxonomy'] );
			$terms	 = get_terms( $tax, $args );
		}

		if ( is_array( $terms ) ) {
			return $terms;
		} else {
			return array();
		}
	}

	/**
	 * Retrieve an array of all hidden terms for the current user. If the user can
	 * view private posts (e.g. user is an administrator) then these terms will not be
	 * included in the result.
	 *
	 * If the user has previously unlocked a term, then this will be excluded from the result.
	 *
	 * @param array $taxonomies A list of taxonomies for which to retrieve the hidden terms
	 * @param $fields The fields to retrieve. @see get_terms()
	 * @return array An array of WP_Term objects or an empty array if none found
	 */
	public static function get_hidden_terms( $taxonomies, $fields = 'all' ) {
		$visibility = array();

		if ( ! self::get_option( 'show_protected' ) ) {
			$visibility[] = 'password';
		}
		if ( ! current_user_can( 'read_private_posts' ) ) {
			$visibility[] = 'private';
		}
		if ( ! $visibility || ! $taxonomies ) {
			return array();
		}

		$unlocked = self::get_unlocked_term();

		return self::get_terms( array(
				'ppc_check'	 => true,
				'taxonomy'	 => $taxonomies,
				'fields'	 => $fields,
				'hide_empty' => true,
				'exclude'	 => $unlocked ? $unlocked['term_id'] : array(),
				'meta_query' => array(
					array(
						'key'		 => 'visibility',
						'value'		 => $visibility,
						'compare'	 => 'IN'
					)
				)
			) );
	}

	/**
	 * Get an array of password protected terms for the specified taxonomies. If no taxonomies passed,
	 * retrieve all password protected terms.
	 *
	 * @param array $taxonomies An array of taxonomy names (optional)
	 * @return array An array of password protected terms (WP_Term objects)
	 */
	public static function get_password_protected_terms( $taxonomies = array() ) {

		$taxonomies = $taxonomies ? array_intersect( $taxonomies, self::get_protectable_taxonomies() ) : self::get_protectable_taxonomies();

		if ( ! $taxonomies ) {
			return array();
		}

		return self::get_terms( array(
				'ppc_check'	 => true,
				'taxonomy'	 => $taxonomies,
				'fields'	 => 'all',
				'hide_empty' => false,
				'meta_query' => array(
					array(
						'key'		 => 'visibility',
						'value'		 => 'password',
						'compare'	 => '='
					)
				)
			) );
	}

	/**
	 * Retrieve a list of all protectable taxonomies from the list of currently registered taxonomies.
	 *
	 * @return array A list of taxonomy names which can be protected by the plugin
	 */
	public static function get_protectable_taxonomies() {
		$taxonomies = get_taxonomies( array( 'hierarchical' => true ) );

		if ( self::wc_ppc_active() ) {
			$taxonomies = array_diff( $taxonomies, array( 'product_cat' ) );
		}
		return $taxonomies;
	}

	/**
	 * Retrieve the PPC_Term_Visibility instance for the specified term.
	 *
	 * @param WP_Term $term The term to retrieve the visibility for
	 * @return boolean|PPC_Term_Visibility
	 */
	public static function get_term_visibility( $term ) {
		if ( ! ( $term instanceof WP_Term ) ) { return false; }

		$cache = self::get_term_visibility_cache();

		if ( ! array_key_exists( $term->term_id, $cache ) ) {
			$cache[$term->term_id] = new PPC_Term_Visibility( $term );
			self::update_term_visibility_cache( $cache );
		}
		return $cache[$term->term_id];
	}

	/**
	 * Retrive a list of PPC_Term_Visibility objects for the specified post.
	 * If no post is specified, the current post object is used.
	 *
	 * @param int|WP_Post $post The post ID or post object
	 * @param string $taxonomy The taxonomy to retrive visibilites for, or false to retrieve all applicable taxonomies
	 * @return array An array of PPC_Term_Visibility objects
	 */
	public static function get_the_term_visibility( $post = null, $taxonomy = false ) {

		$post = $post ? get_post( $post ) : get_queried_object();

		// Bail if no post
		if ( ! ( $post instanceof WP_Post ) ) {
			return array();
		}

		// Defer to WooCommerce PPC if it's also active.
		if ( 'product' === $post->post_type && self::wc_ppc_active() ) {
			return array();
		}

		$terms = array();

		if ( ! $taxonomy || ! taxonomy_exists( $taxonomy ) ) {
			$taxonomies = array_intersect( get_object_taxonomies( $post ), self::get_protectable_taxonomies() );

			if ( $taxonomies ) {
				foreach ( $taxonomies as $taxonomy ) {
					$post_terms = get_the_terms( $post, $taxonomy );
					if ( $post_terms && is_array( $post_terms ) ) {
						$terms = array_merge( $terms, $post_terms );
					}
				}
			}
		} else {
			$terms = get_the_terms( $post, $taxonomy );
		}

		return self::to_term_visibilities( $terms );
	}

	public static function to_term_visibilities( $terms ) {
		if ( ! $terms || ! is_array( $terms ) ) {
			return array();
		}

		$result	 = array();
		$cache	 = self::get_term_visibility_cache();

		foreach ( $terms as $term ) {
			if ( array_key_exists( $term->term_id, $cache ) ) {
				$result[] = $cache[$term->term_id];
			} else {
				$cache[$term->term_id]	 = new PPC_Term_Visibility( $term );
				$result[]				 = $cache[$term->term_id];
			}
		}

		self::update_term_visibility_cache( $cache );

		return $result;
	}

	/**
	 * Is this a hidden post (i.e. private or password protected)?
	 * Defaults to the current post if not specified.
	 *
	 * @param int|WP_Post $post Post ID or WP_Post object
	 * @param boolean $show_if_protected Whether to show the post if its password protected
	 * @return boolean
	 */
	public static function is_hidden_post( $post = null ) {

		if ( $visibilities = self::get_the_term_visibility( $post ) ) {
			foreach ( $visibilities as $visibility ) {
				if ( $visibility->is_hidden() ) {
					return true;
				}
			}
		}
		return false;
	}

	public static function get_term_visibility_cache() {
		$cache = wp_cache_get( 'ppc_visibilities' );

		if ( false !== $cache || ! is_array( $cache ) ) {
			$cache = array();
		}
		return $cache;
	}

	public static function update_term_visibility_cache( $term_visibilities ) {
		wp_cache_set( 'ppc_visibilities', $term_visibilities );
	}

	/**
	 * Is our WooCommerce Password Protected Categories plugin activated?
	 *
	 * @return boolean
	 */
	public static function wc_ppc_active() {
		return class_exists( 'WC_Password_Protected_Categories_Plugin' );
	}

}
// end class PPC_Util
