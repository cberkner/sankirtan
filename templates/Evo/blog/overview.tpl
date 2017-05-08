<h1>{lang key="news" section="news"}</h1>

{if !empty($hinweis)}
    <div class="alert alert-info">{$hinweis}</div>
{/if}
{if !empty($fehler)}
    <div class="alert alert-danger">{$fehler}</div>
{/if}

{include file="snippets/extension.tpl"}

<div class="well well-sm">
    <form id="frm_filter" name="frm_filter" action="{get_static_route id='news.php'}" method="post" class="form-inline text-center">
        {$jtl_token}

        <select name="nSort" onchange="this.form.submit();" class="form-control form-group">
            <option value="-1"{if $nSort == -1} selected{/if}>{lang key="newsSort" section="news"}</option>
            <option value="1"{if $nSort == 1} selected{/if}>{lang key="newsSortDateDESC" section="news"}</option>
            <option value="2"{if $nSort == 2} selected{/if}>{lang key="newsSortDateASC" section="news"}</option>
            <option value="3"{if $nSort == 3} selected{/if}>{lang key="newsSortHeadlineASC" section="news"}</option>
            <option value="4"{if $nSort == 4} selected{/if}>{lang key="newsSortHeadlineDESC" section="news"}</option>
            <option value="5"{if $nSort == 5} selected{/if}>{lang key="newsSortCommentsDESC" section="news"}</option>
            <option value="6"{if $nSort == 6} selected{/if}>{lang key="newsSortCommentsASC" section="news"}</option>
        </select>

        <select name="cDatum" onchange="this.form.submit();" class="form-control form-group">
            <option value="-1"{if $cDatum == -1} selected{/if}>{lang key="newsDateFilter" section="news"}</option>
            {if !empty($oDatum_arr)}
                {foreach name="datum" from=$oDatum_arr item=oDatum}
                    <option value="{$oDatum->cWert}"{if $cDatum == $oDatum->cWert} selected{/if}>{$oDatum->cName}</option>
                {/foreach}
            {/if}
        </select>

        {lang key="newsCategorie" section="news" assign="cCurrentKategorie"}
        <select name="nNewsKat" onchange="this.form.submit();" class="form-control form-group">
            <option value="-1"{if $nNewsKat == -1} selected{/if}>{lang key="newsCategorie" section="news"}</option>
            {if !empty($oNewsKategorie_arr)}
                {foreach name="newskats" from=$oNewsKategorie_arr item=oNewsKategorie}
                    {if $nNewsKat == $oNewsKategorie->kNewsKategorie}{assign var="cCurrentKategorie" value=$oNewsKategorie->cName}{/if}
                    <option value="{$oNewsKategorie->kNewsKategorie}"{if $nNewsKat == $oNewsKategorie->kNewsKategorie} selected{/if}>{$oNewsKategorie->cName}</option>
                {/foreach}
            {/if}
        </select>

        <select class="form-control form-group" name="{$oPagination->getId()}_nItemsPerPage" id="{$oPagination->getId()}_nItemsPerPage"
                onchange="this.form.submit();">
            <option value="0" {if $oPagination->getItemsPerPage() == 0} selected{/if}>
                {lang key='newsPerSite' section='news'}
            </option>
            {foreach $oPagination->getItemsPerPageOptions() as $nItemsPerPageOption}
                <option value="{$nItemsPerPageOption}"{if $oPagination->getItemsPerPage() == $nItemsPerPageOption} selected{/if}>
                    {$nItemsPerPageOption}
                </option>
            {/foreach}
        </select>

        <button name="submitGo" type="submit" value="{lang key="filterGo" section="global"}" class="btn btn-default">{lang key="filterGo" section="global"}</button>
    </form>
</div>

{if isset($noarchiv) && $noarchiv}
    <div class="alert alert-info">{lang key="noNewsArchiv" section="news"}.</div>
{elseif !empty($oNewsUebersicht_arr)}
    <div id="newsContent">
        {if !empty($cCurrentKategorie)}
            <h2>{$cCurrentKategorie}</h2>
            <hr>
            {include file='snippets/pagination.tpl' oPagination=$oPagination cThisUrl='news.php' parts=['label']}
        {/if}
        {foreach name=uebersicht from=$oNewsUebersicht_arr item=oNewsUebersicht}
            {include file="blog/preview.tpl"}
        {/foreach}
    </div>
    {include file='snippets/pagination.tpl' oPagination=$oPagination cThisUrl='news.php' parts=['pagi']}
{/if}