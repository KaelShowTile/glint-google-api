jQuery(document).ready(function($) {
    // Initialize visibility
    function initVisibility() {
        // Toggle service settings
        const service = $('#google_service').val();
        if (service === 'google_sheet') {
            $('#google_sheet_settings').addClass('active');
            $('#service_actions_container').addClass('active');
        } else {
            $('#google_sheet_settings').removeClass('active');
            $('#service_actions_container').removeClass('active');
        }

        // Toggle action settings
        const action = $('#google_sheets_action').val();
        if (action === 'send_data_to_google_sheets') {
            $('#send_data_to_google_sheets_settings').addClass('active');
        } else {
            $('#send_data_to_google_sheets_settings').removeClass('active');
        }
    }

    // Set up event handlers
    function setupHandlers() {
        // Service change handler
        $('#google_service').change(function() {
            if ($(this).val() === 'google_sheet') {
                $('#google_sheet_settings').addClass('active');
                $('#service_actions_container').addClass('active');
            } else {
                $('#google_sheet_settings').removeClass('active');
                $('#service_actions_container').removeClass('active');
                // Reset action settings when service changes
                $('#google_sheets_action').val('');
                $('#send_data_to_google_sheets_settings').removeClass('active');
            }
        });

        // Action change handler
        $('#google_sheets_action').change(function() {
            if ($(this).val() === 'send_data_to_google_sheets') {
                $('#send_data_to_google_sheets_settings').addClass('active');
            } else {
                $('#send_data_to_google_sheets_settings').removeClass('active');
            }
        });

        // Add new parameter row
        $('.add-row').click(function() {
            const index = Date.now();
            const row = `
                <div class="repeater-row">
                    <input type="text" name="parameter_settings[${index}][column_name]" placeholder="Column header in sheet">
                    <input type="text" name="parameter_settings[${index}][wp_parameter]" placeholder="Parameter from WP">
                    <button type="button" class="remove-row">Remove</button>
                </div>
            `;
            $(this).prev('.repeater-rows').append(row);
        });

        // Remove parameter row
        $('.repeater-rows').on('click', '.remove-row', function() {
            $(this).closest('.repeater-row').remove();
        });
    }

    // Initialize
    initVisibility();
    setupHandlers();
});