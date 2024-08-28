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
			<!-- Customer Details Section -->
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
						<tr class="scs_quotation_item">
							<td><input type="text" name="scs_quotation_details[0][details]" /></td>
							<td><input type="text" name="scs_quotation_details[0][specifications]" /></td>
							<td><input type="number" name="scs_quotation_details[0][quantity]" value="1" /></td>
							<td><input type="number" name="scs_quotation_details[0][rate]" value="0" /></td>
							<td><input type="number" name="scs_quotation_details[0][total]" value="0" readonly /></td>
							<td><button type="button" class="scs_remove_item">❌</button></td>
						</tr>
					</tbody>
				</table>
				<button type="button" id="scs_add_item">Add Item</button>

				<label for="scs_total_project_cost">Total Project Cost</label>
				<input type="number" id="scs_total_project_cost" name="scs_total_project_cost" readonly />
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
						<tr class="scs_wire_item">
							<td><input type="text" name="scs_wire_calculations[0][type]" value="Wiring DC" /></td>
							<td><input type="text" name="scs_wire_calculations[0][specifications]" /></td>
							<td><input type="number" name="scs_wire_calculations[0][length]" value="0" /></td>
							<td><input type="number" name="scs_wire_calculations[0][unit_price]" value="0" /></td>
							<td><input type="number" name="scs_wire_calculations[0][total]" value="0" readonly /></td>
							<td><button type="button" class="scs_remove_item">❌</button></td>
						</tr>
					</tbody>
				</table>
				<button type="button" id="scs_add_wire_item">Add Wire Item</button>

				<label for="scs_total_wire_cost">Total Wire Cost</label>
				<input type="number" id="scs_total_wire_cost" name="scs_total_wire_cost" readonly />
			</div>

			<!-- Master Total Section -->
			<div class="scs_master_total">
				<h3>Grand Total</h3>
				<label for="scs_grand_total">Grand Total (Items + Wire)</label>
				<input type="number" id="scs_grand_total" name="scs_grand_total" readonly />
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
