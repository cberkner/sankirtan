{if $bBoxenFilterNach}
    {if !empty($Suchergebnisse->Tags)}
        <section class="panel panel-default box box-filter-tag" id="sidebox{$oBox->kBox}">
            <div class="panel-heading">
                <h5 class="panel-title">{lang key="tagFilter" section="global"}</h5></div>
            <div class="box-body">
                <ul class="nav nav-list">
                 {foreach name=tagfilter from=$Suchergebnisse->Tags item=oTag}
                     {if isset($NaviFilter->TagFilter) && $NaviFilter->TagFilter[0]->kTag == $oTag->kTag}
                         <li>
                             <a rel="nofollow" href="{$NaviFilter->URL->cAlleTags}" class="active">
                                 <i class="fa fa-check-square-o text-muted"></i>
                                 <span class="value">
                                     {$oTag->cName}
                                     <span class="badge pull-right">{$oTag->nAnzahl}</span>
                                 </span>
                             </a>
                         </li>
                     {else}
                         <li>
                             <a rel="nofollow" href="{$oTag->cURL}" class="active">
                                 <span class="value">
                                     {$oTag->cName}
                                     <span class="badge pull-right">{$oTag->nAnzahl}</span>
                                 </span>
                             </a>
                         </li>
                     {/if}
                 {/foreach}
                </ul>
            </div>
        </section>
    {/if}
{/if}