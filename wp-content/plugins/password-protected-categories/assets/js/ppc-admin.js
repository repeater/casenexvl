
( function( $, document ) {
	"use strict";

	var $cat_passwords = $( '#cat-passwords' );

	var updateTermVisibility = function() {
		if ( $( '#password-visibility:checked' ).length > 0 ) {
			$cat_passwords.show();
		} else {
			$cat_passwords.hide();
		}
	};

	$( '#cat-visibility' ).find( 'input:radio' ).change( function() {
		updateTermVisibility();
	} );

	$cat_passwords.on( 'click', 'a.cat-password-icon', function() {
		// Add password field
		if ( 'add' === $( this ).data( 'action' ) ) {
			var $password_copy = $( this ).parents( 'span.cat-password-field' ).clone();

			if ( $password_copy.data( 'first' ) ) {
				$password_copy.attr( 'data-first', false );
				$password_copy.children( 'span.add-remove-icons' ).append( '<a class="cat-password-icon delete" data-action="delete" href="#"><span class="dashicons dashicons-minus"></span></a>' );
			}
			$password_copy.find( 'input' ).attr( 'value', '' );
			$password_copy.appendTo( $cat_passwords );

		}
		// Remove password field
		else if ( 'delete' === $( this ).data( 'action' ) ) {
			var $password_field = $( this ).parents( 'span.cat-password-field' );
			var pass = $password_field.find( 'input' ).val();
			// Remove if no password entered or if user confirms
			if ( !pass || ( pass && confirm( ppc_params.confirm_delete ) ) ) {
				$password_field.remove();
			}
		}
		return false;
	} );

	$( document ).ready( function() {
		updateTermVisibility();

		// Fix strange table layout for EDD Download Categories
		$( 'body.taxonomy-download_category table.wp-list-table' ).removeClass( 'fixed' );
	} );

} )( jQuery, document );
