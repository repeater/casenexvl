<?php

add_action('wp_head', 'qcopd_ajax_ajaxurl');
add_action('admin_head', 'qcopd_ajax_ajaxurl');

function qcopd_ajax_ajaxurl()
{

    echo '<script type="text/javascript">
           var ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
}

//Doing ajax action stuff

function upvote_ajax_action_stuff()
{


    //Get posted items
    $action = trim($_POST['action']);
    $post_id = trim($_POST['post_id']);
    $meta_title = trim($_POST['meta_title']);
    $meta_link = trim($_POST['meta_link']);
    $li_id = trim($_POST['li_id']);

    //Check wpdb directly, for all matching meta items
    global $wpdb;

    $results = $wpdb->get_results("SELECT * FROM $wpdb->postmeta WHERE post_id = $post_id AND meta_key = 'qcopd_list_item01'");

    //Defaults
    $votes = 0;

    $data['votes'] = 0;
    $data['vote_status'] = 'failed';

    $exists = in_array("$li_id", $_COOKIE['voted_li']);

    //If li-id not exists in the cookie, then prceed to vote
    if (!$exists) {
		
        //Iterate through items
        foreach ($results as $key => $value) {

            $item = $value;

            $meta_id = $value->meta_id;

            $unserialized = unserialize($value->meta_value);

            //If meta title and link matches with unserialized data
            if (trim($unserialized['qcopd_item_title']) == trim($meta_title) && trim($unserialized['qcopd_item_link']) == trim($meta_link)) {

                $metaId = $meta_id;

                //Defaults for current iteration
                $upvote_count = 0;
                $new_array = array();
                $flag = 0;

                //Check if there already a set value (previous)
                if (array_key_exists('qcopd_upvote_count', $unserialized)) {
                    $upvote_count = (int)$unserialized['qcopd_upvote_count'];
                    $flag = 1;
                }

                foreach ($unserialized as $key => $value) {
                    if ($flag) {
                        if ($key == 'qcopd_upvote_count') {
                            $new_array[$key] = $upvote_count + 1;
                        } else {
                            $new_array[$key] = $value;
                        }
                    } else {
                        $new_array[$key] = $value;
                    }
                }

                if (!$flag) {
                    $new_array['qcopd_upvote_count'] = $upvote_count + 1;
                }

                $votes = (int)$new_array['qcopd_upvote_count'];

                $updated_value = serialize($new_array);

                $wpdb->update(
                    $wpdb->postmeta,
                    array(
                        'meta_value' => $updated_value,
                    ),
                    array('meta_id' => $metaId)
                );

                $voted_li = array("$li_id");

                $total = 0;
                $total = count($_COOKIE['voted_li']);
                $total = $total + 1;

                setcookie("voted_li[$total]", $li_id, time() + (86400 * 30), "/");

                $data['vote_status'] = 'success';
                $data['votes'] = $votes;
            }

        }
    }

    $data['cookies'] = $_COOKIE['voted_li'];

    echo json_encode($data);


    die(); // stop executing script
}

//Implementing the ajax action for frontend users
add_action('wp_ajax_qcopd_upvote_action', 'upvote_ajax_action_stuff'); // ajax for logged in users
add_action('wp_ajax_nopriv_qcopd_upvote_action', 'upvote_ajax_action_stuff'); // ajax for not logged in users
