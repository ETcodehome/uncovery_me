<?php
/*
 * This file is part of Uncovery Minecraft.
 * Copyright (C) 2015 uncovery.me
 *
 * Uncovery Minecraft is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the Free
 * Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of  MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
 * more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * This file is called by wordpress to run all the plugins. It does not go through
 * core_include.inc.php so you need to include whatever you need on-demand.
 */
global $umc_wp_register_questions;

global $XMPP_ERROR;
$XMPP_ERROR['config']['project_name'] = 'Uncovery.me';
require_once('/home/includes/xmpp_error/xmpp_error.php');

/**
 * Initialize plugins so that the hooks in Wordpress are correct
 */
function umc_wp_init_plugins() {
    XMPP_ERROR_trace(__FUNCTION__, func_get_args());
    // chose different page templates depending on condition
    add_filter('template_include', 'umc_wp_template_picker', 99);
    // add fields to registration form
    add_action('register_form', 'umc_wp_register_addFields');
    // add check to posting registration form
    add_action('register_post', 'umc_wp_register_checkFields', 10, 3);
    // add actions when the user registers successfullt
    add_action('user_register', 'umc_wp_register_addWhitelist', 10, 1);
    // make notification when new post is made to blog or forum
    add_action('transition_post_status', 'umc_wp_notify_new_post', 10, 3);
    // make notification when new comment is made on post
    add_action('comment_post', 'umc_wp_notify_new_comment', 10, 2);
    // add additional CSS and JS
    add_action('wp_enqueue_scripts', 'umc_wp_add_css_and_js');
    add_action('admin_enqueue_scripts', 'umc_wp_add_css_and_js');

    // what happens when a user gets deleted
    add_action('delete_user', 'umc_wp_user_delete');

    remove_action('wp_head', 'start_post_rel_link', 10, 0 );
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
    add_action('wp_footer', 'umc_wp_fingerprint_call');

    // check if we allow password resets in case user is banned
    add_action('validate_password_reset', 'umc_wp_password_reset_check',  10, 2 );

    // avatars
    add_filter('avatar_defaults', 'umc_wp_add_uncovery_avatar');
    add_filter('get_avatar', 'umc_wp_get_uncovery_avatar', 1, 5);

    add_filter('bbp_subscription_to_email', 'umc_wp_bbp_subscription_to_email');

    global $pagenow;
    if ($pagenow==='wp-login.php') {
        add_filter( 'gettext', 'user_email_login_text', 20, 3 );
        function user_email_login_text( $translated_text, $text, $domain ) {
            $texts = array(
                'Please enter your username or email address. You will receive a link to create a new password via email.',
                'A password will be e-mailed to you.',
            );
            if (in_array($translated_text, $texts)) {
                $translated_text .= '<br><br><strong>Attention: </strong> If you have trouble getting emails, please use the following command in-game: <strong>/info setpass</strong>';
            }
            return $translated_text;
      }

    }
}

/**
 * action that is run when WP deletes a user
 *
 * @param type $wp_user_id
 */
function umc_wp_user_delete($wp_user_id) {
    XMPP_ERROR_trace(__FUNCTION__, func_get_args());
    require_once('/home/minecraft/server/bin/core_include.php');
    // let's get more info about the user
    $user_obj = get_userdata($wp_user_id);
    $username = $user_obj->display_name;
    $uuid = umc_wp_get_uuid_for_currentuser($user_obj);
    if ($uuid) { // if there is no UUID, nothing to do
        umc_plugin_eventhandler('user_delete', $uuid);
    }
    XMPP_ERROR_trigger("User $username / $uuid has been deleted!");
}

function umc_wp_fingerprint_call() {
    XMPP_ERROR_trace(__FUNCTION__, func_get_args());
    require_once('/home/minecraft/server/bin/core_include.php');
    $uuid = umc_wp_get_uuid_for_currentuser();
    if ($uuid) {
        $out = '
    <script>
        jQuery(document).ready(function(jQuery) {
            var fp = new Fingerprint2();
            fp.get(function(result) {
                var fingerprint_url = "http://uncovery.me/admin/index.php?function=web_set_fingerprint&uuid='.$uuid.'&id=" + result;
                jQuery.ajax(fingerprint_url);
            });
        });
    </script>';
        echo $out;
    }
}

/**
 * Validate password resets for banned users
 *
 * @param type $errors
 * @param type $user_obj
 */
function umc_wp_password_reset_check($errors, $user_obj) {
    XMPP_ERROR_trace(__FUNCTION__, func_get_args());
    require_once('/home/minecraft/server/bin/index_wp.php');
    $check = umc_user_is_banned($user_obj->user_login);
    if ($check) {
        // user is banned
        $errors->add('user_is_banned', 'ERROR: You are banned from this server. Password request denied.');
        XMPP_ERROR_send_msg("Banned User " . $user_obj->user_login . " attempted password reset");
    }
}


/**
 * pick specific templates based on POST variables
 *
 * @param type $template
 * @return type
 */
function umc_wp_template_picker($template) {
    XMPP_ERROR_trace(__FUNCTION__, func_get_args());
    $s_post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);
    // to prevent recursive inclusion of headers and footers, we only load the
    // core contents of a page when we submit a form through Ajax.
    if (isset($s_post['ajax_form_submit'])) {
        $new_template = locate_template(array('emptypage.php'));
        if ('' != $new_template) {
            return $new_template ;
        }
    }
    return $template;
}

/**
 * add additional CSS and JS
 */
function umc_wp_add_css_and_js() {
    wp_enqueue_style( 'dataTables', 'http://uncovery.me/admin/dataTables.css' );
    wp_enqueue_style( 'uncovery', 'http://uncovery.me/admin/global.css' );
    // execute floored's CSS only on his page
    $postid = get_the_ID();
    if ($postid == 15523) {
        wp_enqueue_style( 'floored_css', 'http://uncovery.me/admin/floored.css' );
    }
    wp_enqueue_script('jquery-ui-accordion');
    wp_enqueue_script('jquery-ui-tabs');
    wp_enqueue_script('uncovery_global_js', 'http://uncovery.me/admin/js/global.js');
    wp_enqueue_script('uncovery_fingerprint', 'http://uncovery.me/admin/js/fingerprint2.min.js');
}

function umc_wp_login_stylesheet() {
    wp_enqueue_style( 'custom-login',  'http://uncovery.me/admin/global.css' );
}
add_action('login_enqueue_scripts', 'umc_wp_login_stylesheet');


/**
 * Notify in-game when there is a new comment posted to the blog
 *
 * @param type $comment_id
 * @param type $arg2
 */
function umc_wp_notify_new_comment($comment_id, $arg2){
    XMPP_ERROR_trace(__FUNCTION__, func_get_args());
    $comment = get_comment( $comment_id, 'ARRAY_A' );
    $author = $comment['comment_author'];
    $parent = $comment['comment_post_ID'];

    $post = get_post($parent, 'ARRAY_A');
    $title = $post['post_title'];
    $post_link = "http://uncovery.me/?p=" . $post['ID'];

    $cmd1 = "ch qm n New Comment on Post &a$title &fby $author&f";
    $cmd2 = "ch qm n Link: &a$post_link&f";
    $cmd3 = "ch qm n Type &a/web read c$comment_id&f to read in-game";
    require_once('/home/minecraft/server/bin/index_wp.php');
    umc_exec_command($cmd1, 'asConsole');
    umc_exec_command($cmd2, 'asConsole');
    umc_exec_command($cmd3, 'asConsole');
}

/**
 * Notify in-game when there is a new post made to forum or blog
 * @param type $new_status
 * @param type $old_status
 * @param type $post
 */
function umc_wp_notify_new_post($new_status, $old_status, $post) {
    XMPP_ERROR_trace(__FUNCTION__, func_get_args());
    // check for valid post types
    $valid_post_types = array('post', "reply");
    if (!in_array($post->post_type, $valid_post_types)) {
        return;
    }
    
    if ($old_status != 'publish' && $new_status == 'publish') {
        $post_title = $post->post_title;
        $post_link = "http://uncovery.me/?p=" . $post->ID;
        $id = $post->ID;
        if ($post->post_type == 'post' && $post->post_parent == 0) {
            $cmd1 = "ch qm u New Blog Post: &a$post_title&f";
            $cmd2 = "ch qm u Link: &a$post_link&f";
            $cmd3 = "ch qm u Type &a/web read $id&f to read in-game";
        } else {
            $type = ucwords($post->post_type);
            if ($type == 'Reply') {
                $parent = get_post($post->post_parent);
                $post_title = $parent->post_title;
            }
            $author_id = $post->post_author;
            $user = get_userdata($author_id);
            $username = $user->display_name;
            $cmd1 = "ch qm n New Forum $type: &a$post_title &fby $username&f";
            $cmd2 = "ch qm n Link: &a$post_link&f";
            $cmd3 = "ch qm n Type &a/web read $id&f to read in-game";
        }
        require_once('/home/minecraft/server/bin/index_wp.php');
        umc_exec_command($cmd1, 'asConsole');
        umc_exec_command($cmd2, 'asConsole');
        umc_exec_command($cmd3, 'asConsole');
    }
}

$umc_wp_register_questions = array(
    0 => array('text'=>'What user level will you have after whitelisting?', 'true'=>1,
        'answers'=>array('0'=>'Admin - I can do anything!', '1'=>'Guest - I can only look around', '2'=>'Settler - I get a place to build!')),
    1 => array('text'=>'What do you need to do to get building rights?', 'true'=>1,
        'answers'=>array('0'=>'I have it already!', '1'=>'I have to apply for Settler status on the website!')),
    2 => array('text'=>'Which username do you choose here?', 'true'=>0,
        'answers'=>array('0'=>'My Minecraft username', '1'=>'My email address', '2'=>'31337 sh0073rz')),
    3 => array('text'=>'How do you know the IP of the server?', 'true'=>0,
        'answers'=>array('0'=>'Its written in the email I get when I fill this out correctly', '1'=>'I will have to guess', '2'=>'I ask for it in the forum')),
    4 => array('text'=>'In which world do you spawn?', 'true'=>2,
        'answers'=>array('0'=>'City world (survival mode)', '1'=>'Empire world (creative mode)', '2'=>'City world (creative mode)')),
);

/**
 * Add fields to the new user registration page that the user must fill out to register
 *
 * @since 0.5
 * @access private
 * @author Andrew Ferguson
*/
function umc_wp_register_addFields(){
    global $umc_wp_register_questions, $UMC_DOMAIN;
    XMPP_ERROR_trace(__FUNCTION__, func_get_args());

    // echo "Due to technical reasons, we cannot accept any new users right now. Please check back later";
    // die('umc_wp_register_addFields');

    $out = '<p>IMPORTANT: Hotmail/Microsoft is rejecting emails from us.
    If you do not get a password by email within one hour, please use the /info setpass command in-game instead.
        <label for="email_confirm">Confirm E-mail<br />
        <input type="text" name="email_confirm" id="email_confirm" class="input" value="" size="25" /></label>
    </p>';

    $out .= "<strong>Please also answer these questions AFTER reading <a href=\"$UMC_DOMAIN/whitelist/\">this page</a>:</strong><br /><br />\n\n";
    foreach ($umc_wp_register_questions as $q_index => $item) {
        $question = $item['text'];
        $answers = $item['answers'];
        $out .= "<label>$question</label><br />\n";
        foreach ($answers as $a_index => $text){
            $out .= "<input type=\"radio\" name=\"$q_index\" value=\"$a_index\" /> <label>$text</label><br>\n";
        }
        $out .= "<br /><br />\n\n";
    }
    echo $out;
}

/**
 * custom forum widget that shows replies and topics together instead of separated.
 * This should be one day converted into a proper widget. Rigth now the widget is
 * displayed directly via calling this function in an PHP-enabled widget.
 *
 * @param type $items
 * @return type
 */
function umc_wp_forum_widget($items = 20) {
    // get all topics
    $args1 = array(
	'posts_per_page'   => $items,
	'orderby'          => 'date',
	'order'            => 'DESC',
	'post_type'        => 'topic',
	'post_status'      => 'publish',
	'suppress_filters' => true
    );
    $topic_array = get_posts($args1);

    // iterate the posts
    $posts = array();
    foreach ($topic_array as $P) {
        // get all text fields
        $html = umc_wp_forum_get_postlink($P);
        $posts[$P->ID]['latest'] = $P->post_date;
        $posts[$P->ID]['link'] = $html;
    }

    // get all replies
    $args2 = array(
	'posts_per_page'   => $items,
	'orderby'          => 'date',
	'order'            => 'DESC',
	'post_type'        => 'reply',
	'post_status'      => 'publish',
	'suppress_filters' => true
    );
    $replies_array = get_posts($args2);

    foreach ($replies_array as $P) {
        // get all text fields
        $user = get_userdata($P->post_author);
        $uuid = get_user_meta($user->ID, 'minecraft_uuid', true);
        $icon_url = umc_user_get_icon_url($uuid);
        $verb = "replied";
        $parent = get_post($P->post_parent);
        $post_title = $parent->post_title;
        $date_obj = umc_datetime($P->post_date);
        $time_ago = umc_timer_format_diff($date_obj);
        $link = $parent->guid . "#post-" . $P->ID;
        $html = "<a href=\"http://uncovery.me/forums/users/$user->user_login/\" title=\"View $user->display_name&#039;s profile\"
            class=\"bbp-author-avatar\" rel=\"nofollow\"><img alt='' src='$icon_url' class='avatar avatar-14 photo' height='14' width='14' /></a>&nbsp;
            <a href=\"http://uncovery.me/forums/users/$user->user_login/\" title=\"View $user->display_name&#039;s profile\" class=\"bbp-author-name\" rel=\"nofollow\">
            $user->display_name</a> $verb<br><a class=\"bbp-reply-topic-title\" href=\"$link\" title=\"$post_title\">$time_ago ago</a>";
        if (!isset($posts[$P->post_parent])) {
            $parent_post = get_post($P->post_parent);
            $posts[$P->post_parent]['link'] = umc_wp_forum_get_postlink($parent_post);
            $posts[$P->post_parent]['latest'] = $P->post_date;
        }
        $posts[$P->post_parent]['replies'][$P->post_date] = $html;
        $posts[$P->post_parent]['latest'] = min($P->post_date, $posts[$P->post_parent]['latest']);
    }
    // sort the array
    usort($posts, "umc_wp_forum_sort");
    // reverse the sorting since it's old -> new otherwise
    //$rev_new_arr = array_reverse($new_arr, true);

    // assemble the HTML
    $out = "<ul>\n";
    foreach ($posts as $P) {
        $out .= "<li>\n{$P['link']}\n";
        if (isset($P['replies']) && count($P['replies']) > 0) {
            $out .= "<ul>\n";
            foreach ($P['replies'] as $reply) {
                $out .= "<li>\n$reply\n</li>\n";
            }
            $out .= "</ul>\n";
        }
        $out .="</li>\n";
    }
    $out .= "</ul>\n";
    // return output
    return $out;
}

function umc_wp_forum_sort($a, $b) {
    return strcmp($b["latest"], $a["latest"]);
}

function umc_wp_forum_get_postlink($P) {
    $user = get_userdata($P->post_author);
    $uuid = get_user_meta($user->ID, 'minecraft_uuid', true);
    $icon_url = umc_user_get_icon_url($uuid);
    $date_obj = umc_datetime($P->post_date);
    $time_ago = umc_timer_format_diff($date_obj);
    $link = $P->guid;
    $post_title = $P->post_title;
    $html = "<a class=\"bbp-reply-topic-title\" href=\"$link\" title=\"$post_title\">$post_title</a><br>by
        <a href=\"http://uncovery.me/forums/users/$user->user_login/\" title=\"View $user->display_name&#039;s profile\"
        class=\"bbp-author-avatar\" rel=\"nofollow\"><img alt='' src='$icon_url' class='avatar avatar-14 photo' height='14' width='14' /></a>&nbsp;
        <a href=\"http://uncovery.me/forums/users/$user->user_login/\" title=\"View $user->display_name&#039;s profile\" class=\"bbp-author-name\" rel=\"nofollow\">
        $user->display_name</a><br>$time_ago ago";
    return $html;
}

/**
 * Checks registration fields to make sure they are filled out (although that is the extent of the checking).
 * If they are not, an error is added to WP_Error
 *
 * @param $user_login string
 * @param $user_email string
 * @param $errors object WP_Error object that contains the list of existing registration errors, if any
 * @since 0.5
 * @access private
 * @author Andrew Ferguson
*/
function umc_wp_register_checkFields($user_login, $user_email, $errors){
    global $umc_wp_register_questions;
    XMPP_ERROR_trace(__FUNCTION__, func_get_args());
    $error = false;
    $s_post = filter_input_array(INPUT_POST, FILTER_SANITIZE_STRING);


    $chars = array('@', ":", "\\", "/");
    $count = 0;
    str_replace($chars, "", $user_login, $count);

    if (strlen($user_login) == 0 || strlen($user_email) == 0) {
        return false;
    } else if ($count > 0) {
        $error_msg = "You need to enter your minercaft username, not an email addres etc!";
        $errors->add('demo_error',__($error_msg));
        return $errors;
    } else if ((!isset($s_post['user_email'])) || (!isset($s_post['email_confirm'])) || ($s_post['user_email'] != $s_post['email_confirm'])) {
        $error_msg = "<strong>ERROR:</strong> Your email and your confirmed email address do not match. Please check and try again.";
        $errors->add('demo_error',__($error_msg));
        return $errors;
    } else {
        foreach ($umc_wp_register_questions as $q_index => $item) {
            if (!isset($s_post[$q_index]) || ($s_post[$q_index] != $item['true'])) {
                $error = true;
                $error_msg = "<strong>ERROR:</strong>You entered one or more wrong answers to the questions below. "
                    . "Please go back to <a href=\"http://uncovery.me/whitelist/\">this page</a>, read it properly and try again.";
            }
        }
        if ($error) {
            $errors->add('demo_error',__($error_msg));
            return $errors;
        } else {
            require_once('/home/minecraft/server/bin/index_wp.php');
            global $UMC_USER;
            if (!$UMC_USER || !$UMC_USER['uuid']) {
                $error_msg = "<strong>ERROR:</strong>We could not verify your username right now. If you own a copy of Minecraft under the username '$user_login', you can try to login to the server once. "
                    . "It will not let you login, but our system will get a confirmation from Mojang in case your username exists. "
                    . "If you are sure that this is your username, try to connect to uncovery.me with your minecraft client once. You can try to register afterwards here again. "
                    . "If you are certain that you are using the right username and still get this error, please submit a <a href=\"http://uncovery.me/help-2/support/\">support ticket</a>.";
                $errors->add('demo_error',__($error_msg));
                return $errors;
            } else if (umc_user_is_banned($UMC_USER['uuid'])) {
                $error_msg = "<strong>ERROR:</strong>Sorry, you were banned from the server. Please find another one.";
                $errors->add('demo_error',__($error_msg));
                return $errors;
            }
        }
    }
}

/**
 * register the user to the whitelist
 * ad the uuid to the UUID table
 * add the UUID to the Meta data in wordpress
 *
 * @param type $user_id
 */
function umc_wp_register_addWhitelist($user_id){
    XMPP_ERROR_trace(__FUNCTION__, func_get_args());
    $current_user = get_user_by('id', $user_id);
    $username = $current_user->user_login;
    require_once('/home/minecraft/server/bin/index_wp.php');
    //$check = umc_read_data('whitelist');
    // umc_update_data('whitelist', array($username => $username));
    umc_exec_command("whitelist add $username", 'asConsole', false);
    // add UUID to use meta
    $UUID = umc_user2uuid($username);
    add_user_meta($user_id, 'minecraft_uuid', $UUID);
    // add user to UUID table
    umc_uuid_record_usertimes('firstlogin');
}

function umc_wp_add_uncovery_avatar( $avatar_defaults ) {
    XMPP_ERROR_trace(__FUNCTION__, func_get_args());
    $avatar_defaults['uncovery'] = 'Minecraft Avatar';
    return $avatar_defaults;
}

function umc_wp_get_uncovery_avatar($avatar, $id_or_email, $size, $default, $alt) {
    XMPP_ERROR_trace(__FUNCTION__, func_get_args());
    if ($default == 'uncovery') {
        $filename = '/home/minecraft/server/bin/core_include.php';
        require_once($filename);
        if (!function_exists('umc_user_ban')) {
            XMPP_ERROR_trigger("Failed to include $filename!");
        }
        //Alternative text
        if (false === $alt) {
            $safe_alt = '';
        } else {
            $safe_alt = esc_attr($alt);
        }

        //Get username
        if (is_numeric($id_or_email)) {
            $id = (int) $id_or_email;
            $user = get_userdata($id);
            if ($user) {
                $username = $user->user_login;
            } else {
                return false; // user cannot be found, probably deleted
            }
        } else if (is_object($id_or_email)) {
            if (!empty($id_or_email->user_id)) {
                $id = (int) $id_or_email->user_id;
                $user = get_userdata($id);
                if ($user) {
                    $username = $user->user_login;
                } else {
                    return '';
                }
            } else if (!empty($id_or_email->comment_author)) {
                $username = $id_or_email->comment_author;
            }
        } else if (strstr($id_or_email, '@')) { // email
            require_once(ABSPATH . WPINC . '/ms-functions.php');
            $user = get_user_by('email', $id_or_email);
            $username = $user->user_login;
        } else { // by displayname
            $username = $id_or_email;
        }

        $uuid = umc_wp_get_uuid_from_userlogin($username);
        $icon = umc_user_get_icon_url($uuid); // 'https://crafatar.com/avatars/' . $uuid . '?size=' . $size;
        $avatar = "<img  class='avatar avatar-64 photo' alt='".$safe_alt."' src='".$icon."' class='avatar avatar-".$size." photo' height='".$size."' width='".$size."' />";
    }
    return $avatar;
}