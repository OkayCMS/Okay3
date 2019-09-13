{$meta_title = $btr->okaycms__wayforpay__description_title|escape scope=global}

{*Название страницы*}
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {$btr->okaycms__liqpay__description_title|escape}
            </div>
        </div>
    </div>
    <div class="col-md-12 col-lg-12 col-sm-12 float-xs-right"></div>
</div>

<div class="row">
    <div class="col-xs-12">
        <div class="boxed">
            <div class="row d_flex">
                <div class="col-lg-12 col-md-12">
                    {$btr->okaycms__wayforpay__description_part_1}: <b>{url_generator route='OkayCMS_WayForPay_callback' absolute=1}</b> {$btr->okaycms__wayforpay__description_part_2}
                </div>
            </div>
            <br>
            <div>
                <img src="{$rootUrl}/Okay/Modules/OkayCMS/WayForPay/Backend/design/images/wayforpay.png">
            </div>
        </div>
    </div>
</div>