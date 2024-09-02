<?php
class SCS_Dashboard {

    public static function init() {
        add_shortcode('scs_dashboard', array(__CLASS__, 'render_dashboard'));
    }

    public static function render_dashboard() {
		// First, check if the user is logged in
		if (!is_user_logged_in()) {
			wp_redirect(home_url('/login'));
			exit;
		}

		// Get the current user
		$user = wp_get_current_user();

		// Get the current section
		$current_section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'profile';

		// Check if the user has the correct roles
		if (in_array('administrator', (array) $user->roles) || in_array('solar_technician', (array) $user->roles)) {
			// User has access, render the dashboard
			ob_start();
			?>
			<div id="scs-dashboard-wrapper">
				<aside id="scs-dashboard-nav">
					<ul>
						<li><a href="<?php echo add_query_arg('section', 'profile', home_url('/solar-system-dashboard')); ?>" class="<?php echo ($current_section == 'profile') ? 'active' : ''; ?>">Profile Setup</a></li>
						<li><a href="<?php echo add_query_arg('section', 'estimates', home_url('/solar-system-dashboard')); ?>" class="<?php echo ($current_section == 'estimates') ? 'active' : ''; ?>">View Invoices</a></li>
						<li><a href="<?php echo add_query_arg('section', 'create', home_url('/solar-system-dashboard')); ?>" class="<?php echo ($current_section == 'create') ? 'active' : ''; ?>">Create Invoice</a></li>
					</ul>
				</aside>

				<section id="scs-dashboard-content">
					<?php self::render_dashboard_section(); ?>
				</section>
			</div>
			<?php
			return ob_get_clean();
		} else {
			// User does not have access
			return 'You do not have access to this page.';
		}
	}
	
	public static function render_print_template($post_id) {
        $post = get_post($post_id);

        if (!$post || $post->post_type !== 'solar_system') {
            return 'Invalid Invoice';
        }

        $customer_name = get_post_meta($post_id, 'scs_customer_name', true);
        $address = get_post_meta($post_id, 'scs_address', true);
        $project_size = get_post_meta($post_id, 'scs_project_size', true);
        $proposal_date = get_post_meta($post_id, 'scs_proposal_date', true);
        $notes = get_post_meta($post_id, 'scs_notes', true);
        $quotation_details = get_post_meta($post_id, 'scs_quotation_details', true);
        $total_project_cost = get_post_meta($post_id, 'scs_total_project_cost', true);
        $wire_calculations = get_post_meta($post_id, 'scs_wire_calculations', true);
        $total_wire_cost = get_post_meta($post_id, 'scs_total_wire_cost', true);

        // Calculate per watt price
        $dc_system_size = floatval(str_replace('KW', '', $project_size)) * 1000; // Convert to watts if needed
        $per_watt_price = $dc_system_size > 0 ? $total_project_cost / $dc_system_size : 0;

        // Retrieve company details from the user profile
        $user_id = $post->post_author;
        $company_name = get_user_meta($user_id, 'scs_company_name', true);
        $owner_name = get_user_meta($user_id, 'scs_owner_name', true);
        $company_address = get_user_meta($user_id, 'scs_company_address', true);
        $company_phone = get_user_meta($user_id, 'scs_company_phone', true);
        $company_logo = get_user_meta($user_id, 'scs_company_logo', true);

        ob_start();
        ?>
        <html>
        <head>
            <style>
                @media print {
                    body {
                        margin: 0;
                        padding: 20px;
                        color: #333;
                        font-family: Arial, sans-serif;
                    }
                    .invoice-container {
                        max-width: 800px;
                        margin: 0 auto;
                        padding: 20px;
                        border: 1px solid #ddd;
                        background-color: #fff;
                    }
                    .invoice-header {
                        text-align: center;
                        margin-bottom: 40px;
                    }
                    .invoice-header img {
                        max-width: 150px;
                        margin-bottom: 20px;
                    }
                    .invoice-header h1 {
                        margin: 0;
                        font-size: 24px;
                        color: #d90000;
                    }
                    .invoice-info, .invoice-total, .invoice-notes {
                        margin-bottom: 20px;
                    }
                    .invoice-info p, .invoice-total p {
                        margin: 5px 0;
                    }
                    .invoice-table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-bottom: 20px;
                    }
                    .invoice-table th, .invoice-table td {
                        border: 1px solid #ddd;
                        padding: 8px;
                        text-align: left;
                    }
                    .invoice-table th {
                        background-color: #f9f9f9;
                        font-weight: bold;
                    }
                    .print-button {
                        display: none; /* Hide button in print mode */
                    }
                }
                /* Hide non-printable elements */
                #wpadminbar, .site-header, .site-footer, .sidebar, #scs-dashboard-nav, header, footer {
                    display: none !important;
                }
				.entry-content {
					padding: 0 !important;
					margin: 0 auto !important;
					width: 100%;
				}
            </style>
        </head>
        <body>
        <div class="invoice-container">
            <div class="invoice-header">
                <?php if ($company_logo): ?>
                    <img src="<?php echo esc_url($company_logo); ?>" alt="Company Logo">
                <?php endif; ?>
                <h1>QUOTATION</h1>
            </div>
            <div class="invoice-info">
                <p><strong>Customer Name:</strong> <?php echo esc_html($customer_name); ?></p>
                <p><strong>Site Address:</strong> <?php echo esc_html($address); ?></p>
                <p><strong>DC System Size:</strong> <?php echo esc_html($project_size); ?></p>
                <p><strong>AC System Size:</strong> 15,000 Watts (On Grid)</p> <!-- Adjust as needed -->
                <p><strong>Quotation Date:</strong> <?php echo esc_html($proposal_date); ?></p>
            </div>
            <table class="invoice-table">
                <thead>
                    <tr>
                        <th>Item Details</th>
                        <th>Specifications</th>
                        <th>Quantity</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($quotation_details as $detail): ?>
                        <tr>
                            <td><?php echo esc_html($detail['details']); ?></td>
                            <td><?php echo esc_html($detail['specifications']); ?></td>
                            <td><?php echo esc_html($detail['quantity']); ?></td>
                            <td><?php echo number_format($detail['total'], 0, '.', ','); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Wire Calculations Section -->
            <?php if (!empty($wire_calculations)): ?>
                <h3>Wire Calculations</h3>
                <table class="invoice-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Specification</th>
                            <th>Length (mtrs)</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($wire_calculations as $wire): ?>
                            <tr>
                                <td><?php echo esc_html($wire['type']); ?></td>
                                <td><?php echo esc_html($wire['specifications']); ?></td>
                                <td><?php echo esc_html($wire['length']); ?></td>
                                <td><?php echo number_format($wire['unit_price'], 0, '.', ','); ?></td>
                                <td><?php echo number_format($wire['total'], 0, '.', ','); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <div class="invoice-total">
                <p><strong>Total Project Cost:</strong> <?php echo number_format($total_project_cost, 0, '.', ','); ?></p>
                <p><strong>Per Watt Price:</strong> <?php echo number_format($per_watt_price, 2, '.', ','); ?></p>
            </div>
            <div class="invoice-notes">
                <p><strong>NOTES:</strong> <?php echo esc_html($notes); ?></p>
            </div>
            <div class="print-button" onclick="window.print();">Print Quotation</div>
        </div>
        </body>
        </html>
        <?php
        return ob_get_clean();
    }


    public static function render_dashboard_section() {
		$section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : '';

		switch ($section) {
			case 'profile':
				echo self::render_profile_setup();
				break;
			case 'estimates':
				echo self::render_estimates_invoices();
				break;
			case 'create':
				echo self::render_create_estimate_invoice();
				break;
			case 'print':
				$post_id = isset($_GET['invoice_id']) ? intval($_GET['invoice_id']) : 0;
				echo self::render_print_template($post_id);
				break;
			default:
				echo '<p>Welcome to the Solar System Dashboard. Please choose an option from the menu.</p>';
				break;
		}
	}


    public static function render_profile_setup() {
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

            <input type="submit" name="scs_save_profile" value="Save Profile" />
        </form>
        <?php
        return ob_get_clean();
    }

    public static function render_estimates_invoices() {
		$user_id = get_current_user_id();

		$args = array(
			'post_type' => 'solar_system',
			'author'    => $user_id,
			'posts_per_page' => -1,
		);

		$invoices = get_posts($args);

		if ($invoices) {
			echo '<table>';
			echo '<thead>';
			echo '<tr>';
			echo '<th>Invoice Number</th>';
			echo '<th>Customer Name</th>';
			echo '<th>Address</th>';
			echo '<th>System Size</th>';
			echo '<th>Edit</th>';
			echo '<th>Print</th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';

			foreach ($invoices as $invoice) {
				$customer_name = get_post_meta($invoice->ID, 'scs_customer_name', true);
				$address = get_post_meta($invoice->ID, 'scs_address', true);
				$system_size = get_post_meta($invoice->ID, 'scs_project_size', true);

				echo '<tr>';
				echo '<td>' . esc_html($invoice->ID) . '</td>';
				echo '<td>' . esc_html($customer_name) . '</td>';
				echo '<td>' . esc_html($address) . '</td>';
				echo '<td>' . esc_html($system_size) . '</td>';
				echo '<td><a href="' . add_query_arg(array('section' => 'create', 'invoice_id' => $invoice->ID), home_url('/solar-system-dashboard')) . '">Edit</a></td>';
				echo '<td><a href="' . home_url('/print-invoice?invoice_id=' . $invoice->ID) . '" target="_blank">Print</a></td>';
				echo '</tr>';
			}

			echo '</tbody>';
			echo '</table>';
		} else {
			echo '<p>No estimates or invoices found.</p>';
		}
	}


    public static function render_create_estimate_invoice($post_id = 0) {
        if ($post_id > 0) {
            // Load existing data for the invoice
            $post = get_post($post_id);

            if ($post && $post->post_type === 'solar_system') {
                // Load the data into the form
                $form_data = array(
                    'scs_project_size' => get_post_meta($post_id, 'scs_project_size', true),
                    'scs_customer_name' => get_post_meta($post_id, 'scs_customer_name', true),
                    'scs_address' => get_post_meta($post_id, 'scs_address', true),
                    'scs_proposal_date' => get_post_meta($post_id, 'scs_proposal_date', true),
                    'scs_notes' => get_post_meta($post_id, 'scs_notes', true),
                    'scs_quotation_details' => get_post_meta($post_id, 'scs_quotation_details', true),
                    'scs_total_project_cost' => get_post_meta($post_id, 'scs_total_project_cost', true),
                    'scs_wire_calculations' => get_post_meta($post_id, 'scs_wire_calculations', true),
                    'scs_total_wire_cost' => get_post_meta($post_id, 'scs_total_wire_cost', true),
                    'scs_grand_total' => get_post_meta($post_id, 'scs_grand_total', true),
                    'scs_status_type' => wp_get_post_terms($post_id, 'scs_type', array('fields' => 'names'))[0],
                );

                // Render the form with the loaded data
                echo SCS_Frontend::render_solar_system_form($form_data, $post_id);
                return;
            }
        }

        // If no post ID, render an empty form for creating a new estimate/invoice
        echo SCS_Frontend::render_solar_system_form();
    }
}
