<ul class="{if isset($class)}{$class}{else}nav nav-list{/if}">
    {foreach name=bewertungen from=$Suchergebnisse->Bewertung item=oBewertung}
        {if isset($NaviFilter->BewertungFilter) && $NaviFilter->BewertungFilter->nSterne == $oBewertung->nStern}
            <li>
                <a rel="nofollow" href="{$NaviFilter->URL->cAlleBewertungen}" class="active">
                    <i class="fa fa-check-square-o text-muted"></i>
                    <span class="value">
                        {include file="productdetails/rating.tpl" stars=$oBewertung->nStern}
                        {if $NaviFilter->BewertungFilter->nSterne < 5}
                            <em>({lang key="from" section="productDetails"} {$oBewertung->nStern}
                                {if $oBewertung->nStern > 1}
                                    {lang key="starPlural"}
                                {else}
                                    {lang key="starSingular"}
                                {/if})
                            </em>
                        {/if}
                        <span class="badge pull-right">{$oBewertung->nAnzahl}</span>
                    </span>
                </a>
            </li>
        {else}
            {if $oBewertung->nAnzahl >= 1 && $oBewertung->nStern > 0}
                <li>
                    <a rel="nofollow" href="{$oBewertung->cURL}">
                        <i class="fa fa-square-o text-muted"></i>
                        <span class="value">
                            {include file="productdetails/rating.tpl" stars=$oBewertung->nStern}
                            {if $oBewertung->nStern < 5}
                                <em>
                                    ({lang key="from" section="productDetails"} {$oBewertung->nStern}
                                    {if $oBewertung->nStern > 1}
                                        {lang key="starPlural"}
                                    {else}
                                        {lang key="starSingular"}
                                    {/if})
                                </em>
                            {/if}
                            <span class="badge pull-right">{$oBewertung->nAnzahl}</span>
                        </span>
                    </a>
                </li>
            {/if}
        {/if}
    {/foreach}
</ul>
