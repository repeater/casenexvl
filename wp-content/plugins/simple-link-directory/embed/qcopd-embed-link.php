<link rel="stylesheet" type="text/css" href="<?php echo QCOPD_ASSETS_URL . "/css/font-awesome.min.css"; ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo QCOPD_ASSETS_URL . "/css/directory-style.css"; ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo QCOPD_URL . "/embed/css/embed-form.css"; ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo QCOPD_ASSETS_URL . "/css/directory-style-rwd.css"; ?>"/>

<script type="text/javascript">
    var ajaxurl = '<?php echo admin_url('admin-ajax.php'); ?>';
</script>

<style>
    .sld-add .sld-add-btn {
        display: none;
    !important;
    }
</style>

<script src="<?php echo QCOPD_URL . "/embed/js/jquery-1.11.3.js"; ?>"></script>
<script src="<?php echo QCOPD_ASSETS_URL . "/js/packery.pkgd.js"; ?>"></script>
<script src="<?php echo QCOPD_ASSETS_URL . "/js/directory-script.js"; ?>"></script>
<script src="<?php echo QCOPD_URL . "/embed/js/embed-form.js"; ?>"></script>
<script src="<?php echo QCOPD_ASSETS_URL . "/js/directory-script.js"; ?>"></script>

<?php


$order = sanitize_text_field($_GET['order']);
$mode = sanitize_text_field($_GET['mode']);
$column = sanitize_text_field($_GET['column']);
$style = sanitize_text_field($_GET['style']);
$search = '';
$category = sanitize_text_field($_GET['category']);
$upvote = '';

$list_id = sanitize_text_field($_GET['list_id']);

echo '<div class="clear">';

echo do_shortcode('[qcopd-directory mode="' . $mode .  '" list_id="' . $list_id . '" style="' . $style . '" column="' . $column . '" search="' . $search . '" category="' . $category . '" upvote="' . $upvote . '" item_count="on" orderby="date" order="' . $order . '"]'); 

echo '</div>';

?>





