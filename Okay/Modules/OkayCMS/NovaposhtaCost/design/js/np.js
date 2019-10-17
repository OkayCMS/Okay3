const configParamsObj = {
    //placeholder: 'Выберите город...', // Place holder text to place in the select
    minimumResultsForSearch: 3, // Overrides default of 15 set above
    width: 'resolve',
    matcher: function (params, data) {
        if ($.trim(params.term) === '') {
            return data;
        }
        if (data.text.toLowerCase().startsWith(params.term.toLowerCase())) {
            var modifiedData = $.extend({}, data, true);
            return modifiedData;
        }
        return null;
    }
};

init();
$('select.city_novaposhta').select2(configParamsObj);

$(document).on('change', 'select.fn_select_warehouses_novaposhta', set_warehouse);
$(document).on('change', 'input[name="novaposhta_redelivery"]', calc_delivery_price);

function init() {

    let delivery_block = $('.fn_delivery_novaposhta').closest('.delivery__item');
    let city = $(".city_novaposhta");
    let city_ref = delivery_block.find('input[name="novaposhta_delivery_city_id"]').val();
    
    $('select.city_novaposhta').closest('.delivery_wrap').find('span.deliver_price').text('');
    $.ajax({
        url: okay.router['OkayCMS_NovaposhtaCost_get_cities'],
        data: {method: 'get_cities', selected_city: city_ref},
        dataType: 'json',
        success: function(data) {
            if(data.cities_response.success == 1){
                city.html(data.cities_response.cities);
                $(document).on('change', 'input[name="delivery_id"]', calc_delivery_price);
                $(document).on('change', 'select.city_novaposhta', calc_delivery_price);
            }
            $('select.city_novaposhta option:first').attr('notselected', 'notselected');
            $('select.city_novaposhta option:first').text('Выберите город...');
            if (city_ref) {
                calc_delivery_price();
            }
            $('.np_preloader').remove();
        }
    });
}

function calc_delivery_price() {
    
    let active_delivery = $('input[name="delivery_id"]:checked');
    if (active_delivery.data('module_id') == okay.np_delivery_module_id) {
        $('#fn_total_delivery_price').text('');
    } else {
        return false;
    }
    
    let remove_warehouse = false;
    let delivery_block = active_delivery.closest('.delivery__item');
    let price_elem = delivery_block.find('.fn_delivery_price');
    let term_elem = delivery_block.find('.term_novaposhta span');
    let warehouses_block = delivery_block.find('.warehouses_novaposhta');
    let delivery_id = active_delivery.val();
    let city_novaposhta = delivery_block.find('select.city_novaposhta').children(':selected').data('city_ref');
    let city_ref = delivery_block.find('input[name="novaposhta_delivery_city_id"]').val();
    
    if (city_novaposhta != city_ref) {
        remove_warehouse = true;
    }
    if (city_ref && !city_novaposhta) {
        city_novaposhta = city_ref;
    }
    
    var payment_method_id = $('input[name="payment_method_id"]:checked').val();

    var redelivery = 0;
    
    if (delivery_block.find('input[name="novaposhta_redelivery"]').is(':checked')){
        redelivery = delivery_block.find('input[name="novaposhta_redelivery"]').val();
    }

    if (city_novaposhta) {
        delivery_block.find('input[name="novaposhta_delivery_city_id"]').val(city_novaposhta);
        if (remove_warehouse) {
            delivery_block.find('input[name="novaposhta_delivery_warehouse_id"]').val('')
        }
        let warehouse_ref = delivery_block.find('input[name="novaposhta_delivery_warehouse_id"]').val();
        
        price_elem.text('Вычисляем...');
        $('#fn_total_delivery_price').text('Вычисляем...');
        term_elem.text('');

        delivery_block.find('input[name="novaposhta_delivery_price"]').val('');
        delivery_block.find('input[name="novaposhta_delivery_term"]').val('');
        $.ajax({
            url: okay.router['OkayCMS_NovaposhtaCost_calc'],
            data: {city: city_novaposhta, redelivery: redelivery, warehouse: warehouse_ref, delivery_id: delivery_id},
            dataType: 'json',
            success: function(data) {
                if (data.price_response.success) {
                    price_elem.text(data.price_response.price_formatted);
                    delivery_block.find('input[name="novaposhta_delivery_price"]').val(data.price_response.price);
                    delivery_block.find('input[name="delivery_id"]').data('total_price', data.price_response.cart_total_price)
                        .data('delivery_price', data.price_response.price );
                    
                    /*if (data.price_response.payments_tpl) {
                        $('#fn_delivery_payment_'+data.price_response.delivery_id).html(data.price_response.payments_tpl).show();
                        $('input[name="payment_method_id"]#payment_'+data.price_response.delivery_id+'_'+payment_method_id).trigger('click');
                    }*/
                    okay.change_payment_method();
                }
                
                if (data.term_response.success) {
                    delivery_block.find('input[name="novaposhta_delivery_term"]').val(data.term_response.term);
                    term_elem.text(data.term_response.term);
                    term_elem.parent().show();
                } else {
                    term_elem.parent().hide();
                }
                if (data.warehouses_response.success) {
                    warehouses_block.show();
                    warehouses_block.find('.fn_select_warehouses_novaposhta')
                        .html(data.warehouses_response.warehouses)
                        .attr('disabled', false)
                        .select2(configParamsObj);
                } else {
                    warehouses_block.hide();
                    warehouses_block.find('.fn_select_warehouses_novaposhta')
                        .html('')
                        .attr('disabled', true);
                }
            }
        });
    }
}

function set_warehouse() {
    if ($(this).val() != '') {
        $('input[name="address"]').trigger('focus');
        let city_name = $('select.city_novaposhta').children(':selected').val(),
            warehouse_name = $(this).val(),
            delivery_address = city_name + ', ' + warehouse_name;
        $('input[name="address"]').val(delivery_address);
        $('input[name="novaposhta_delivery_warehouse_id"]').val($(this).children(':selected').data('warehouse_ref'));
    }
}