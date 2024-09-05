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

// API End Points for the Login
add_action('rest_api_init', function () {
    register_rest_route('solar/v1', '/login', array(
        'methods' => 'POST',
        'callback' => 'solar_login_callback',
        'permission_callback' => '__return_true', // This allows any user to call this endpoint
    ));
});

function solar_login_callback(WP_REST_Request $request) {
    $username = $request->get_param('username');
    $password = $request->get_param('password');

    if (empty($username) || empty($password)) {
        return new WP_Error('authentication_failed', __('Username or Password cannot be empty'), array('status' => 403));
    }

    $user = wp_authenticate($username, $password);

    if (is_wp_error($user)) {
        return new WP_Error('authentication_failed', __('Invalid credentials'), array('status' => 403));
    }

    // Return success response
    return rest_ensure_response(array(
        'success' => true,
        'user_id' => $user->ID,
        // Add any other user data you want to return
    ));
}


add_action('rest_api_init', function () {
    register_rest_route('solar/v1', '/solar_system', array(
        'methods' => 'POST',
        'callback' => 'solar_create_invoice_callback',
        'permission_callback' => 'solar_permission_check',
    ));
});

function solar_create_invoice_callback(WP_REST_Request $request) {
    $invoice_data = $request->get_json_params();

    $post_data = array(
        'post_title'    => sanitize_text_field($invoice_data['customerName']),
        'post_type'     => 'solar_system',
        'post_status'   => 'publish',
        'meta_input'    => array(
            'scs_project_size'        => sanitize_text_field($invoice_data['projectSize']),
            'scs_customer_name'       => sanitize_text_field($invoice_data['customerName']),
            'scs_address'             => sanitize_text_field($invoice_data['address']),
            'scs_proposal_date'       => sanitize_text_field($invoice_data['proposalDate']),
            'scs_notes'               => sanitize_textarea_field($invoice_data['notes']),
            'scs_quotation_details'   => $invoice_data['quotationDetails'],
            'scs_wire_calculations'   => $invoice_data['wireCalculations'],
            'scs_total_project_cost'  => floatval($invoice_data['totalProjectCost']),
            'scs_total_wire_cost'     => floatval($invoice_data['totalWireCost']),
            'scs_grand_total'         => floatval($invoice_data['grandTotal']),
            'scs_status_type'         => sanitize_text_field($invoice_data['statusType']),
        ),
    );

    $post_id = wp_insert_post($post_data);

    if (is_wp_error($post_id)) {
        return new WP_Error('create_failed', __('Failed to create invoice'), array('status' => 500));
    }

    return rest_ensure_response(array('success' => true, 'post_id' => $post_id));
}

function solar_permission_check() {
    return current_user_can('edit_posts'); // Ensure the correct capabilities are assigned
}
