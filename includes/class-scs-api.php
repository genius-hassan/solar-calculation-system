<?php

class SCS_API {

    public static function register_routes() {
        // Register API routes here
    }
}
add_action( 'rest_api_init', array( 'SCS_API', 'register_routes' ) );
