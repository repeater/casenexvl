<?php
// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class provides the main functions to lock down protected categories on the front end.
 *
 * This includes removing categories (and posts belonging to these categories) from various locations,
 * such as the search results, posts page, archives, navigation menus, and widgets.
 *
 * @package   Password_Protected_Categories
 * @author    Barn2 Media <info@barn2.co.uk>
 * @license   GPL-3.0
 * @link      https://barn2.co.uk
 * @copyright 2016-2018 Barn2 Media Ltd
 */
class PPC_Term_Protector {

	private $password_protected = false;

	private $private = false;

	private $tax_queries = array();

	private $hidden_term_ids = false;

	public function __construct() {
		$this->protection_hooks();
	}

	/**
	 * Hooks for filtering posts, categories, nav menus, etc
	 */
	public function protection_hooks() {

		// Adjust query to exclude posts in private or password protected categories
		add_action( 'pre_get_posts', array( $this, 'pre_get_posts' ) );

		// Spoof the query (for password protected categories/posts) or return 404 template (for private categories/posts).
		add_action( 'template_redirect', array( $this, 'template_redirect' ), 11 );

		// Remove private and password protected categories (and their posts) from nav menus
		add_filter( 'wp_get_nav_menu_items', array( $this, 'protect_nav_menus' ), 10, 3 );

		// Adjust query for 'get_terms' to exclude private and password protected categories
		add_filter( 'get_terms_args', array( $this, 'get_terms_args' ), 10, 2 );

		do_action( 'ppc_term_protection_hooks_registered', $this );
	}

	public function pre_get_posts( &$query ) {

		if ( $query->is_singular() || $query->is_404() || $query->is_preview() || $query->is_robots() || $query->is_embed() || $query->is_trackback() || $query->is_comment_feed() ) {
			return;
		}

		$is_protected_tax = false;

		if ( $query->is_main_query() && ( $query->is_category() || $query->is_tax( PPC_Util::get_protectable_taxonomies() ) ) ) {
			// Category or custom taxonomy archive
			$term = get_queried_object();

			if ( ! ( $term instanceof WP_Term ) ) {
				return;
			}

			$term_visibility = PPC_Util::get_term_visibility( $term );

			if ( $term_visibility->is_password_protected() ) {
				$this->password_protected = true;
			} elseif ( $term_visibility->is_private() ) {
				$this->private = true;
			}

			$is_protected_tax = $this->password_protected || $this->private;
		}

		// If not the main query, or we're not viewing a protected taxonomy archive, we need to exclude posts in hidden categories from results.
		if ( ! $query->is_main_query() || ! $is_protected_tax ) {
			$query->query_vars = $this->build_tax_query( $query->query_vars );
		}
	}

	/**
	 * We check for single pages/posts on template_redirect as this needs to happen after $wp_the_query->query_posts() has completed.
	 * Also needs to run after PPC_Protected_Term_Login::process_login() which may have set a password cookie.
	 *
	 * @global WP $wp Main WP instance
	 * @global WP_Query $wp_query The main query
	 */
	public function template_redirect() {
		global $wp, $wp_query;

		if ( is_singular() ) {
			// Single post/page check
			foreach ( PPC_Util::get_the_term_visibility() as $term_visibility ) {
				if ( $term_visibility->is_private() ) {
					$this->private = true;
					break; // break for private category, but not password protected as there may be a private category to come
				} elseif ( $term_visibility->is_password_protected() ) {
					$this->password_protected = true;
				}
			}
		}

		// Check for private category first as this takes precedence
		if ( $this->private ) {
			$wp_query->is_404 = true;
			$wp->handle_404();
		} elseif ( $this->password_protected ) {
			// Add nocache headers
			nocache_headers();

			// noindex this page - we add X-Robots-Tag header and set meta robots
			@header( 'X-Robots-Tag: noindex, nofollow' );
			add_action( 'wp_head', array( $this, 'meta_robots_noindex_head' ), 5 );

			// Spoof main query with dummy login page
			$this->spoof_main_query();

			// Plugin-specific checks
			$this->plugin_compat();
		}
	}

	public function meta_robots_noindex_head() {
		echo '<meta name="robots" content="noindex, nofollow" />' . "\n";
	}

	public function protect_nav_menus( $menu_items, $menu, $args ) {
		$removed_items			 = array();
		$protectable_taxonomies	 = PPC_Util::get_protectable_taxonomies();

		foreach ( $menu_items as $key => $menu_item ) {

			if ( 'taxonomy' === $menu_item->type && in_array( $menu_item->object, $protectable_taxonomies ) ) {
				if ( $term = get_term_by( 'id', $menu_item->object_id, $menu_item->object ) ) {
					$term_visibility = PPC_Util::get_term_visibility( $term );

					if ( $term_visibility->is_hidden() ) {
						$removed_items[] = $menu_item->ID;
						unset( $menu_items[$key] );
					}
				}
			} elseif ( 'post_type' === $menu_item->type ) {
				if ( PPC_Util::is_hidden_post( $menu_item->object_id ) ) {
					$removed_items[] = $menu_item->ID;
					unset( $menu_items[$key] );
				}
			}
		} // foreach menu item
		// Now find and remove any children of any removed menu item
		while ( $removed_items ) {
			$child_items_removed = array();

			foreach ( $menu_items as $key => $menu_item ) {
				if ( in_array( $menu_item->menu_item_parent, $removed_items ) ) {
					$child_items_removed[] = $menu_item->ID;
					unset( $menu_items[$key] );
				}
			}
			// Update the removed list with the removed child items and start over
			$removed_items = $child_items_removed;
		}

		return array_values( $menu_items );
	}

	public function get_terms_args( $args, $taxonomies ) {

		// Bail if our internal flag is set
		if ( isset( $args['ppc_check'] ) ) {
			return $args;
		}

		// Bail if we're getting the terms for one or more objects
		if ( ! empty( $args['object_ids'] ) ) {
			return $args;
		}

		// Bail if 'get' => 'all' set (e.g. when getting the term hierarchy or get_term_by call)
		if ( 'all' === $args['get'] || 1 === $args['number'] ) {
			return $args;
		}

		// Bail if get_terms() is calling itself (it does this when 'exclude_tree' is set), to avoid an infinite loop.
		// When calling itself, 'child_of' => parent, 'fields' => 'ids' and 'hide_empty' => 0.
		if ( ! empty( $args['child_of'] ) && 'ids' === $args['fields'] && ! $args['hide_empty'] ) {
			return $args;
		}

		// Bail if we're fetching terms for taxonomies which are not protectable
		if ( $taxonomies && ! array_intersect( $taxonomies, PPC_Util::get_protectable_taxonomies() ) ) {
			return $args;
		}

		$args['exclude_tree'] = $this->get_hidden_term_ids();
		return $args;
	}

	private function get_hidden_term_ids() {
		if ( false === $this->hidden_term_ids ) {
			$this->hidden_term_ids = PPC_Util::get_hidden_terms( PPC_Util::get_protectable_taxonomies(), 'ids' );
		}
		return $this->hidden_term_ids;
	}

	private function build_tax_query( $query_vars ) {

		$taxonomies = PPC_Util::get_protectable_taxonomies();

		if ( ! empty( $query_vars['post_type'] ) ) {
			$post_type = $query_vars['post_type'];

			if ( in_array( $post_type, array( 'revision', 'nav_menu_item' ) ) ) {
				return $query_vars;
			}
			if ( 'any' === $query_vars['post_type'] ) {
				$post_type = get_post_types( array( 'exclude_from_search' => false ) );
			}

			$taxonomies = array_intersect( get_object_taxonomies( $post_type, 'names' ), $taxonomies );
		}

		if ( ! $taxonomies ) {
			// No password protectable taxonomies found, so return query vars
			return $query_vars;
		}

		$tax_query		 = array();
		$taxonomies_hash = md5( json_encode( $taxonomies ) );

		// Check tax query cache first
		if ( isset( $this->tax_queries[$taxonomies_hash] ) ) {
			$tax_query = $this->tax_queries[$taxonomies_hash];
		} else {
			// Not in cache, so we need to query terms
			$taxonomy_terms	 = array();
			$hidden_terms	 = PPC_Util::get_hidden_terms( $taxonomies, 'all' );

			foreach ( $hidden_terms as $term ) {
				if ( array_key_exists( $term->taxonomy, $taxonomy_terms ) ) {
					$taxonomy_terms[$term->taxonomy][] = $term->term_id;
				} else {
					$taxonomy_terms[$term->taxonomy] = array( $term->term_id );
				}
			}

			foreach ( $taxonomy_terms as $taxonomy => $term_ids ) {
				$tax_query[] = array(
					'taxonomy'			 => $taxonomy,
					'field'				 => 'term_id',
					'terms'				 => $term_ids,
					'operator'			 => 'NOT IN',
					'include_children'	 => true
				);
			}

			if ( count( $tax_query ) > 1 ) {
				$tax_query['relation'] = 'AND';
			}
			$this->tax_queries[$taxonomies_hash] = $tax_query;
		}

		if ( $tax_query ) {
			// If there's already a tax query present, wrap it our query and set as AND relation
			if ( ! empty( $query_vars['tax_query'] ) ) {
				$tax_query = array(
					'relation' => 'AND',
					array( $tax_query ),
					array( $query_vars['tax_query'] )
				);
			}

			$query_vars['tax_query'] = $tax_query;
		}

		return $query_vars;
	}

	private function spoof_main_query() {
		global $wp, $wp_query;

		$post_id				 = 0; // to avoid clash with a valid post
		$post					 = new stdClass();
		$post->ID				 = $post_id;
		$post->post_author		 = 1;
		$post->post_date		 = current_time( 'mysql' );
		$post->post_date_gmt	 = current_time( 'mysql', 1 );
		$post->post_status		 = 'publish';
		$post->comment_status	 = 'closed';
		$post->ping_status		 = 'closed';
		$post->post_type		 = 'page';
		$post->filter			 = 'raw'; // important
		$post->post_name		 = 'term-login-' . rand( 1, 99999 ); // append random number to avoid clash
		$post->post_title		 = PPC_Util::get_option( 'form_title' );
		$post->post_content		 = '[' . PPC_Login_Shortcode::SHORTCODE . ']';

		// Convert to WP_Post object
		$wp_post = new WP_Post( $post );

		// Add our fake post to the cache
		wp_cache_add( $post_id, $wp_post, 'posts' );
		wp_cache_add( $post_id, array( true ), 'post_meta' );

		// Override main query
		$wp_query->post					 = $wp_post;
		$wp_query->posts				 = array( $wp_post );
		$wp_query->queried_object		 = $wp_post;
		$wp_query->queried_object_id	 = $wp_post->ID;
		$wp_query->found_posts			 = 1;
		$wp_query->post_count			 = 1;
		$wp_query->max_num_pages		 = 1;
		$wp_query->is_singular			 = true;
		$wp_query->is_page				 = true;
		$wp_query->is_single			 = false;
		$wp_query->is_attachment		 = false;
		$wp_query->is_archive			 = false;
		$wp_query->is_category			 = false;
		$wp_query->is_tag				 = false;
		$wp_query->is_tax				 = false;
		$wp_query->is_author			 = false;
		$wp_query->is_date				 = false;
		$wp_query->is_year				 = false;
		$wp_query->is_month				 = false;
		$wp_query->is_day				 = false;
		$wp_query->is_time				 = false;
		$wp_query->is_search			 = false;
		$wp_query->is_feed				 = false;
		$wp_query->is_comment_feed		 = false;
		$wp_query->is_trackback			 = false;
		$wp_query->is_home				 = false;
		$wp_query->is_embed				 = false;
		$wp_query->is_404				 = false;
		$wp_query->is_paged				 = false;
		$wp_query->is_admin				 = false;
		$wp_query->is_preview			 = false;
		$wp_query->is_robots			 = false;
		$wp_query->is_posts_page		 = false;
		$wp_query->is_post_type_archive	 = false;

		// Update globals
		$GLOBALS['wp_query'] = $wp_query;
		$wp->register_globals();
	}

	private function plugin_compat() {
		// Prevent The Events Calendar template stuff running
		remove_filter( 'template_include', array( 'Tribe__Events__Templates', 'templateChooser' ) );
		remove_action( 'template_redirect', 'tribe_initialize_view' );
		remove_action( 'wp_head', array( 'Tribe__Events__Templates', 'maybeSpoofQuery' ), 100 );
		remove_action( 'tribe_tec_template_chooser', array( 'Tribe__Events__Templates', 'maybe_modify_global_post_title' ) );
	}

}
// class PPC_Term_Protecter

