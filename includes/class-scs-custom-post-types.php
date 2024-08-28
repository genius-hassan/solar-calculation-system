<?php

class SCS_Custom_Post_Types {

    public static function register_post_types() {
        // Register Solar System Post Type
        $labels = array(
            'name'               => _x( 'Solar System', 'post type general name', 'solar-calculation-system' ),
            'singular_name'      => _x( 'Solar System', 'post type singular name', 'solar-calculation-system' ),
            'menu_name'          => _x( 'Solar System', 'admin menu', 'solar-calculation-system' ),
            'name_admin_bar'     => _x( 'Solar System', 'add new on admin bar', 'solar-calculation-system' ),
            'add_new'            => _x( 'Add New', 'solar system', 'solar-calculation-system' ),
            'add_new_item'       => __( 'Add New Solar System', 'solar-calculation-system' ),
            'new_item'           => __( 'New Solar System', 'solar-calculation-system' ),
            'edit_item'          => __( 'Edit Solar System', 'solar-calculation-system' ),
            'view_item'          => __( 'View Solar System', 'solar-calculation-system' ),
            'all_items'          => __( 'All Solar Systems', 'solar-calculation-system' ),
            'search_items'       => __( 'Search Solar Systems', 'solar-calculation-system' ),
            'parent_item_colon'  => __( 'Parent Solar Systems:', 'solar-calculation-system' ),
            'not_found'          => __( 'No solar systems found.', 'solar-calculation-system' ),
            'not_found_in_trash' => __( 'No solar systems found in Trash.', 'solar-calculation-system' )
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false, // Not publicly accessible
            'publicly_queryable' => false, // Disable frontend queries
            'show_ui'            => true,  // Show in admin interface
            'show_in_menu'       => true,  // Show in admin menu
            'query_var'          => false, // Disable query variable
            'rewrite'            => false, // No pretty permalinks
            'capability_type'    => 'post',
            'has_archive'        => false, // No archive page
            'hierarchical'       => false,
            'menu_position'      => 5,
            'supports'           => array( 'title', 'editor', 'author', 'revisions' ), // Removed 'thumbnail'
            'taxonomies'         => array( 'type' ),
            'show_in_rest'       => true,  // Enable REST API
        );

        register_post_type( 'solar_system', $args );

        // Register Type Taxonomy
        $labels = array(
            'name'              => _x( 'Types', 'taxonomy general name', 'solar-calculation-system' ),
            'singular_name'     => _x( 'Type', 'taxonomy singular name', 'solar-calculation-system' ),
            'search_items'      => __( 'Search Types', 'solar-calculation-system' ),
            'all_items'         => __( 'All Types', 'solar-calculation-system' ),
            'parent_item'       => __( 'Parent Type', 'solar-calculation-system' ),
            'parent_item_colon' => __( 'Parent Type:', 'solar-calculation-system' ),
            'edit_item'         => __( 'Edit Type', 'solar-calculation-system' ),
            'update_item'       => __( 'Update Type', 'solar-calculation-system' ),
            'add_new_item'      => __( 'Add New Type', 'solar-calculation-system' ),
            'new_item_name'     => __( 'New Type Name', 'solar-calculation-system' ),
            'menu_name'         => __( 'Type', 'solar-calculation-system' ),
        );

        $args = array(
            'hierarchical'      => true,
            'labels'            => $labels,
            'show_ui'           => true,
            'show_admin_column' => true,
            'query_var'         => false,
            'rewrite'           => false,
            'show_in_rest'      => true,  // Enable REST API for taxonomy
            'rest_base'         => 'scs_type',  // Custom rest_base to avoid conflicts
        );

        register_taxonomy( 'scs_type', array( 'solar_system' ), $args );
    }
}

// Disable Gutenberg for Solar System Post Type
function scs_disable_gutenberg_for_solar_system($can_edit, $post_type) {
    if ($post_type === 'solar_system') {
        return false;
    }
    return $can_edit;
}
add_filter('use_block_editor_for_post_type', 'scs_disable_gutenberg_for_solar_system', 10, 2);