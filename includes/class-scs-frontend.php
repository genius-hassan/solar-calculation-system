<?php
class SCS_Frontend {

    public static function init() {
        add_shortcode('scs_solar_system_form', array(__CLASS__, 'render_solar_system_form'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_frontend_scripts'));
        add_action('wp_ajax_scs_save_solar_system', array(__CLASS__, 'save_solar_system'));
        add_action('wp_ajax_nopriv_scs_save_solar_system', array(__CLASS__, 'save_solar_system'));
    }

    public static function enqueue_frontend_scripts() {
        wp_enqueue_style('scs-frontend-css', SCS_PLUGIN_URL . 'assets/css/frontend.css', array(), '1.0.0');
        wp_enqueue_script('scs-frontend-js', SCS_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), '1.0.0', true);
        wp_localize_script('scs-frontend-js', 'scs_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'security' => wp_create_nonce('scs_solar_system_nonce'),
        ));
    }

    public static function render_solar_system_form($form_data = array(), $post_id = 0) {
        // If form data is provided, use it to pre-fill the form
        $customer_name = isset($form_data['scs_customer_name']) ? $form_data['scs_customer_name'] : '';
        $project_size = isset($form_data['scs_project_size']) ? $form_data['scs_project_size'] : '';
        $address = isset($form_data['scs_address']) ? $form_data['scs_address'] : '';
        $proposal_date = isset($form_data['scs_proposal_date']) ? $form_data['scs_proposal_date'] : '';
        $notes = isset($form_data['scs_notes']) ? $form_data['scs_notes'] : '';
        $quotation_details = isset($form_data['scs_quotation_details']) ? $form_data['scs_quotation_details'] : array();
        $total_project_cost = isset($form_data['scs_total_project_cost']) ? $form_data['scs_total_project_cost'] : '';
        $wire_calculations = isset($form_data['scs_wire_calculations']) ? $form_data['scs_wire_calculations'] : array();
        $total_wire_cost = isset($form_data['scs_total_wire_cost']) ? $form_data['scs_total_wire_cost'] : '';
        $grand_total = isset($form_data['scs_grand_total']) ? $form_data['scs_grand_total'] : '';
        $status_type = isset($form_data['scs_status_type']) ? $form_data['scs_status_type'] : 'estimate';

        ob_start();
        ?>
        <form id="scs_solar_system_form">
            <input type="hidden" name="scs_post_id" value="<?php echo esc_attr($post_id); ?>" />
            
            <!-- Adjusted Customer Name Position -->
            <div class="scs_customer_details">
                <label for="scs_customer_name">Customer Name</label>
                <input type="text" id="scs_customer_name" name="scs_customer_name" value="<?php echo esc_attr($customer_name); ?>" />

                <label for="scs_project_size">Project Size</label>
                <input type="text" id="scs_project_size" name="scs_project_size" value="<?php echo esc_attr($project_size); ?>" />

                <label for="scs_address">Address</label>
                <input type="text" id="scs_address" name="scs_address" value="<?php echo esc_attr($address); ?>" />

                <label for="scs_proposal_date">Proposal Date</label>
                <input type="date" id="scs_proposal_date" name="scs_proposal_date" value="<?php echo esc_attr($proposal_date); ?>" />

                <label for="scs_notes">Notes</label>
                <textarea id="scs_notes" name="scs_notes"><?php echo esc_textarea($notes); ?></textarea>
            </div>

            <!-- Quotation Details Section -->
            <div class="scs_quotation_details">
                <h3>Quotation Details</h3>
                <table id="scs_quotation_table">
                    <thead>
                        <tr>
                            <th>Item Details</th>
                            <th>Specification</th>
                            <th>Quantity</th>
                            <th>Rate</th>
                            <th>Total</th>
                            <th></th> <!-- Empty for remove button -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($quotation_details)) {
                            foreach ($quotation_details as $index => $item) {
                                ?>
                                <tr class="scs_quotation_item">
                                    <td><input type="text" name="scs_quotation_details[<?php echo $index; ?>][details]" value="<?php echo esc_attr($item['details']); ?>" /></td>
                                    <td><input type="text" name="scs_quotation_details[<?php echo $index; ?>][specifications]" value="<?php echo esc_attr($item['specifications']); ?>" /></td>
                                    <td><input type="number" name="scs_quotation_details[<?php echo $index; ?>][quantity]" value="<?php echo esc_attr($item['quantity']); ?>" /></td>
                                    <td><input type="number" name="scs_quotation_details[<?php echo $index; ?>][rate]" value="<?php echo esc_attr($item['rate']); ?>" /></td>
                                    <td><input type="number" name="scs_quotation_details[<?php echo $index; ?>][total]" value="<?php echo esc_attr($item['total']); ?>" readonly /></td>
                                    <td><button type="button" class="scs_remove_item">❌</button></td>
                                </tr>
                                <?php
                            }
                        } else {
                            // Render an empty row for a new estimate/invoice
                            ?>
                            <tr class="scs_quotation_item">
                                <td><input type="text" name="scs_quotation_details[0][details]" /></td>
                                <td><input type="text" name="scs_quotation_details[0][specifications]" /></td>
                                <td><input type="number" name="scs_quotation_details[0][quantity]" value="1" /></td>
                                <td><input type="number" name="scs_quotation_details[0][rate]" value="0" /></td>
                                <td><input type="number" name="scs_quotation_details[0][total]" value="0" readonly /></td>
                                <td><button type="button" class="scs_remove_item">❌</button></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
                <button type="button" id="scs_add_item">Add Item</button>

                <label for="scs_total_project_cost">Total Project Cost</label>
                <input type="number" id="scs_total_project_cost" name="scs_total_project_cost" value="<?php echo esc_attr($total_project_cost); ?>" readonly />
            </div>

            <!-- Wire Calculations Section -->
            <div class="scs_wire_calculations">
                <h3>Wire Calculations</h3>
                <table id="scs_wire_calculations_table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Specification</th>
                            <th>Length (mtrs)</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                            <th></th> <!-- Empty for remove button -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if (!empty($wire_calculations)) {
                            foreach ($wire_calculations as $index => $wire) {
                                ?>
                                <tr class="scs_wire_item">
                                    <td><input type="text" name="scs_wire_calculations[<?php echo $index; ?>][type]" value="<?php echo esc_attr($wire['type']); ?>" /></td>
                                    <td><input type="text" name="scs_wire_calculations[<?php echo $index; ?>][specifications]" value="<?php echo esc_attr($wire['specifications']); ?>" /></td>
                                    <td><input type="number" name="scs_wire_calculations[<?php echo $index; ?>][length]" value="<?php echo esc_attr($wire['length']); ?>" /></td>
                                    <td><input type="number" name="scs_wire_calculations[<?php echo $index; ?>][unit_price]" value="<?php echo esc_attr($wire['unit_price']); ?>" /></td>
                                    <td><input type="number" name="scs_wire_calculations[<?php echo $index; ?>][total]" value="<?php echo esc_attr($wire['total']); ?>" readonly /></td>
                                    <td><button type="button" class="scs_remove_item">❌</button></td>
                                </tr>
                                <?php
                            }
                        } else {
                            // Render an empty row for a new wire calculation
                            ?>
                            <tr class="scs_wire_item">
                                <td><input type="text" name="scs_wire_calculations[0][type]" value="Wiring DC" /></td>
                                <td><input type="text" name="scs_wire_calculations[0][specifications]" /></td>
                                <td><input type="number" name="scs_wire_calculations[0][length]" value="0" /></td>
                                <td><input type="number" name="scs_wire_calculations[0][unit_price]" value="0" /></td>
                                <td><input type="number" name="scs_wire_calculations[0][total]" value="0" readonly /></td>
                                <td><button type="button" class="scs_remove_item">❌</button></td>
                            </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
                <button type="button" id="scs_add_wire_item">Add Wire Item</button>

                <label for="scs_total_wire_cost">Total Wire Cost</label>
                <input type="number" id="scs_total_wire_cost" name="scs_total_wire_cost" value="<?php echo esc_attr($total_wire_cost); ?>" readonly />
            </div>

            <!-- Dropdown for Estimate/Invoice -->
            <div class="scs_status_type">
                <label for="scs_status_type">Status Type</label>
                <select id="scs_status_type" name="scs_status_type">
                    <option value="estimate" <?php selected($status_type, 'estimate'); ?>>Estimate</option>
                    <option value="invoice" <?php selected($status_type, 'invoice'); ?>>Invoice</option>
                </select>
            </div>

            <!-- Master Total Section -->
            <div class="scs_master_total">
                <h3>Grand Total</h3>
                <label for="scs_grand_total">Grand Total (Items + Wire)</label>
                <input type="number" id="scs_grand_total" name="scs_grand_total" value="<?php echo esc_attr($grand_total); ?>" readonly />
            </div>

            <button type="submit">Save</button>
        </form>
        <?php
        return ob_get_clean();
    }

    public static function save_solar_system() {
        if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'scs_solar_system_nonce')) {
            wp_send_json_error(array('message' => 'Invalid nonce.'));
            return;
        }

        // Check if we're updating an existing post or creating a new one
        $post_id = isset($_POST['scs_post_id']) && intval($_POST['scs_post_id']) > 0 ? intval($_POST['scs_post_id']) : 0;

        $post_data = array(
            'post_title'   => sanitize_text_field($_POST['scs_customer_name']),
            'post_type'    => 'solar_system',
            'post_status'  => 'publish',
            'meta_input'   => array(
                'scs_customer_name'   => sanitize_text_field($_POST['scs_customer_name']),
                'scs_project_size' => sanitize_text_field($_POST['scs_project_size']),
                'scs_address' => sanitize_text_field($_POST['scs_address']),
                'scs_proposal_date' => sanitize_text_field($_POST['scs_proposal_date']),
                'scs_notes' => sanitize_textarea_field($_POST['scs_notes']),
                'scs_quotation_details' => $_POST['scs_quotation_details'], // Ensure proper sanitization/validation
                'scs_total_project_cost' => floatval($_POST['scs_total_project_cost']),
                'scs_wire_calculations' => $_POST['scs_wire_calculations'], // Ensure proper sanitization/validation
                'scs_total_wire_cost' => floatval($_POST['scs_total_wire_cost']),
                'scs_grand_total' => floatval($_POST['scs_grand_total']),
            ),
        );

        if ($post_id > 0) {
            // Updating existing post
            $post_data['ID'] = $post_id;
            $result = wp_update_post($post_data);
        } else {
            // Creating a new post
            $result = wp_insert_post($post_data);
        }

        if ($result) {
            // Handle the taxonomy term
            $status_type = sanitize_text_field($_POST['scs_status_type']);

            // Check if the term exists, if not, create it
            if (!term_exists($status_type, 'scs_type')) {
                $new_term = wp_insert_term($status_type, 'scs_type');
                $status_type_id = $new_term['term_id'];
            } else {
                $term = get_term_by('name', $status_type, 'scs_type');
                $status_type_id = $term->term_id;
            }

            // Set the term for the post
            wp_set_post_terms($result, $status_type_id, 'scs_type');

			wp_send_json_success(array('redirect_url' => add_query_arg('section', 'estimates', home_url('/solar-system-dashboard'))));
        } else {
            wp_send_json_error(array('message' => 'Failed to save Solar System.'));
        }
    }
}
