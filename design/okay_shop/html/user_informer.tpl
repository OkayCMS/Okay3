{* User account *}
{if $user}
	<a class="account__link d-inline-flex align-items-center icon icon-perm-identity" href="{url_generator route="user"}">
        <span class="account__text" data-language="index_account">{$lang->index_account} </span>
		{$user->name|escape}
	</a>
{else}
	<a class="account__link d-inline-flex align-items-center icon icon-perm-identity" href="javascript:;" onclick="document.location.href = '{url_generator route="login"}'" title="{$lang->index_login}">
        <span class="account__text" data-language="index_account">{$lang->index_account} </span>
		<span class="account__login" data-language="index_login">{$lang->index_login}</span>
	</a>
{/if}