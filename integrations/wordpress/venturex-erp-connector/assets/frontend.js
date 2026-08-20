jQuery(document).ready(function($) {
    $('#venturex-lead-form').on('submit', function(e) {
        e.preventDefault();

        var $form = $(this);
        var $btn = $form.find('button[type="submit"]');
        var $msg = $form.find('.venturex-form-message');

        $btn.prop('disabled', true).text('Sending...');
        $msg.hide();

        $.post(venturexFrontend.url, {
            action: 'venturex_submit_form',
            nonce: venturexFrontend.nonce,
            vx_name: $form.find('#vx_name').val(),
            vx_email: $form.find('#vx_email').val(),
            vx_phone: $form.find('#vx_phone').val(),
            vx_company: $form.find('#vx_company').val(),
            vx_message: $form.find('#vx_message').val()
        }, function(response) {
            $btn.prop('disabled', false).text('Send Message');
            if (response.success) {
                $msg.removeClass('venturex-form-message--error')
                    .addClass('venturex-form-message--success')
                    .text(response.data.message).show();
                $form[0].reset();
            } else {
                $msg.removeClass('venturex-form-message--success')
                    .addClass('venturex-form-message--error')
                    .text(response.data.message).show();
            }
        }).fail(function() {
            $btn.prop('disabled', false).text('Send Message');
            $msg.removeClass('venturex-form-message--success')
                .addClass('venturex-form-message--error')
                .text('An error occurred. Please try again later.').show();
        });
    });
});
