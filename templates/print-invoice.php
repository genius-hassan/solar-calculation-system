<?php
/*
* Template for Printing the Invoice
*/

if (!isset($_GET['invoice_id'])) {
    die('No invoice ID provided.');
}

$post_id = intval($_GET['invoice_id']);
$post = get_post($post_id);

if (!$post || $post->post_type !== 'solar_system') {
    die('Invalid Invoice.');
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

// Convert date format to day-month-year
$formatted_date = date('d-m-Y', strtotime($proposal_date));

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
?>

<!DOCTYPE html>
<html>
<head>
    <title>Print Invoice</title>
    <style>
        /* CSS for printable layout */
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            border: 1px solid #ddd;
            background-color: #fff;
            position: relative;
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
        .invoice-date {
            position: absolute;
            top: 20px;
            right: 20px;
            font-weight: bold;
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
        .invoice-summary {
            text-align: right;
            margin-top: 20px;
        }
        .print-button {
            display: none; /* Hide button in print mode */
        }
        @media print {
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
<div class="invoice-container">
    <!-- Date on the Top Right -->
    <div class="invoice-date">
        <p><?php echo esc_html($formatted_date); ?></p>
    </div>
    
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
            <?php 
            $items_total = 0;
            foreach ($quotation_details as $detail): 
                $items_total += $detail['total'];
            ?>
                <tr>
                    <td><?php echo esc_html($detail['details']); ?></td>
                    <td><?php echo esc_html($detail['specifications']); ?></td>
                    <td><?php echo esc_html($detail['quantity']); ?></td>
                    <td><?php echo number_format($detail['total'], 0, '.', ','); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="invoice-summary">
        <p><strong>Total for Items:</strong> <?php echo number_format($items_total, 0, '.', ','); ?></p>
    </div>

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
                <?php 
                $wires_total = 0;
                foreach ($wire_calculations as $wire): 
                    $wires_total += $wire['total'];
                ?>
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
        <div class="invoice-summary">
            <p><strong>Total for Wire Items:</strong> <?php echo number_format($wires_total, 0, '.', ','); ?></p>
        </div>
    <?php endif; ?>

    <!-- Grand Total and Per Watt Price -->
    <div class="invoice-summary">
        <p><strong>Grand Total:</strong> <?php echo number_format($items_total + $wires_total, 0, '.', ','); ?></p>
        <p><strong>Per Watt Price:</strong> <?php echo number_format($per_watt_price, 2, '.', ','); ?></p>
    </div>

    <div class="invoice-notes">
        <p><strong>NOTES:</strong> <?php echo esc_html($notes); ?></p>
    </div>
    <div class="print-button" onclick="window.print();">Print Quotation</div>
</div>
</body>
</html>
