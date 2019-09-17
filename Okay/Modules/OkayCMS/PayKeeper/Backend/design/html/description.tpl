{$meta_title = $btr->okaycms__pay_keeper__title|escape scope=global}

{*Название страницы*}
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {$btr->okaycms__pay_keeper__title}
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
                    <p>{$btr->okaycms__pay_keeper__description_part_1}</p>

                    <p>
                        {$btr->okaycms__pay_keeper__description_part_2}: <br />
                        {$btr->okaycms__pay_keeper__description_part_3} <br />
                        {$btr->okaycms__pay_keeper__description_part_4}: {url_generator route='OkayCMS_PayKeeper_callback' absolute=1} <br />
                        {$btr->okaycms__pay_keeper__description_part_5}: {$rootUrl} <br />
                        {$btr->okaycms__pay_keeper__description_part_6}: {$rootUrl} <br />
                    </p>

                    <p>{$btr->okaycms__pay_keeper__description_part_7}</p>
                </div>
            </div>
        </div>
    </div>
</div>
