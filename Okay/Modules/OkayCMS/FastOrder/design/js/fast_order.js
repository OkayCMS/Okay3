$(document).ready(function() {
    $(document).on('click', '.fn_fast_order_button', function (e) {
        e.preventDefault();

        let variant,
            form_obj = $(this).closest("form.fn_variants");

        $("#fast_order_product_name").html($(this).data('name'));
        if (form_obj.find('input[name=variant]:checked').length > 0) {
            variant = form_obj.find('input[name=variant]:checked').val();
        }

        if (form_obj.find('select[name=variant]').length > 0) {
            variant = form_obj.find('select').val();
        }

        $("#fast_order_variant_id").val(variant);

        $.fancybox.open({
            src: '#fn_fast_order',
            type : 'inline'
        });
    });

    $(document).on('click', '.fn_fast_order_submit', function (e) {
        e.preventDefault();

        let $form      = $("#fn_fast_order"),
            action     = $form.attr('action'),
            name       = $form.find('input[name="name"]').val(),
            phone      = $form.find('input[name="phone"]').val(),
            variant_id = $form.find('input[name="variant_id"]').val(),
            amount     = $('[name="amount"]').val();

        $.ajax({
            url: action,
            type: 'post',
            data: {
                action,
                name,
                phone,
                variant_id,
                amount
            },
            dataType: 'json'
        }).done(function(response) {
            if (response.hasOwnProperty('success') && response.hasOwnProperty('redirect_location')) {
                window.location = response.redirect_location;
            } else if (response.hasOwnProperty('errors')) {
                //$('#fast_order_name_error, #fast_order_phone_error').text('');
                $('.fn_validate_fast_name, .fn_validate_fast_phone').removeClass('error');

                if (response.errors.name) {
                    console.log(response.errors.name);
                    //$('#fast_order_name_error').text(response.errors.name);
                    $('.fn_validate_fast_name').addClass('error');
                }

                if (response.errors.phone) {
                    //$('#fast_order_phone_error').text(response.errors.phone);
                    $('.fn_validate_fast_phone').addClass('error');
                }
            }
        });
    });
});