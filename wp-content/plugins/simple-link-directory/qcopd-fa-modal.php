<?php 
function qcsld_modal_fa() 
{
	    $icons = get_option( 'fa_icons' );

        if ( ! $icons ) {
          $pattern = '/\.(fa-(?:\w+(?:-)?)+):before\s+{\s*content:\s*"(.+)";\s+}/';
          $subject = wp_remote_fopen( QCOPD_ASSETS_URL . '/css/font-awesome.css' );

          preg_match_all( $pattern, $subject, $matches, PREG_SET_ORDER );
          $icons = array();
          foreach ( $matches as $match ) {
            $icons[] = array( 'css' => $match[2], 'class' => stripslashes( $match[1] ) );
          }
          update_option( 'fa_icons', $icons );
        }

?>

<div class="fa-field-modal" id="fa-field-modal" style="display:none">
  <div class="fa-field-modal-close">&times;</div>
  <h1 class="fa-field-modal-title"><?php _e( 'Select Font Awesome Icon', 'qc-opb' ); ?></h1>

  <div class="fa-field-modal-icons">
		<form action="#">
			<fieldset>
				<input type="search" name="search" value="" id="id_search" /> <span class="loading">Loading...</span>
			</fieldset>
		</form>
	<?php if ( $icons ) : ?>

	  <?php foreach ( $icons as $icon ) : ?>

		<div class="fa-field-modal-icon-holder" data-icon="<?php echo $icon['class']; ?>">
		  <div class="icon">
			<i class="fa <?php echo $icon['class']; ?>"></i>
		  </div>
		  <div class="label">
			<?php echo $icon['class']; ?>
		  </div>
		</div>

	  <?php endforeach; ?>

	<?php endif; ?>
  </div>
</div>

<?php
}

add_action( 'admin_footer', 'qcsld_modal_fa');