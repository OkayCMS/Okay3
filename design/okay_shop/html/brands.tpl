{* The brand page template *}

{* The canonical address of the page *}
{$canonical="{url_generator route='brands' absolute=1}" scope=global}
<div class="block">
	{* The page heading *}
	<div class="block__header block__header--boxed block__header--border">
		<h1 class="block__heading"><span data-page="{$page->id}">{if $page->name_h1|escape}{$page->name_h1|escape}{else}{$page->name|escape}{/if}</span></h1>
	</div>
	{if $brands || $page->description}
		<div class="block__body block--boxed block--border">
			{* The list of the brands *}
			{if $brands}
				<div class="brand row">
					{foreach $brands as $b}
						<div class="brand__item col-xs-6 col-sm-4 col-lg-2">
							<div class="brand__preview">
								<a class="brand__link" data-brand="{$b->id}" href="{url_generator route='brand' url=$b->url}">
									{if $b->image}
										<div class="brand__image">
											<img class="brand_img lazy" data-src="{$b->image|resize:120:100:false:$config->resized_brands_dir}" src="{$b->image|resize:120:100:false:$config->resized_brands_dir}" alt="{$b->name|escape}" title="{$b->name|escape}">
										</div>
									{else}
										<div class="brand__name">
											<span>{$b->name|escape}</span>
										</div>
									{/if}
								</a>
							</div>
						</div>
					{/foreach}
				</div>
			{/if}

			{* The page body *}
			{if $page->description}
			<div class="">
				<div class="fn_reedmore">
					<div class="page-description__text boxed__description">{$page->description}</div>
				</div>
			</div>
			{/if}
		</div>
	{/if}
</div>