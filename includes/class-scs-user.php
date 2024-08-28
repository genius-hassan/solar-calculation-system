<?php

class SCS_User {

    public static function init() {
        add_shortcode('scs_login_form', array(__CLASS__, 'render_login_form'));
        add_shortcode('scs_profile_form', array(__CLASS__, 'render_profile_form')); // New shortcode for the profile form
        add_action('template_redirect', array(__CLASS__, 'handle_login'));
        add_action('wp_ajax_scs_save_profile', array(__CLASS__, 'save_profile')); // AJAX action for saving profile
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

    public static function render_profile_form() {
        // Only logged-in users can see this form
        if (!is_user_logged_in()) {
            wp_redirect(home_url('/login'));
            exit;
        }

        $user_id = get_current_user_id();
        $company_name = get_user_meta($user_id, 'scs_company_name', true);
        $owner_name = get_user_meta($user_id, 'scs_owner_name', true);
        $company_address = get_user_meta($user_id, 'scs_company_address', true);
        $company_phone = get_user_meta($user_id, 'scs_company_phone', true);
        $company_logo = get_user_meta($user_id, 'scs_company_logo', true);
        $default_disclaimer = get_user_meta($user_id, 'scs_default_disclaimer', true);

        ob_start();
        ?>
        <form id="scs-profile-setup-form" method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('scs_profile_nonce', 'security'); ?>
            <label for="scs_company_name">Company Name</label>
            <input type="text" name="scs_company_name" id="scs_company_name" value="<?php echo esc_attr($company_name); ?>" />

            <label for="scs_owner_name">Owner Name</label>
            <input type="text" name="scs_owner_name" id="scs_owner_name" value="<?php echo esc_attr($owner_name); ?>" />

            <label for="scs_company_address">Company Address</label>
            <input type="text" name="scs_company_address" id="scs_company_address" value="<?php echo esc_attr($company_address); ?>" />

            <label for="scs_company_phone">Company Phone</label>
            <input type="text" name="scs_company_phone" id="scs_company_phone" value="<?php echo esc_attr($company_phone); ?>" />

            <label for="scs_company_logo">Company Logo</label>
            <input type="file" name="scs_company_logo" id="scs_company_logo" />

            <label for="scs_default_disclaimer">Default Disclaimer</label>
            <textarea name="scs_default_disclaimer" id="scs_default_disclaimer"><?php echo esc_textarea($default_disclaimer); ?></textarea>

            <button type="submit">Save Profile</button>
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

    public static function save_profile() {
        if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'scs_solar_system_nonce')) {
            wp_send_json_error(array('message' => 'Invalid nonce.'));
            return;
        }

        $user_id = get_current_user_id();

        if ($user_id) {
            update_user_meta($user_id, 'scs_company_name', sanitize_text_field($_POST['scs_company_name']));
            update_user_meta($user_id, 'scs_owner_name', sanitize_text_field($_POST['scs_owner_name']));
            update_user_meta($user_id, 'scs_company_address', sanitize_text_field($_POST['scs_company_address']));
            update_user_meta($user_id, 'scs_company_phone', sanitize_text_field($_POST['scs_company_phone']));
            update_user_meta($user_id, 'scs_default_disclaimer', sanitize_textarea_field($_POST['scs_default_disclaimer']));

            // Handle file upload for company logo
            if (!empty($_FILES['scs_company_logo']['name'])) {
                $upload = wp_handle_upload($_FILES['scs_company_logo'], array('test_form' => false));
                if (!isset($upload['error']) && isset($upload['url'])) {
                    update_user_meta($user_id, 'scs_company_logo', $upload['url']);
                }
            }

            wp_send_json_success(array('message' => 'Profile updated successfully.'));
        } else {
            wp_send_json_error(array('message' => 'User not found.'));
        }
    }
}
