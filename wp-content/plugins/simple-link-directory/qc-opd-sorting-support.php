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

		<style>

		.filter-sld-tax .filter-btn {
		  background-color: #fff;
		  border-color: #ccc;
		  color: #333;
		  -moz-user-select: none;
		  background-image: none;
		  border: 1px solid #ccc;
		  border-radius: 4px;
		  cursor: pointer;
		  display: inline-block;
		  font-size: 14px;
		  font-weight: 400;
		  line-height: 1.42857;
		  margin-bottom: 0;
		  padding: 6px 12px;
		  text-align: center;
		  vertical-align: middle;
		  white-space: nowrap;
		  -moz-border-radius: 3px;
		  -webkit-border-radius: 3px;
		  border-radius: 3px;
		  -ms-filter: "progid:DXImageTransform.Microsoft.Shadow(Strength=0, Direction=90, Color=#ffffff)";
			-moz-box-shadow: 0px 0px 0px #ffffff;
			-webkit-box-shadow: 0px 0px 0px #ffffff;
			box-shadow: 0px 0px 0px #ffffff;
			filter: progid:DXImageTransform.Microsoft.Shadow(Strength=0, Direction=90, Color=#ffffff);  
		  -webkit-transition: all 0.3s ease;
		  -moz-transition: all 0.3s ease;
		  -o-transition: all 0.3s ease;
		  transition: all 0.3s ease;
		  text-decoration: none;
		}

		.filter-sld-tax .filter-btn:hover{
		  background-color: #e6e6e6;
		  border-color: #adadad;
		  color: #333;
		}

		.filter-btn {
		  margin-bottom: 10px !important;
		  margin-right: 10px;
		  text-decoration: none !important;
		}
		</style>

		<?php 

		$terms = get_terms( array(
		    'taxonomy' => 'sld_cat',
		    'hide_empty' => false,
		) );

		?>

		<h2>Order Directory Items</h2>
		<p>Simply drag the item up or down and they will be saved in that order.</p>

		<div class="filter-sld-tax" style="margin-bottom: 15px; padding-bottom: 5px; border-bottom: 1px solid #ccc;">
			<a href="#" class="filter-btn" data-filter="all">
				<?php _e('Show All', 'qc-opd'); ?>
			</a>

			<?php foreach ($terms as $term) : ?>
				<a href="#" class="filter-btn" data-filter="<?php echo $term->slug; ?>">
					<?php echo $term->name; ?>
				</a>
			<?php endforeach; ?>
		</div>

	<?php $sld = new WP_Query( array( 'post_type' => 'sld', 'posts_per_page' => -1, 'order' => 'ASC', 'orderby' => 'menu_order' ) ); ?>
	<?php if( $sld->have_posts() ) : ?>

		<table id="opd-sort-tbl" class="wp-list-table widefat fixed posts">
			<thead>
				<tr>
					<th class="column-order">Order</th>
					<th class="column-title">Title</th>
					<th class="column-cat">Category</th>
					<th class="column-elem">Number of Elements</th>
					<th class="column-code">Shortcode</th>
				</tr>
			</thead>
			<tbody class="tbl-body" data-post-type="sld">
			<?php while( $sld->have_posts() ) : $sld->the_post(); ?>

				<?php 
					//Get all the term slugs for this post
					$terms = get_the_terms( get_the_ID(), 'sld_cat' );
					$termListTitles = "";
					$termListSlugs = "";

					if( $terms && !is_wp_error( $terms ) ) 
					{
						
						$count = 1;
						$length = count($terms);

					    foreach( $terms as $term ) 
					    {
					        
					        $termListSlugs .= $term->slug . " ";
					        $termListTitles .= $term->name;
					        
					        if( $count != $length ){
					        	$termListTitles .= ", ";
					        }

					        $count++;

					    }

					} 
				?>

				<tr id="post-<?php the_ID(); ?>" class="all-row <?php echo $termListSlugs; ?>">
					<td class="column-order">
						<img src="<?php echo QCOPD_IMG_URL . '/move_alt1.png'; ?>" title="" alt="Move Icon" width="24" height="24" class="" />
					</td>
					<td class="column-title">
						<strong><?php the_title(); ?></strong>
					</td>
					<td class="column-category">
						<?php echo $termListTitles; ?>
					</td>
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
					<th class="column-cat">Category</th>
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


