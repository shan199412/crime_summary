<?php

/*
Plugin Name: Crime Summary
Description: Short Code: [Crime-City-Summary]
Version: 1.1
Author: Alex
*/

// User cannot access the plugin directly
if (!defined('ABSPATH')) {
	exit;
}

// Add short code for the plugin
function generate_ccs_short_code() {
	include 'crime-summary.php';
}

add_shortcode('Crime-City-Summary', 'generate_ccs_short_code');

// Add the scripts
function add_ccs_scripts() {
	wp_enqueue_script('ccscity_script', plugins_url('/js/ccscity_script.js',__FILE__), array('jquery'),'1.1', true);
	wp_enqueue_style( 'ccscity_style', plugins_url('/css/ccscity_style.css', __FILE__), array(), '1.1');
}

add_action('wp_enqueue_scripts', 'add_ccs_scripts');
