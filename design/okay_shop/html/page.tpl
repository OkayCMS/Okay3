{* Page template *}

{* The canonical address of the page *}
{$canonical="{url_generator route="page" url=$page->url absolute=1}" scope=global}

{if $page->url == '404'}
    {include file='page_404.tpl'}
{else}
	<div class="block">
		{* The page heading *}
		<div class="block__header block__header--boxed block__header--border">
			<h1 class="block__heading">
				<span data-page="{$page->id}">{if $page->name_h1|escape}{$page->name_h1|escape}{else}{$page->name|escape}{/if}</span>
			</h1>
		</div>

		{* The page content *}
		<div class="block block--boxed block--border">
			{$page->description}
		</div>
    </div>
{/if}
