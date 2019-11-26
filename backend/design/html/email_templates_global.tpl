{* Title *}
{$meta_title = $btr->email_templates_debug scope=global}

<div class="row">
    <div class="col-lg-10 col-md-10">
        <div class="wrap_heading">
            <div class="box_heading heading_page">
                {$btr->email_templates_debug|escape}
            </div>
        </div>
    </div>
    <div class="col-md-2 col-lg-2 col-sm-12 float-xs-right"></div>
</div>
<div class="row">
    <div class="col-lg-12 col-md-12 col-sm-12">
        <div class="boxed boxed_attention">
            <div class="">
                {$btr->general_design_message3|escape}
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-lg-12 col-md-12">
        <div class="boxed match fn_toggle_wrap tabs">
           <div class="design_tabs">
                <div class="design_navigation">
                    <span class="design_navigation_link focus">{$btr->general_templates_email|escape}</span>
                </div>
                <div class="design_container">
                    <a class="design_tab focus" href="{url debug=emailOrderAdmin order_id=1}">emailOrderAdmin()</a>
                    <a class="design_tab focus" href="{url debug=emailCommentAdmin comment_id=1}">emailCommentAdmin()</a>
                    <a class="design_tab focus" href="{url debug=emailCallbackAdmin callback_id=1}">emailCallbackAdmin()</a>
                    <a class="design_tab focus" href="{url debug=emailFeedbackAdmin feedback_id=1}">emailFeedbackAdmin()</a>
                    <a class="design_tab focus" href="{url debug=emailOrderUser order_id=1}">emailOrderUser()</a>
                </div>
            </div>
        </div>
    </div>
</div>
