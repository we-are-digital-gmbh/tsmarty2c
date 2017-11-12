{if $direction == "down"}
    {assign var="icon" value="fa-arrow-down"}
    {assign var="title" value="{t}move field down{/t}"}
{else}
    {assign var="icon" value="fa-arrow-up"}
    {assign var="title" value="{t}move field up{/t}"}
{/if}

{strip}
    <a href="{$href}" title="{$title}">
        <i class="fa {$icon}" aria-hidden="true"></i>
    </a>
{/strip}