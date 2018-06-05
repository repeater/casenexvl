<?php
// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class represents a term (WP_Term instance) with various functions to test for its visibility.
 *
 * @package   Password_Protected_Categories
 * @author    Barn2 Media <info@barn2.co.uk>
 * @license   GPL-3.0
 * @link      https://barn2.co.uk
 * @copyright 2016-2018 Barn2 Media Ltd
 */
class PPC_Term_Visibility {

	public $term;

	public $visibility = false;

	private $ancestors = null;

	public function __construct( $term ) {
		$this->term			 = $term;
		$this->visibility	 = get_term_meta( $this->term->term_id, 'visibility', true );

		if ( ! $this->visibility ) {
			$this->visibility = 'public';
		}
	}

	/**
	 * Retrieve the ancestor terms for this term. Lazy loaded so it only hits the
	 * database once.
	 *
	 * @return array An array of PPC_Term_Visibility objects (one for each ancestor),
	 *      or an empty array if there are no ancestors
	 */
	public function ancestors() {
		if ( null === $this->ancestors ) {
			$ancestor_ids	 = get_ancestors( $this->term->term_id, $this->term->taxonomy, 'taxonomy' );
			$this->ancestors = array_filter( PPC_Util::to_term_visibilities( array_map( array( $this, 'to_term' ), $ancestor_ids ) ) );
		}

		return $this->ancestors;
	}

	/**
	 * Is the password valid for this term?
	 *
	 * @param string $password The password to check
	 * @param boolea $include_ancestors Whether to check the password against ancestor terms
	 * @return boolean|WP_Term The term the password is valid for, or false if not a valid password
	 */
	public function check_password( $password, $include_ancestors = true ) {
		if ( ! $password ) {
			return false;
		}

		$valid_for_term = in_array( $password, $this->get_passwords() ) ? $this->term : false;

		if ( ! $valid_for_term && $include_ancestors ) {
			foreach ( $this->ancestors() as $ancestor ) {
				if ( $ancestor->check_password( $password, false ) ) {
					$valid_for_term = $ancestor->term;
					break;
				}
			}
		}

		return $valid_for_term;
	}

	/**
	 * Is this a public? If $include_ancestors is set, ancestor terms
	 * will also be checked and it will return true if all ancestors are also public.
	 *
	 * @param boolean $include_ancestors Whether to check the ancestor terms as well
	 * @return boolean true if the term is public, false if not
	 */
	public function is_public( $include_ancestors = true ) {
		$public = 'public' === $this->visibility;

		// Only check ancestors if flag set and this category is public
		if ( $public && $include_ancestors ) {
			foreach ( $this->ancestors() as $ancestor ) {
				if ( ! $ancestor->is_public( false ) ) {
					$public = false;
					break;
				}
			}
		}

		return $public;
	}

	/**
	 * Is this a password protected term? If $include_ancestors is set, ancestor terms
	 * will also be checked and it will return true if any ancestor is password protected.
	 *
	 * @param boolean $include_ancestors Whether to check the ancestor terms as well
	 * @return boolean true if password protected, false if not
	 */
	public function is_password_protected( $include_ancestors = true ) {
		$protected = ( 'password' === $this->visibility ) && $this->get_passwords();

		// Not password protected if this category has been unlocked
		if ( $protected && $this->is_unlocked() ) {
			return false;
		}

		// Only check ancestors if flag set and this category itself is not protected
		if ( ! $protected && $include_ancestors ) {
			foreach ( $this->ancestors() as $ancestor ) {
				if ( $ancestor->is_password_protected( false ) ) {
					return true;
				}
			}
		}
		return $protected;
	}

	/**
	 * Is this a private term? If $include_ancestors is set, ancestor terms will
	 * also be checked and it will return true if any ancestor is private.
	 *
	 * @param boolean $include_ancestors Whether to check the ancestor terms as well
	 * @return boolean true if private, false if not
	 */
	public function is_private( $include_ancestors = true ) {
		$private = ( 'private' === $this->visibility ) && ! current_user_can( 'read_private_posts' );

		// Only check ancestors if flag set and this category itself is not private
		if ( ! $private && $include_ancestors ) {
			foreach ( $this->ancestors() as $ancestor ) {
				if ( $ancestor->is_private( false ) ) {
					$private = true;
					break;
				}
			}
		}

		return $private;
	}

	public function is_hidden( $include_ancestors = true ) {
		$show_protected = (bool) PPC_Util::get_option( 'show_protected' );
		return $this->is_private( $include_ancestors ) || ( ! $show_protected && $this->is_password_protected( $include_ancestors ) );
	}

	/**
	 * Retrieve this term's passwords.
	 *
	 * @return array An array of passwords, or an empty array if none set
	 */
	private function get_passwords() {
		return (array) get_term_meta( $this->term->term_id, 'password', false );
	}

	/**
	 * Is this term unlocked?
	 *
	 * @return boolean true if unlocked, false otherwise
	 */
	private function is_unlocked() {
		$unlocked = PPC_Util::get_unlocked_term();

		if ( ! $unlocked || $unlocked['term_id'] !== $this->term->term_id || $unlocked['taxonomy'] !== $this->term->taxonomy ) {
			return false;
		}

		require_once ABSPATH . WPINC . '/class-phpass.php';
		$hasher = new PasswordHash( 8, true );

		$hash = wp_unslash( $unlocked['password'] );
		if ( 0 !== strpos( $hash, '$P$B' ) ) {
			return false;
		}

		if ( $passwords = $this->get_passwords() ) {
			foreach ( $passwords as $password ) {
				if ( $hasher->CheckPassword( $password, $hash ) ) {
					return true;
				}
			}
		}

		return false;
	}

	private function to_term( $term_id ) {
		$term = get_term_by( 'id', $term_id, $this->term->taxonomy );
		return $term ? $term : false;
	}

}
// class PPC_Term_Visibility
