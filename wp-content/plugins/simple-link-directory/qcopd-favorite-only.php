<?php
/**
 * Created by QuantunCloud.
 * Date: 9/14/2017
 * Time: 3:16 PM
 */

defined('ABSPATH') or die("No direct script access!");

add_shortcode('qcopd-directory-favorite', 'qcopd_directory_favorite_fnc');
function qcopd_directory_favorite_fnc($atts = array()){


	ob_start();

		echo do_shortcode('[qcopd-directory mode="all" style="custom" column="4" upvote="on" search="true" item_count="on" orderby="date" filterorderby="date" order="ASC" filterorder="ASC" paginate_items="false" favorite="enable" tooltip="false" list_title_font_size="" item_orderby="" list_title_line_height="" title_font_size="" subtitle_font_size="" title_line_height="" subtitle_line_height="" filter_area="normal" topspacing="" onlyfavorite="true"]');

	$content = ob_get_clean();
	return $content;

}