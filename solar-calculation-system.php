<?php
/*
Plugin Name: Solar Calculation System
Plugin URI: https://brandbees.net
Description: A plugin for solar system installation companies to generate invoices and save all relevant contract details.
Version: 1.0.0
Author: Hassan Ejaz
Author URI: https://brandbees.net
License: GPLv2 or later
Text Domain: solar-calculation-system
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants
define( 'SCS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SCS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

// Include required files
require_once SCS_PLUGIN_DIR . 'includes/class-scs-custom-post-types.php';
require_once SCS_PLUGIN_DIR . 'includes/class-scs-api.php';
require_once SCS_PLUGIN_DIR . 'includes/class-scs-frontend.php';
require_once SCS_PLUGIN_DIR . 'includes/class-scs-admin.php';
require_once SCS_PLUGIN_DIR . 'includes/class-scs-db.php';
require_once SCS_PLUGIN_DIR . 'includes/class-scs-calculations.php';
require_once SCS_PLUGIN_DIR . 'includes/class-scs-user.php';
require_once SCS_PLUGIN_DIR . 'includes/class-scs-dashboard.php';

// Initialization hooks
function scs_initialize_plugin() {
    // Register custom post types first
    SCS_Custom_Post_Types::register_post_types();

    // Initialize other components
    SCS_User::init();        // Initialize the user class
    SCS_Dashboard::init();   // Initialize the dashboard class
    SCS_Frontend::init();    // Initialize frontend functionality
    SCS_Admin::init();       // Initialize admin functionality
}
add_action( 'init', 'scs_initialize_plugin' );

function scs_enqueue_assets() {
    // Enqueue frontend styles and scripts only on frontend
    if ( ! is_admin() ) {
        wp_enqueue_style( 'scs-frontend-css', SCS_PLUGIN_URL . 'assets/css/frontend.css', array(), '1.0.0' );
        wp_enqueue_script( 'scs-frontend-js', SCS_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), '1.0.0', true );
    }

    // Enqueue admin styles and scripts only in admin area
    if ( is_admin() ) {
        wp_enqueue_style( 'scs-admin-css', SCS_PLUGIN_URL . 'assets/css/admin.css', array(), '1.0.0' );
        wp_enqueue_script( 'scs-admin-js', SCS_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), '1.0.0', true );
    }
}
add_action( 'wp_enqueue_scripts', 'scs_enqueue_assets' );
add_action( 'admin_enqueue_scripts', 'scs_enqueue_assets' );


add_action('init', function () {
    add_rewrite_rule('^print-invoice/?$', 'index.php?print_invoice=1', 'top');
});

add_filter('query_vars', function ($query_vars) {
    $query_vars[] = 'print_invoice';
    $query_vars[] = 'invoice_id';
    return $query_vars;
});

add_action('template_redirect', function () {
    if (get_query_var('print_invoice')) {
        include plugin_dir_path(__FILE__) . 'templates/print-invoice.php';
        exit;
    }
});

register_activation_hook(__FILE__, 'scs_flush_rewrite_rules');
function scs_flush_rewrite_rules() {
    scs_add_rewrite_rules();
    flush_rewrite_rules();
}
