<?php
class SCS_Frontend {

    public static function init() {
        add_shortcode('scs_solar_system_form', array(__CLASS__, 'render_solar_system_form'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_frontend_scripts'));
        add_action('wp_ajax_scs_save_solar_system', array(__CLASS__, 'save_solar_system'));
        add_action('wp_ajax_nopriv_scs_save_solar_system', array(__CLASS__, 'save_solar_system'));
    }

    public static function enqueue_frontend_scripts() {
        wp_enqueue_script('scs-frontend-js', SCS_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), '1.0.0', true);
        wp_localize_script('scs-frontend-js', 'scs_ajax', array('ajax_url' => admin_url('admin-ajax.php')));
    }

    public static function render_solar_system_form() {
        ob_start();
        ?>
        <form id="scs_solar_system_form">
            <div class="scs_customer_details">
                <label for="scs_project_size">Project Size</label>
                <input type="text" id="scs_project_size" name="scs_project_size" />

                <label for="scs_customer_name">Customer Name</label>
                <input type="text" id="scs_customer_name" name="scs_customer_name" />

                <label for="scs_address">Address</label>
                <input type="text" id="scs_address" name="scs_address" />

                <label for="scs_proposal_date">Proposal Date</label>
                <input type="date" id="scs_proposal_date" name="scs_proposal_date" />

                <label for="scs_notes">Notes</label>
                <textarea id="scs_notes" name="scs_notes"></textarea>
            </div>

            <div class="scs_quotation_details">
                <label>Quotation Details</label>
                <div id="scs_quotation_items">
                    <!-- Line items will be dynamically added here -->
                </div>
                <button type="button" id="scs_add_item">Add Item</button>

                <label for="scs_total_project_cost">Total Project Cost</label>
                <input type="number" id="scs_total_project_cost" name="scs_total_project_cost" readonly />

                <label for="scs_quotation_notes">Quotation Notes</label>
                <textarea id="scs_quotation_notes" name="scs_quotation_notes"></textarea>
            </div>

            <button type="submit">Save</button>
        </form>
        <?php
        return ob_get_clean();
    }

    public static function save_solar_system() {
        // Check nonce for security
        check_ajax_referer('scs_solar_system_nonce', 'security');

        $post_data = array(
            'post_title'   => sanitize_text_field($_POST['scs_customer_name']),
            'post_type'    => 'solar_system',
            'post_status'  => 'publish',
            'meta_input'   => array(
                'scs_project_size' => sanitize_text_field($_POST['scs_project_size']),
                'scs_address' => sanitize_text_field($_POST['scs_address']),
                'scs_proposal_date' => sanitize_text_field($_POST['scs_proposal_date']),
                'scs_notes' => sanitize_textarea_field($_POST['scs_notes']),
                'scs_quotation_details' => $_POST['scs_quotation_details'], // Ensure proper sanitization/validation
                'scs_total_project_cost' => floatval($_POST['scs_total_project_cost']),
                'scs_quotation_notes' => sanitize_textarea_field($_POST['scs_quotation_notes']),
            ),
        );

        $post_id = wp_insert_post($post_data);

        if ($post_id) {
            wp_send_json_success(array('message' => 'Solar System Estimate/Invoice saved successfully.'));
        } else {
            wp_send_json_error(array('message' => 'Failed to save Solar System Estimate/Invoice.'));
        }
    }
}
