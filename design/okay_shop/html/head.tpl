
    {* Full base address *}
    <base href="{$base}/">

    {$ok_head}
    
    {strip}
    <script>
        const ut_tracker = {
            start: function(name) {
                performance.mark(name + ':start');
            },
            end: function(name) {
                performance.mark(name + ':end');
                performance.measure(name, name + ':start', name + ':end');
                console.log(name + ' duration: ' + performance.getEntriesByName(name)[0].duration);
            }
        }
    </script>
    {/strip}
    
    {* Title *}
    <title>{$meta_title|escape}</title>

    {* Meta tags *}
    {if !empty($meta_keywords)}
        <meta name="keywords" content="{$meta_keywords|escape}"/>
    {/if}
    
    {if !empty($meta_description)}
        <meta name="description" content="{$meta_description|escape}"/>
    {/if}

    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    
    {if $controller == 'CategoryController' || $controller == 'BrandController' || $controller == 'ProductsController'}
        {if $set_canonical && !$need_indexing}
            <meta name="robots" content="noindex,nofollow">
        {elseif $sort}
            <meta name="robots" content="noindex,follow">
        {elseif isset($smarty.get.keyword)}
            <meta name="robots" content="noindex,follow">
        {else}
            <meta name="robots" content="index,follow">
        {/if}
    {elseif $controller == "RegisterController" || $controller == "LoginController" || $controller == "UserController" || $controller == "CartController"}
        <meta name="robots" content="noindex,follow">
    {elseif $controller == "OrderController"}
        <meta name="robots" content="noindex,nofollow">
    {elseif $controller == "CartController"}
        <meta name="robots" content="noindex,follow">
    {else}
        <meta name="robots" content="index,follow">
    {/if}

    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="generator" content="OkayCMS {$config->version}">

    {* rel prev next для блога *}
    {if $controller == "BlogController" && $total_pages_num > 1}
        {if $current_page_num == $total_pages_num}
            {if $current_page_num == 2}
                <link rel="prev" href="{url page=null}"/>
            {else}
                <link rel="prev" href="{url page=$current_page_num-1}"/>
            {/if}
        {elseif $current_page_num == 1}
            <link rel="next" href="{url page=2}"/>
        {else}
            {if $current_page_num == 2}
                <link rel="prev" href="{url page=null}"/>
            {else}
                <link rel="prev" href="{url page=$current_page_num-1}"/>
            {/if}
            <link rel="next" href="{url page=$current_page_num+1}"/>
        {/if}
    {/if}

    {* rel prev next для каталога товаров *}
    {$rel_prev_next}

    {* Product image/Post image for social networks *}
    {if $controller == 'ProductController'}
        <meta property="og:url" content="{$canonical}">
        <meta property="og:type" content="website">
        <meta property="og:title" content="{$product->name|escape}">
        <meta property="og:description" content='{$product->annotation|strip_tags|escape}'>
        <meta property="og:image" content="{$product->image->filename|resize:330:300}">
        <link rel="image_src" href="{$product->image->filename|resize:330:300}">
        {*twitter*}
        <meta name="twitter:card" content="product"/>
        <meta name="twitter:url" content="{$canonical}">
        <meta name="twitter:site" content="{$settings->site_name|escape}">
        <meta name="twitter:title" content="{$product->name|escape}">
        <meta name="twitter:description" content="{$product->annotation|strip_tags|escape}">
        <meta name="twitter:image" content="{$product->image->filename|resize:330:300}">
        <meta name="twitter:data1" content="{$lang->cart_head_price}">
        <meta name="twitter:label1" content="{$product->variant->price|convert:null:false} {$currency->code|escape}">
        <meta name="twitter:data2" content="{$lang->meta_organization}">
        <meta name="twitter:label2" content="{$settings->site_name|escape}">
    {elseif $controller == 'BlogController' && $post}
        <meta property="og:url" content="{$canonical}">
        <meta property="og:type" content="article">
        <meta property="og:title" content="{$post->name|escape}">
        {if $post->image}
            <meta property="og:image" content="{$post->image|resize:400:300:false:$config->resized_blog_dir}">
            <link rel="image_src" href="{$post->image|resize:400:300:false:$config->resized_blog_dir}">
        {else}
            <meta property="og:image" content="{$rootUrl}/{$config->design_images}{$settings->site_logo}">
            <meta name="twitter:image" content="{$rootUrl}/{$config->design_images}{$settings->site_logo}">
        {/if}
        <meta property="og:description" content='{$post->annotation|strip_tags|escape}'>
        {*twitter*}
        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="{$post->name|escape}">
        <meta name="twitter:description" content="{$post->annotation|strip_tags|escape}">
        <meta name="twitter:image" content="{$post->image|resize:400:300:false:$config->resized_blog_dir}">
    {else}
        <meta property="og:title" content="{$settings->site_name|escape}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{$rootUrl}">
        <meta property="og:image" content="{$rootUrl}/{$config->design_images}{$settings->site_logo}">
        <meta property="og:site_name" content="{$settings->site_name|escape}">
        <meta property="og:description" content="{$meta_description|escape}">
        <link rel="image_src" href="{$rootUrl}/{$config->design_images}{$settings->site_logo}">
        {*twitter*}
        <meta name="twitter:card" content="summary">
        <meta name="twitter:title" content="{$settings->site_name|escape}">
        <meta name="twitter:description" content="{$meta_description|escape}">
        <meta name="twitter:image" content="{$rootUrl}/{$config->design_images}{$settings->site_logo}">
    {/if}

    {* The canonical address of the page *}
    {if isset($canonical)}
        <link rel="canonical" href="{$canonical}">
    {elseif $sort}
        <link rel="canonical" href="{$sort_canonical}">
    {/if}

    {* Language attribute *}
    {foreach $languages as $l}
        {if $l->enabled}
            <link rel="alternate" hreflang="{$l->href_lang}" href="{$l->url}">
        {/if}
    {/foreach}

    {if $settings->captcha_type == "v3"}
        <script>ut_tracker.start('render:recaptcha');</script>
        <script src="https://www.google.com/recaptcha/api.js?render={$settings->public_recaptcha_v3}"></script>
        <script>
            grecaptcha.ready(function () {
                {if $controller == 'ProductController' || $controller == 'BlogController'}
                    var recaptcha_action = 'product';
                {elseif $controller == 'CartController'}
                    var recaptcha_action = 'cart';
                {else}
                    var recaptcha_action = 'other';
                {/if}

                var all_captchеs = document.getElementsByClassName('fn_recaptchav3');
                grecaptcha.execute('{$settings->public_recaptcha_v3}', { action: recaptcha_action })
                    .then(function (token) {
                        for (capture of all_captchеs) {
                            capture.value = token;
                        }
                    });
            });
        </script>
        <script>ut_tracker.end('render:recaptcha');</script>
    {elseif $settings->captcha_type == "v2"}
        <script>ut_tracker.start('render:recaptcha');</script>
        <script type="text/javascript">
            var onloadCallback = function() {
                mysitekey = "{$settings->public_recaptcha}";
                if($('#recaptcha1').length>0){
                    grecaptcha.render('recaptcha1', {
                        'sitekey' : mysitekey
                    });
                }
                if($('#recaptcha2').length>0){
                    grecaptcha.render('recaptcha2', {
                        'sitekey' : mysitekey
                    });
                }
            };
        </script>
        <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
        <script>ut_tracker.end('render:recaptcha');</script>
    {elseif $settings->captcha_type == "invisible"}
        <script>ut_tracker.start('render:recaptcha');</script>
        <script>
            function onSubmit(token) {
                document.getElementById("captcha_id").submit();
            }
            function onSubmitCallback(token) {
                document.getElementById("fn_callback").submit();
            }
            function onSubmitBlog(token) {
                document.getElementById("fn_blog_comment").submit();
            }
        </script>
        <script src='https://www.google.com/recaptcha/api.js'></script>
        <script>ut_tracker.end('render:recaptcha');</script>
    {/if}

    <link rel="search" type="application/opensearchdescription+xml" title="{$rootUrl} Search" href="{url_generator route="opensearch" absolute=1}" />

    {* Favicon *}
    <link href="{$rootUrl}/{$config->design_images}{$settings->site_favicon}?v={$settings->site_favicon_version}" type="image/x-icon" rel="icon">
    <link href="{$rootUrl}/{$config->design_images}{$settings->site_favicon}?v={$settings->site_favicon_version}" type="image/x-icon" rel="shortcut icon">

    {*<link href="https://fonts.googleapis.com/css?family=Montserrat:400,500,600,700&display=swap&subset=cyrillic" rel="stylesheet">
    <link href="https://cdn.materialdesignicons.com/3.8.95/css/materialdesignicons.min.css" rel="stylesheet">*}

    {* JQuery *}
    <script>ut_tracker.start('parsing:page');</script>

    <script>ut_tracker.start('parsing:head:scripts');</script>
    {if $controller == "Products"}
    {js file='jquery-migrate-3.0.1.min.js' defer=true}
    {/if}
    <script>ut_tracker.end('parsing:head:scripts');</script>

    {if !empty($counters['head'])}
    <script>ut_tracker.start('parsing:head:counters');</script>
    {foreach $counters['head'] as $counter}
    {$counter->code}
    {/foreach}
    <script>ut_tracker.end('parsing:head:counters');</script>
    {/if}