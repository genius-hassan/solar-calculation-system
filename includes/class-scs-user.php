<?php

class SCS_User {

    public static function init() {
        add_shortcode('scs_login_form', array(__CLASS__, 'render_login_form'));
        add_action('template_redirect', array(__CLASS__, 'handle_login'));
        add_action('init', array(__CLASS__, 'create_solar_technician_role'));
        add_action('init', array(__CLASS__, 'restrict_solar_technician_access'));
    }

    public static function render_login_form() {
        if (is_user_logged_in()) {
            wp_redirect(home_url('/solar-system-dashboard'));
            exit;
        }

        ob_start();
        ?>
        <form id="scs-login-form" method="post">
            <label for="scs_username">Username</label>
            <input type="text" name="scs_username" id="scs_username" required />

            <label for="scs_password">Password</label>
            <input type="password" name="scs_password" id="scs_password" required />

            <input type="submit" name="scs_login" value="Login" />
        </form>
        <?php
        return ob_get_clean();
    }

    public static function handle_login() {
        if (isset($_POST['scs_login'])) {
            $creds = array(
                'user_login'    => $_POST['scs_username'],
                'user_password' => $_POST['scs_password'],
                'remember'      => true,
            );

            $user = wp_signon($creds, false);

            if (is_wp_error($user)) {
                echo '<p>Login failed: ' . $user->get_error_message() . '</p>';
            } else {
                wp_redirect(home_url('/solar-system-dashboard'));
                exit;
            }
        }
    }

    public static function create_solar_technician_role() {
        add_role('solar_technician', 'Solar Technician', array(
            'read' => true,
            'edit_solar_system' => true,
            'publish_solar_system' => true,
            'edit_published_solar_system' => true,
            'delete_solar_system' => true,
            'upload_files' => true,
        ));
    }

    public static function restrict_solar_technician_access() {
        if (current_user_can('solar_technician')) {
            show_admin_bar(false);
            if (is_admin()) {
                wp_redirect(home_url('/solar-system-dashboard'));
                exit;
            }
        }
    }
}
