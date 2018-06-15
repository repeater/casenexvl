<?php 
wp_head();
?>
<link rel="stylesheet" type="text/css" href="<?php echo QCOPD_ASSETS_URL . "/css/font-awesome.min.css"; ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo QCOPD_ASSETS_URL . "/css/directory-style.css"; ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo QCOPD_URL . "/embed/css/embed-form.css"; ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo QCOPD_ASSETS_URL . "/css/directory-style-rwd.css"; ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo QCOPD_ASSETS_URL . "/css/slick.css"; ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo QCOPD_ASSETS_URL . "/css/magnific-popup.css"; ?>"/>
<link rel="stylesheet" type="text/css" href="<?php echo QCOPD_ASSETS_URL . "/css/slick-theme.css"; ?>"/>





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
<script src="<?php echo QCOPD_ASSETS_URL . "/js/tooltipster.bundle.min.js"; ?>"></script>
<script src="<?php echo QCOPD_URL . "/embed/js/embed-form.js"; ?>"></script>
<script src="<?php echo QCOPD_ASSETS_URL . "/js/jquery.magnific-popup.min.js"; ?>"></script>

<script src="<?php echo QCOPD_ASSETS_URL . "/js/directory-script.js"; ?>"></script>
<script src="<?php echo QCOPD_ASSETS_URL . "/js/slick.min.js"; ?>"></script>

<?php

$orderby = sanitize_text_field(isset($_GET['orderby'])?$_GET['orderby']:'');
$order = sanitize_text_field(isset($_GET['order'])?$_GET['order']:'');
$mode = sanitize_text_field(isset($_GET['mode'])?$_GET['mode']:'');
$column = sanitize_text_field(isset($_GET['column'])?$_GET['column']:'');
$style = sanitize_text_field(isset($_GET['style'])?$_GET['style']:'');
$search = sanitize_text_field(isset($_GET['search'])?$_GET['search']:'');
$category = sanitize_text_field(isset($_GET['category'])?$_GET['category']:'');
$upvote = sanitize_text_field(isset($_GET['upvote'])?$_GET['upvote']:'');
$tooltip = sanitize_text_field(isset($_GET['tooltip'])?$_GET['tooltip']:'');
$list_id = sanitize_text_field(isset($_GET['list_id'])?$_GET['list_id']:'');



echo '<div class="clear">';

echo do_shortcode('[qcopd-directory mode="' . $mode . '" list_id="' . $list_id . '" style="' . $style . '" tooltip="' . $tooltip . '" column="' . $column . '" search="' . $search . '" category="' . $category . '" upvote="' . $upvote . '" item_count="on" orderby="' .$orderby. '" order="' . $order . '"]');

echo '</div>'; 

?>
<?php 
wp_footer();
?>




