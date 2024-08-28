<?php
/*
Plugin Name: Solar Calculation System
Plugin URI: https://yourwebsite.com/
Description: A plugin for solar system installation companies to generate invoices and save all relevant contract details.
Version: 1.0.0
Author: Hassan Ejaz
Author URI: https://yourwebsite.com/
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

// Initialization hooks
function scs_initialize_plugin() {
    // Register custom post types
    SCS_Custom_Post_Types::register_post_types();

    // Initialize frontend and admin functionality
    SCS_Frontend::init();
    SCS_Admin::init();
}
add_action( 'init', 'scs_initialize_plugin' );

function scs_enqueue_assets() {
    // Enqueue frontend styles and scripts
    wp_enqueue_style( 'scs-frontend-css', SCS_PLUGIN_URL . 'assets/css/frontend.css', array(), '1.0.0' );
    wp_enqueue_script( 'scs-frontend-js', SCS_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), '1.0.0', true );

    // Enqueue admin styles and scripts
    if ( is_admin() ) {
        wp_enqueue_style( 'scs-admin-css', SCS_PLUGIN_URL . 'assets/css/admin.css', array(), '1.0.0' );
        wp_enqueue_script( 'scs-admin-js', SCS_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), '1.0.0', true );
    }
}
add_action( 'wp_enqueue_scripts', 'scs_enqueue_assets' );
add_action( 'admin_enqueue_scripts', 'scs_enqueue_assets' );
