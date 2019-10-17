{$meta_title = $btr->okay_cms__fast_order__title|escape scope=global}

{*Название страницы*}
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {$btr->okay_cms__fast_order__title|escape}
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
                    {$btr->okay_cms__fast_order__description|escape}
                </div>
            </div>
        </div>
    </div>
    <div class="col-xs-12">
        <div class="boxed">
            <div class="row d_flex">
                <div class="col-lg-12 col-md-12">
                    <h1>{$btr->okay_cms__fast_order__code|escape}: {literal}{fast_order_btn product=$product}{/literal}</h1>
                </div>
            </div>
        </div>
    </div>
</div>
