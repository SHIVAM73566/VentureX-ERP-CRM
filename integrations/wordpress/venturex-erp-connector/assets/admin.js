jQuery(document).ready(function($) {
    $('#venturex-save').on('click', function() {
        var $btn = $(this);
        var $loading = $('#venturex-loading');
        $btn.prop('disabled', true);
        $loading.show();

        $.post(venturexAjax.url, {
            action: 'venturex_save_settings',
            nonce: venturexAjax.nonce,
            api_url: $('#venturex_api_url').val(),
            api_token: $('#venturex_api_token').val()
        }, function(response) {
            $btn.prop('disabled', false);
            $loading.hide();
            if (response.success) {
                alert('Settings saved successfully.');
            } else {
                alert('Error: ' + response.data);
            }
        }).fail(function() {
            $btn.prop('disabled', false);
            $loading.hide();
            alert('Request failed. Please try again.');
        });
    });

    $('#venturex-test').on('click', function() {
        var $btn = $(this);
        var $loading = $('#venturex-loading');
        $btn.prop('disabled', true);
        $loading.show();

        $.post(venturexAjax.url, {
            action: 'venturex_test_connection',
            nonce: venturexAjax.nonce
        }, function(response) {
            $btn.prop('disabled', false);
            $loading.hide();
            if (response.success) {
                $('#venturex-status').removeClass('venturex-status--disconnected')
                    .addClass('venturex-status--connected').text('Connected');
                alert('Connection successful!');
            } else {
                $('#venturex-status').removeClass('venturex-status--connected')
                    .addClass('venturex-status--disconnected').text('Disconnected');
                alert('Connection failed: ' + response.data);
            }
        }).fail(function() {
            $btn.prop('disabled', false);
            $loading.hide();
            alert('Request failed. Please try again.');
        });
    });
});
