<?php
// Prevent direct file access
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * This class provides functions for the 'visibility' field for categories and custom taxonomies in the admin.
 *
 * @package   Password_Protected_Categories/Admin
 * @author    Barn2 Media <info@barn2.co.uk>
 * @license   GPL-3.0
 * @link      https://barn2.co.uk
 * @copyright 2016-2018 Barn2 Media Ltd
 */
class PPC_Admin_Term_Visibility_Field {

	public function __construct() {
		add_action( 'admin_init', array( $this, 'term_visibility_hooks' ) );
	}

	public function term_visibility_hooks() {
		foreach ( PPC_Util::get_protectable_taxonomies() as $tax ) {
			// Add visibility field
			add_action( "{$tax}_add_form_fields", array( $this, 'add_visibility_field' ), 20 );
			add_action( "{$tax}_edit_form_fields", array( $this, 'edit_visibility_field' ), 10 );

			// Save visibility field
			add_action( "created_{$tax}", array( $this, 'save_visibility_field' ), 10, 2 );
			add_action( "edit_{$tax}", array( $this, 'save_visibility_field' ), 10, 2 );

			// Add visibility column to product category table
			add_filter( "manage_edit-{$tax}_columns", array( $this, 'term_table_visibility_column_heading' ) );
			add_filter( "manage_{$tax}_custom_column", array( $this, 'term_table_visibility_column' ), 10, 3 );

			//@todo To make Visibility column sortable we need to filter 'get_terms_args' and change 'orderby' args into a meta query (if possible)
			//add_filter( 'manage_edit-product_cat_sortable_columns', array( $this, 'term_table_make_visibility_sortable' ) );
		}
	}

	/**
	 * Add visibility field to 'add product category' screen
	 */
	public function add_visibility_field() {
		?>
		<div class="form-field term-visibility-wrap">
			<?php $this->visibility_label(); ?>
			<?php $this->visibility_group(); ?>
		</div>
		<?php
	}

	/**
	 * Add visibility field to 'edit product category' screen
	 *
	 * @param mixed $term The product category being edited
	 */
	public function edit_visibility_field( $term ) {

		$visibility = get_term_meta( $term->term_id, 'visibility', true );

		if ( ! $visibility ) {
			$visibility = 'public'; // Default to public if not set
		}
		?>
		<tr class="form-field term-visibility-wrap">
			<th scope="row" valign="top">
				<?php $this->visibility_label(); ?>
			</th>
			<td>
				<?php $this->visibility_group( $visibility, get_term_meta( $term->term_id, 'password', false ) ); ?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Save visibility and password for product category
	 *
	 * @param mixed $term_id Term ID being saved
	 * @param mixed $tt_id The term taxonomy ID
	 */
	public function save_visibility_field( $term_id, $tt_id = '' ) {

		$visibility = filter_input( INPUT_POST, 'cat_visibility', FILTER_SANITIZE_STRING );

		// Bail if no visibility to save (e.g. on 'quick edit')
		if ( ! $visibility ) {
			return;
		}

		if ( 'password' === $visibility ) {
			// Remove existing passwords
			delete_term_meta( $term_id, 'password' );

			$passwords = filter_input( INPUT_POST, 'cat_password', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
			if ( $passwords ) {
				$passwords = array_filter( $passwords );
			}
			if ( ! $passwords ) {
				// Set default password in case none was entered
				$passwords = array( 'password' );
			}
			foreach ( $passwords as $password ) {
				add_term_meta( $term_id, 'password', esc_attr( $password ) );
			}
		}

		update_term_meta( $term_id, 'visibility', esc_attr( $visibility ) );
	}

	public function term_table_visibility_column_heading( $columns ) {
		return $this->list_table_insert_after_column( $columns, 'name', 'visibility', __( 'Visibility', 'password-protected-categories' ) );
	}

	public function term_table_visibility_column( $output, $column, $term_id ) {
		if ( 'visibility' === $column ) {
			// Default to public visibility
			$output = __( 'Public', 'password-protected-categories' );

			$visibility = get_term_meta( $term_id, 'visibility', true );

			if ( 'private' === $visibility ) {
				$output = __( 'Private', 'password-protected-categories' );
			} elseif ( 'password' === $visibility ) {
				$output = __( 'Password protected', 'password-protected-categories' );
			}
		}

		return $output;
	}

	private function visibility_label() {
		?>
		<label><?php _e( 'Visibility', 'password-protected-categories' ); ?></label>
		<?php
	}

	private function visibility_group( $visibility = 'public', $passwords = false ) {
		if ( ! $passwords ) {
			// If no passwords, add an empty one so we always display at least one password input
			$passwords = array( '' );
		}
		// Re-key to ensure we have numeric keys
		$passwords = array_values( $passwords );
		?>
		<fieldset id="cat-visibility" class="cat-visibility-group">
			<span class="screen-reader-text">
				<?php _e( 'Visibility', 'password-protected-categories' ); ?>
			</span>
			<label for="public-visibility">
				<input type="radio" name="cat_visibility" id="public-visibility" value="public" <?php checked( $visibility, 'public' ); ?> /> <?php _e( 'Public', 'password-protected-categories' ); ?>
			</label>
			<label for="password-visibility">
				<input type="radio" name="cat_visibility" id="password-visibility" value="password" <?php checked( $visibility, 'password' ); ?> /> <?php _e( 'Password protected', 'password-protected-categories' ); ?>
			</label>
			<div id="cat-passwords" class="cat-passwords" style="display:<?php echo ( 'password' === $visibility ? 'block' : 'none'); ?>;">
				<?php
				foreach ( $passwords as $index => $password ) :
					$first = $index === 0;
					?>
					<span class="cat-password-field" data-first="<?php echo esc_attr( $first ? 'true' : 'false'  ); ?>">
						<label><?php _e( 'Password:', 'password-protected-categories' ); ?></label>
						<input type="text" name="cat_password[]" value="<?php echo esc_attr( $password ); ?>" size="40" />
						<span class="add-remove-icons">
							<a class="cat-password-icon add" data-action="add" href="#"><span class="dashicons dashicons-plus"></span></a>
							<?php if ( ! $first ) : ?>
								<a class="cat-password-icon delete" data-action="delete" href="#"><span class="dashicons dashicons-minus"></span></a>
							<?php endif; ?>
						</span>
					</span>
				<?php endforeach; ?>
			</div>
			<label for="private-visibility">
				<input type="radio" name="cat_visibility" id="private-visibility" value="private" <?php checked( $visibility, 'private' ); ?> /> <?php _e( 'Private', 'password-protected-categories' ); ?>
			</label>
		</fieldset>
		<?php
	}

	private function list_table_insert_after_column( $columns, $after_key, $insert_key, $insert_value ) {
		$new_columns = array();

		foreach ( $columns as $key => $column ) {
			if ( $after_key === $key ) {
				$new_columns[$key]			 = $column;
				$new_columns[$insert_key]	 = $insert_value;
			} else {
				$new_columns[$key] = $column;
			}
		}

		return $new_columns;
	}

}
// class PPC_Admin_Term_Visibility_Field