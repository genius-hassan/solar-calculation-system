jQuery(document).ready(function($) {
    let itemIndex = 1;
    let wireIndex = 1;

    $('#scs_add_item').on('click', function() {
        let newRow = '<tr class="scs_quotation_item">' +
            '<td><input type="text" name="scs_quotation_details[' + itemIndex + '][details]" /></td>' +
            '<td><input type="text" name="scs_quotation_details[' + itemIndex + '][specifications]" /></td>' +
            '<td><input type="number" name="scs_quotation_details[' + itemIndex + '][quantity]" value="1" /></td>' +
            '<td><input type="number" name="scs_quotation_details[' + itemIndex + '][rate]" value="0" /></td>' +
            '<td><input type="number" name="scs_quotation_details[' + itemIndex + '][total]" value="0" readonly /></td>' +
            '<td><button type="button" class="scs_remove_item">❌</button></td>' +
            '</tr>';
        $('#scs_quotation_table tbody').append(newRow);
        itemIndex++;
    });

    $('#scs_add_wire_item').on('click', function() {
        let newWireRow = '<tr class="scs_wire_item">' +
            '<td><input type="text" name="scs_wire_calculations[' + wireIndex + '][type]" value="Wiring DC" /></td>' +
            '<td><input type="text" name="scs_wire_calculations[' + wireIndex + '][specifications]" /></td>' +
            '<td><input type="number" name="scs_wire_calculations[' + wireIndex + '][length]" value="0" /></td>' +
            '<td><input type="number" name="scs_wire_calculations[' + wireIndex + '][unit_price]" value="0" /></td>' +
            '<td><input type="number" name="scs_wire_calculations[' + wireIndex + '][total]" value="0" readonly /></td>' +
            '<td><button type="button" class="scs_remove_item">❌</button></td>' +
            '</tr>';
        $('#scs_wire_calculations_table tbody').append(newWireRow);
        wireIndex++;
    });

    $(document).on('click', '.scs_remove_item', function() {
        $(this).closest('tr').remove();
        calculateTotal();
        calculateWireTotal();
        calculateGrandTotal();
    });

    $(document).on('input', '#scs_quotation_table input', function() {
        let $row = $(this).closest('tr');
        let quantity = parseFloat($row.find('input[name*="quantity"]').val()) || 0;
        let rate = parseFloat($row.find('input[name*="rate"]').val()) || 0;
        let total = quantity * rate;
        $row.find('input[name*="total"]').val(total);
        calculateTotal();
        calculateGrandTotal();
    });

    $(document).on('input', '#scs_wire_calculations_table input', function() {
        let $row = $(this).closest('tr');
        let length = parseFloat($row.find('input[name*="length"]').val()) || 0;
        let unitPrice = parseFloat($row.find('input[name*="unit_price"]').val()) || 0;
        let total = length * unitPrice;
        $row.find('input[name*="total"]').val(total);
        calculateWireTotal();
        calculateGrandTotal();
    });

    function calculateTotal() {
        let total = 0;
        $('#scs_quotation_table .scs_quotation_item').each(function() {
            let itemTotal = parseFloat($(this).find('input[name*="total"]').val()) || 0;
            total += itemTotal;
        });
        $('#scs_total_project_cost').val(total);
    }

    function calculateWireTotal() {
        let total = 0;
        $('#scs_wire_calculations_table .scs_wire_item').each(function() {
            let itemTotal = parseFloat($(this).find('input[name*="total"]').val()) || 0;
            total += itemTotal;
        });
        $('#scs_total_wire_cost').val(total);
    }

    function calculateGrandTotal() {
        let totalProjectCost = parseFloat($('#scs_total_project_cost').val()) || 0;
        let totalWireCost = parseFloat($('#scs_total_wire_cost').val()) || 0;
        $('#scs_grand_total').val(totalProjectCost + totalWireCost);
    }

    $('#scs_solar_system_form').on('submit', function(e) {
		e.preventDefault();

		let formData = $(this).serialize();
		formData += '&action=scs_save_solar_system&security=' + scs_ajax.security;

		$.post(scs_ajax.ajax_url, formData, function(response) {
			if (response.success) {
				// If the response is successful, redirect to the View All section
				window.location.href = response.data.redirect_url;
			} else {
				alert(response.data.message);
			}
		});
	});
	
	// Profile updateCommands
	$('#scs-profile-setup-form').on('submit', function(e) {
		e.preventDefault();

		let formData = new FormData(this);
		formData.append('action', 'scs_save_profile');
		formData.append('security', scs_ajax.security);

		$.ajax({
			url: scs_ajax.ajax_url,
			type: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				if (response.success) {
					alert(response.data.message);
					// Optionally, reload the page or redirect to another section
					location.reload();
				} else {
					alert(response.data.message);
				}
			}
		});
	});


});
