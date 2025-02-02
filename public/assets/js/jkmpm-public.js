jQuery(document).ready(function($) {
    // Handle week navigation
    $('.jkmpm-prev-week, .jkmpm-next-week').on('click', function(e) {
        e.preventDefault();
        var offset = $(this).data('offset');
        updateSchedule(offset);
    });

    function updateSchedule(weekOffset) {
        $.ajax({
            url: jkmpm_public_var.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_schedule',
                week_offset: weekOffset, //to upgrade offset value
                nonce: jkmpm_public_var.ajax_nonce
            },
            beforeSend: function() {
                $('.jkmpm-schedule-wrapper').addClass('loading');
            },
            success: function(response) {
                if (response.success) {
                    $('.jkmpm-schedule-wrapper').html(response.data);
                    // Reinitialize event handlers
                    initializeHandlers();
                } else {
                    console.error('Error loading schedule:', response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('Ajax error:', error);
            },
            complete: function() {
                $('.jkmpm-schedule-wrapper').removeClass('loading');
            }
        });
    }

    function initializeHandlers() {
        // Re-render new offset
        $('.jkmpm-prev-week, .jkmpm-next-week').on('click', function(e) {
            e.preventDefault();
            var offset = $(this).data('offset');
            updateSchedule(offset);
        });
    }
});