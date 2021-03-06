{if isset($oImageMap)}
    <div class="banner">
        {block name="banner-map"}
            <img alt="{$oImageMap->cTitel}" src="{$oImageMap->cBildPfad}" class="img-responsive" />
            {foreach from=$oImageMap->oArea_arr item=oImageMapArea}
                {strip}
                <a href="{$oImageMapArea->cUrl}" class="area {$oImageMapArea->cStyle}" style="left:{math equation="100/bWidth*posX" bWidth=$oImageMap->fWidth posX=$oImageMapArea->oCoords->x}%;top:{math equation="100/bHeight*posY" bHeight=$oImageMap->fHeight posY=$oImageMapArea->oCoords->y}%;width:{math equation="100/bWidth*aWidth" bWidth=$oImageMap->fWidth aWidth=$oImageMapArea->oCoords->w}%;height:{math equation="100/bHeight*aHeight" bHeight=$oImageMap->fHeight aHeight=$oImageMapArea->oCoords->h}%" title="{$oImageMapArea->cTitel|strip_tags|escape:"html"|escape:"quotes"}">
                    {if $oImageMapArea->oArtikel || $oImageMapArea->cBeschreibung|@strlen > 0}
                        {assign var="oArtikel" value=$oImageMapArea->oArtikel}
                        <div class="area-desc">
                            {if $oImageMapArea->oArtikel}
                                <img src="{$oArtikel->cVorschaubild}" alt="{$oArtikel->cName|strip_tags|escape:"quotes"|truncate:60}" class="img-responsive center-block" />
                            {/if}
                            {if $oImageMapArea->oArtikel}
                                {include file="productdetails/price.tpl" Artikel=$oArtikel tplscope="box"}
                            {/if}
                            {if $oImageMapArea->cBeschreibung|@strlen > 0}
                                <p>
                                    {$oImageMapArea->cBeschreibung}
                                </p>
                            {/if}
                        </div>
                    {/if}
                </a>
                {/strip}
            {/foreach}
        {/block}
        {if isset($isFluid) && $isFluid == false}
            <hr>
        {/if}
    </div>
    <script type="text/javascript">
        {block name="banner-js"}
        {literal}
        jtl.ready(function () {
            var bannerLink = $('.banner > a');
            bannerLink.popover({
                placement: 'auto bottom',
                html:      true,
                trigger:   'hover',
                container: 'body',
                content:   function () {
                    return $(this).children('.area-desc').html()
                }
            });

            bannerLink.mouseenter(function () {
                $(this).animate({
                    borderWidth: 10,
                    opacity:     0
                }, 900, function () {
                    $(this).css({opacity: 1, borderWidth: 0});
                });
            });

            $('.banner').mouseenter(function () {
                $(this).children('a').animate({
                    borderWidth: 10,
                    opacity:     0
                }, 900, function () {
                    $(this).css({opacity: 1, borderWidth: 0});
                });
            });
        });
        {/literal}
        {/block}
    </script>
{/if}
