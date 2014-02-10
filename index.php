<?php

/*
  Plugin Name:  Verbose Event Calendar
  Version: 1.0
  Plugin URI: http://wp.tutsplus.com/tutorials/wordpress-event-calendar-using-custom-post-types-and-verbose-calendar
  Description: Custom post type for creating events and display visually in a calendar control.
  Author URI: http://www.innovativephp.com
  Author: Rakhitha Nimesh
  License: GPL2
 */

function verbose_calendar_scripts() {
    global $post;

    wp_enqueue_script('jquery');

    wp_register_style('verboseCalCustomStyles', plugins_url('styles.css', __FILE__));
    wp_enqueue_style('verboseCalCustomStyles');

    wp_register_script('verboseCal', plugins_url('javascripts/jquery.calendar/jquery.calendar.min.js', __FILE__));
    wp_enqueue_script('verboseCal');

    wp_register_style('verboseCalMainStyles', plugins_url('stylesheets/main.css', __FILE__));
    wp_enqueue_style('verboseCalMainStyles');

    wp_register_style('verboseCalStyles', plugins_url('javascripts/jquery.calendar/calendar.css', __FILE__));
    wp_enqueue_style('verboseCalStyles');

    wp_register_script('verboseCalCustom', plugins_url('verboseCalCustom.js', __FILE__));
    wp_enqueue_script('verboseCalCustom');

    $config_array = array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('reward-nonce')
    );

    wp_localize_script('verboseCal', 'calendarData', $config_array);
}

add_action('wp_enqueue_scripts', 'verbose_calendar_scripts');

function verbose_calendar_admin_scripts() {
    global $post;

    wp_enqueue_script('jquery');

    wp_register_style('verboseCalCustomStyles', plugins_url('styles.css', __FILE__));
    wp_enqueue_style('verboseCalCustomStyles');

    wp_register_style('jqueryUIALL', plugins_url('themes/base/jquery.ui.all.css', __FILE__));
    wp_enqueue_style('jqueryUIALL');

    wp_register_script('jqueryUICore', plugins_url('ui/jquery.ui.core.js', __FILE__));
    wp_enqueue_script('jqueryUICore');

    wp_register_script('jqueryUIWidget', plugins_url('ui/jquery.ui.widget.js', __FILE__));
    wp_enqueue_script('jqueryUIWidget');

    wp_register_script('jqueryUIDate', plugins_url('ui/jquery.ui.datepicker.js', __FILE__));
    wp_enqueue_script('jqueryUIDate');

    wp_register_script('verboseCalAdmin', plugins_url('verboseCalAdmin.js', __FILE__));
    wp_enqueue_script('verboseCalAdmin');
}

add_action('admin_enqueue_scripts', 'verbose_calendar_admin_scripts');




function register_custom_event_type() {
    $labels = array(
        'name' => _x('Events', 'event'),
        'singular_name' => _x('Event', 'event'),
        'add_new' => _x('Add New', 'event'),
        'add_new_item' => _x('Add New Event', 'event'),
        'edit_item' => _x('Edit Event', 'event'),
        'new_item' => _x('New Event', 'event'),
        'view_item' => _x('View Event', 'event'),
        'search_items' => _x('Search Events', 'event'),
        'not_found' => _x('No events found', 'event'),
        'not_found_in_trash' => _x('No events found in Trash', 'event'),
        'parent_item_colon' => _x('Parent Event:', 'event'),
        'menu_name' => _x('Events', 'event'),
    );
    $args = array(
        'labels' => $labels,
        'hierarchical' => false,
        'supports' => array('title', 'editor', 'thumbnail'),
        'public' => true,
        'show_ui' => true,
        'show_in_menu' => true,
        'show_in_nav_menus' => true,
        'publicly_queryable' => true,
        'exclude_from_search' => false,
        'has_archive' => true,
        'query_var' => true,
        'can_export' => true,
        'rewrite' => true,
        'capability_type' => 'post'
    );
    register_post_type('event', $args);
}
add_action('init', 'register_custom_event_type');



add_action('add_meta_boxes', 'add_events_fields_box');

function add_events_fields_box() {
    add_meta_box('events_fields_box_id', 'Event Info', 'display_event_info_box', 'event');
}

function display_event_info_box() {
    global $post;

    $values = get_post_custom($post->ID);
    $eve_start_date = isset($values['_eve_sta_date']) ? esc_attr($values['_eve_sta_date'][0]) : '';
    $eve_end_date = isset($values['_eve_end_date']) ? esc_attr($values['_eve_end_date'][0]) : '';



    wp_nonce_field('event_frm_nonce', 'event_frm_nonce');

    $html = "<label>Event Start Date</label><input id='datepickerStart' type='text' name='datepickerStart' value='$eve_start_date' />
		<label>Event End Date</label><input id='datepickerEnd' type='text' name='datepickerEnd' value='$eve_end_date' />";
    echo $html;
}

add_action('save_post', 'save_event_information');

function save_event_information($post_id) {


    // Bail if we're doing an auto save  
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;

    // if our nonce isn't there, or we can't verify it, bail 
    if (!isset($_POST['event_frm_nonce']) || !wp_verify_nonce($_POST['event_frm_nonce'], 'event_frm_nonce'))
        return;

    // if our current user can't edit this post, bail  
    if (!current_user_can('edit_post'))
        return;


    if (isset($_POST['datepickerStart']))
        update_post_meta($post_id, '_eve_sta_date', esc_attr($_POST['datepickerStart']));
    if (isset($_POST['datepickerEnd']))
        update_post_meta($post_id, '_eve_end_date', esc_attr($_POST['datepickerEnd']));
}



function verbose_calendar() {
    global $post;


    return "<div id='main-container'></div><div id='popup_events'>
			<div class='pop_cls'></div>
			<div id='popup_events_list'>
				<div id='popup_events_head'></div>
				<div id='popup_events_bar'></div>
				<div id='event_row_panel' class='event_row_panel'></div>
		<div id='popup_events_bottom'></div>
			</div>

		       </div>";
}

add_shortcode("verbose_calendar", "verbose_calendar");


function get_posts_for_year() {
    global $post, $wpdb;


    $allEvents = array();

    $sql = "SELECT $wpdb->posts.guid,$wpdb->posts.post_title,DATE_FORMAT(post_date, '%m-%d-%Y') as post_date  FROM $wpdb->posts WHERE Year($wpdb->posts.post_date)='" . $_POST['currentYear'] . "' and post_status='publish' and post_type='post' ";

    $allPosts = array();
    $yearlyPosts = $wpdb->get_results($sql, ARRAY_A);
    foreach ($yearlyPosts as $key => $singlePost) {
        $singlePost['type'] = 'post';

        array_push($allEvents, $singlePost);
    }




    $sql = "SELECT $wpdb->posts.ID,$wpdb->posts.guid,$wpdb->posts.post_title,DATE_FORMAT(post_date, '%m-%d-%Y') as post_date  FROM $wpdb->posts
		inner join $wpdb->postmeta on $wpdb->posts.ID=$wpdb->postmeta.post_id WHERE 
		$wpdb->postmeta.meta_key='_eve_sta_date' and Year(STR_TO_DATE($wpdb->postmeta.meta_value, '%m/%d/%Y'))='" . $_POST['currentYear'] . "' and 			post_status='publish' and post_type='event'";




    $yearlyEvents = $wpdb->get_results($sql, ARRAY_A);
    foreach ($yearlyEvents as $key => $singleEvent) {

        $startDate = str_replace("/", "-", get_post_meta($singleEvent['ID'], '_eve_sta_date'));
        $endDate = str_replace("/", "-", get_post_meta($singleEvent['ID'], '_eve_end_date'));

        $singleEvent['startDate'] = $startDate[0];
        $singleEvent['endDate'] = $endDate[0];
        $singleEvent['type'] = 'event';


        array_push($allEvents, $singleEvent);
    }
    echo json_encode($allEvents);
    exit;
}

add_action('wp_ajax_nopriv_get_posts_for_year', 'get_posts_for_year');
add_action('wp_ajax_get_posts_for_year', 'get_posts_for_year');


