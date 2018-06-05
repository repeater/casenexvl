<?php

//Registering Sub Menu for Ordering
add_action( 'admin_menu', 'qcopd_register_sld_sorting_menu' );

function qcopd_register_sld_sorting_menu() {
	add_submenu_page(
		'edit.php?post_type=sld',
		'List Ordering',
		'List Ordering',
		'edit_pages', 'sld-order',
		'qcopd_sld_order_page'
	);
}

//Submenu Callback to show ordering page contents
function qcopd_sld_order_page() {
?>
	<div class="wrap">
		<h2>Order Directory Items</h2>
		<p>Simply drag the item up or down and they will be saved in that order.</p>
	<?php $sld = new WP_Query( array( 'post_type' => 'sld', 'posts_per_page' => -1, 'order' => 'ASC', 'orderby' => 'menu_order' ) ); ?>
	<?php if( $sld->have_posts() ) : ?>

		<table class="wp-list-table widefat fixed posts" id="qcopd-sortable-table">
			<thead>
				<tr>
					<th class="column-order">Order</th>
					<th class="column-title">Title</th>
					<th class="column-elem">Number of Elements</th>
					<th class="column-code">Shortcode</th>
				</tr>
			</thead>
			<tbody data-post-type="sld">
			<?php while( $sld->have_posts() ) : $sld->the_post(); ?>
				<tr id="post-<?php the_ID(); ?>">
					<td class="column-order">
						<img src="<?php echo QCOPD_IMG_URL . '/move_alt1.png'; ?>" title="" alt="Move Icon" width="24" height="24" class="" />
					</td>
					<td class="column-title"><strong><?php the_title(); ?></strong></td>
					<td class="column-elem">
						<?php echo count(get_post_meta( get_the_ID(), 'qcopd_list_item01' )); ?>
					</td>
					<td class="column-code">
					<?php echo '[qcopd-directory mode="one" list_id="'.get_the_ID().'"]'; ?>
					</td>
				</tr>
			<?php endwhile; ?>
			</tbody>
			<tfoot>
				<tr>
					<th class="column-order">Order</th>
					<th class="column-title">Title</th>
					<th class="column-elem">Number of Elements</th>
					<th class="column-code">Shortcode</th>
				</tr>
			</tfoot>

		</table>

	<?php else: ?>

		<p>No team found, why not <a href="post-new.php?post_type=gts_team">create one?</a></p>

	<?php endif; ?>
	<?php wp_reset_postdata(); // Don't forget to reset again! ?>

	<style>
		#sortable-table td { background: white; }
		#sortable-table .column-order { padding: 3px 10px; width: 50px; }
			#sortable-table .column-order img { cursor: move; }
		#sortable-table td.column-order { vertical-align: middle; text-align: center; }
		#sortable-table .column-thumbnail { width: 160px; }
	</style>

	</div><!-- .wrap -->

<?php

}

//jQuery UI Sorting
add_action( 'admin_enqueue_scripts', 'qcopd_admin_enqueue_scripts' );

function qcopd_admin_enqueue_scripts() {
	wp_enqueue_script( 'jquery-ui-sortable' );
	wp_enqueue_script( 'qcopd-sorting-scripts', QCOPD_ASSETS_URL . '/js/qcopd-admin-scripts.js' );
}

//Registering ajax for saving sort order
add_action( 'wp_ajax_sld_update_post_order', 'sld_update_post_order' );

function sld_update_post_order() {
	global $wpdb;

	$post_type     = $_POST['postType'];
	$order        = $_POST['order'];

	foreach( $order as $menu_order => $post_id )
	{
		$post_id         = intval( str_ireplace( 'post-', '', $post_id ) );
		$menu_order     = intval($menu_order);
		wp_update_post( array( 'ID' => $post_id, 'menu_order' => $menu_order ) );
	}

	die( '1' );
}


