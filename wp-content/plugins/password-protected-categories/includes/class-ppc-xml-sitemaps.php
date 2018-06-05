<?php
// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Protection for XML sitemaps.
 *
 * @package   Password_Protected_Categories
 * @author    Barn2 Media <info@barn2.co.uk>
 * @license   GPL-3.0
 * @link      https://barn2.co.uk
 * @copyright 2016-2018 Barn2 Media Ltd
 */
class PPC_XML_Sitemaps {

	private $taxonomies = false;

	public function __construct() {
		$this->protect_sitemaps();
	}

	private function protect_sitemaps() {
		/*
		 * @todo: Other sitemap plugins
		 */

		// All in One SEO Pack
		if ( defined( 'AIOSEOP_VERSION' ) && ! is_admin() ) {
			add_filter( 'option_aioseop_options', array( $this, 'aioseop_excludes' ) );
		}

		// WordPress SEO
		add_action( 'admin_init', array( $this, 'wpseo_maybe_clear_cache' ) );
		add_filter( 'wpseo_sitemap_entry', array( $this, 'wpseo_exclude_protected_terms' ), 10, 3 );

		do_action( 'ppc_sitemap_hooks_registered', $this );
	}

	public function aioseop_excludes( $value ) {

		if ( isset( $value['modules']['aiosp_sitemap_options'] ) ) {
			$sitemap_options = $value['modules']['aiosp_sitemap_options'];

			// Get all hidden term IDs
			$excluded_term_ids = PPC_Util::get_hidden_terms( $this->get_taxonomies(), 'ids' );

			// Exclude hidden terms
			if ( isset( $sitemap_options['aiosp_sitemap_excl_categories'] ) ) {
				$sitemap_options['aiosp_sitemap_excl_categories'] = array_unique( array_merge( $sitemap_options['aiosp_sitemap_excl_categories'], $excluded_term_ids ) );
			}

			// Exclude hidden posts
			if ( isset( $sitemap_options['aiosp_sitemap_excl_pages'] ) ) {

				// Get all hidden post IDs
				$post_excludes = get_objects_in_term( $excluded_term_ids, $this->get_taxonomies() );

				if ( $post_excludes && is_array( $post_excludes ) ) {

					if ( ! empty( $sitemap_options['aiosp_sitemap_excl_pages'] ) ) {
						$post_excludes = array_merge( $post_excludes, explode( ',', str_replace( ' ', '', $sitemap_options['aiosp_sitemap_excl_pages'] ) ) );
					}

					$sitemap_options['aiosp_sitemap_excl_pages'] = array_unique( $post_excludes );
				}
			}

			$value['modules']['aiosp_sitemap_options'] = $sitemap_options;
		}

		return $value;
	}

	public function wpseo_maybe_clear_cache() {
		$cache_cleared = get_option( 'ppc_wpseo_cache_cleared' );

		// Clear WPSEO cache once when PPC is first run. If WPSEO is later deactivated, update option.
		if ( defined( 'WPSEO_VERSION' ) && 'wpseo' !== $cache_cleared ) {
			self::clear_wpseo_sitemap_cache();
			update_option( 'ppc_wpseo_cache_cleared', 'wpseo' );
		} elseif ( 'nowpseo' !== $cache_cleared ) {
			update_option( 'ppc_wpseo_cache_cleared', 'nowpseo' );
		}
	}

	public static function clear_wpseo_sitemap_cache() {
		if ( class_exists( 'WPSEO_Sitemaps_Cache_Validator' ) ) {
			WPSEO_Sitemaps_Cache_Validator::invalidate_storage();
		}
	}

	public function wpseo_exclude_protected_terms( $url, $type, $obj ) {

		if ( 'term' === $type && $obj instanceof WP_Term && in_array( $obj->taxonomy, $this->get_taxonomies() ) ) {
			$term_visibility = PPC_Util::get_term_visibility( $obj );

			if ( $term_visibility->is_hidden() ) {
				return false;
			}
		} elseif ( 'post' === $type && $obj instanceof WP_Post ) {
			if ( PPC_Util::is_hidden_post( $obj ) ) {
				return false;
			}
		}

		return $url;
	}

	private function get_taxonomies() {
		if ( false === $this->taxonomies ) {
			$this->taxonomies = PPC_Util::get_protectable_taxonomies();
		}
		return $this->taxonomies;
	}

}
// class PPC_XML_Sitemaps
