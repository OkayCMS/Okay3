{* Languages *}
{if $languages|count > 1}
	{$cnt = 0}
	{foreach $languages as $ln}
		{if $ln->enabled}
			{$cnt = $cnt+1}
		{/if}
	{/foreach}
	{if $cnt>1}
		<div class="switcher__item switcher__language">
			<div class="switcher__visible">
				{if is_file("{$config->lang_images_dir}{$language->label}.png")}
					<img alt="{$language->current_name}" src="{("{$language->label}.png")|resize:20:20:false:$config->lang_resized_dir}"/>
				{/if}
				<span class="switcher__name hidden-xs-up">{$language->name}</span>
				<span class="switcher__name">{$language->label}</span>
			</div>
			<div class="switcher__hidden">
				{foreach $languages as $l}
					{if $l->enabled}
						<a class="switcher__link {if $language->id == $l->id} active{/if}"
						   href="{preg_replace('/^(.+)\/$/', '$1', $l->url)}">
							{if is_file("{$config->lang_images_dir}{$l->label}.png")}
								<img alt="{$l->current_name}" src="{("{$l->label}.png")|resize:20:20:false:$config->lang_resized_dir}" />
							{/if}
							<span class="switcher__name">{$l->name}</span>
							<span class="switcher__name hidden-xl-up">{$l->label}</span>
						</a>
					{/if}
				{/foreach}
			</div>
		</div>
	{/if}
{/if}

{* Currencies *}
{if $currencies|count > 1}
	<div class="switcher__item switcher__currencies">
		<div class="switcher__visible">
			<span class="switcher__name hidden-lg-down">{$currency->name}</span>
			<span class="switcher__name hidden-xl-up">{$currency->sign}</span>
		</div>
		<div class="switcher__hidden">
			{foreach $currencies as $c}
				{if $c->enabled}
					<form method="POST">
						{*<a class="switcher__link {if $currency->id== $c->id} active{/if}" href="#" onClick="change_currency({$c->id}); return false;">
							<span class="switcher__name hidden-lg-down">{$c->name}</span>
							<span class="switcher__name hidden-xl-up">{$c->sign}</span>
						</a>*}

						<button type="submit" name="prg_seo_hide" class="switcher__link {if $currency->id== $c->id} active{/if}" value="{url currency_id=$c->id}">
							<span class="switcher__name hidden-lg-down">{$c->name}</span>
							<span class="switcher__name hidden-xl-up">{$c->sign}</span>
						</button>
					</form>
				{/if}
			{/foreach}
		</div>
	</div>
{/if}