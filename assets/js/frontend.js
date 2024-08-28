jQuery(document).ready(function($) {
    let itemIndex = 0;

    $('#scs_add_item').on('click', function() {
        let newRow = '<div class="scs_quotation_item">' +
            '<label>Specification</label>' +
            '<input type="text" name="scs_quotation_details[' + itemIndex + '][specifications]" />' +
            '<label>Quantity</label>' +
            '<input type="number" name="scs_quotation_details[' + itemIndex + '][quantity]" value="1" />' +
            '<label>Amount</label>' +
            '<input type="number" name="scs_quotation_details[' + itemIndex + '][amount]" value="0" />' +
            '<button type="button" class="scs_remove_item">Remove</button>' +
            '</div>';
        $('#scs_quotation_items').append(newRow);
        itemIndex++;
    });

    $(document).on('click', '.scs_remove_item', function() {
        $(this).closest('.scs_quotation_item').remove();
        calculateTotal();
    });

    $(document).on('input', '#scs_quotation_items input', function() {
        calculateTotal();
    });

    function calculateTotal() {
        let total = 0;
        $('#scs_quotation_items .scs_quotation_item').each(function() {
            let quantity = $(this).find('input[name*="quantity"]').val();
            let amount = $(this).find('input[name*="amount"]').val();
            total += (quantity * amount);
        });
        $('#scs_total_project_cost').val(total);
    }

    $('#scs_solar_system_form').on('submit', function(e) {
        e.preventDefault();

        let formData = $(this).serialize();
        formData += '&action=scs_save_solar_system&security=' + scs_ajax.security;

        $.post(scs_ajax.ajax_url, formData, function(response) {
            if (response.success) {
                alert(response.data.message);
            } else {
                alert(response.data.message);
            }
        });
    });
});
