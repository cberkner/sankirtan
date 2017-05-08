{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
<div class="panel-wrap">
    {if isset($position) && $position === 'popup'}
        {if isset($Artikelhinweise) && count($Artikelhinweise) > 0}
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                {foreach name=hinweise from=$Artikelhinweise item=Artikelhinweis}
                    {$Artikelhinweis}
                {/foreach}
            </div>
        {/if}
    {/if}
    <form action="{if !empty($Artikel->cURLFull)}{$Artikel->cURLFull}{else}index.php{/if}" method="post" id="article_availability">
        {$jtl_token}
        <fieldset>
            <legend>{lang key="contact" section="global"}</legend>
            {if $Einstellungen.$tplscope.benachrichtigung_abfragen_vorname !== 'N' || $Einstellungen.$tplscope.benachrichtigung_abfragen_nachname !== 'N'}
                <div class="row">
                    {if $Einstellungen.$tplscope.benachrichtigung_abfragen_vorname !== 'N'}
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group float-label-control{if !empty($fehlendeAngaben_benachrichtigung.vorname)} has-error{/if}{if $Einstellungen.$tplscope.benachrichtigung_abfragen_vorname === 'Y'} required{/if}">
                            <label for="firstName" class="control-label">
                                {lang key="firstName" section="account data"}
                            </label>
                            <input type="text" class="form-control" name="vorname" value="{if isset($Benachrichtigung->cVorname)}{$Benachrichtigung->cVorname}{/if}" id="firstName"{if $Einstellungen.$tplscope.benachrichtigung_abfragen_vorname === 'Y'} required{/if}>
                            {if !empty($fehlendeAngaben_benachrichtigung.vorname)}
                                <div class="alert alert-danger">
                                    {lang key="fillOut" section="global"}
                                </div>
                            {/if}
                        </div>
                    </div>
                    {/if}
    
                    {if $Einstellungen.$tplscope.benachrichtigung_abfragen_nachname !== 'N'}
                    <div class="col-xs-12 col-md-6">
                        <div class="form-group float-label-control{if !empty($fehlendeAngaben_benachrichtigung.nachname)} has-error{/if}{if $Einstellungen.$tplscope.benachrichtigung_abfragen_nachname === 'Y'} required{/if}">
                            <label for="lastName" class="control-label">
                                {lang key="lastName" section="account data"}
                            </label>
                            <input type="text" class="form-control" name="nachname" value="{if isset($Benachrichtigung->cNachname)}{$Benachrichtigung->cNachname}{/if}" id="lastName"{if $Einstellungen.$tplscope.benachrichtigung_abfragen_nachname === 'Y'} required{/if}>
                            {if !empty($fehlendeAngaben_benachrichtigung.nachname)}
                                <div class="alert alert-danger">
                                    {lang key="fillOut" section="global"}
                                </div>
                            {/if}
                        </div>
                    </div>
                    {/if}
                </div>
            {/if}
    
            <div class="row">
                <div class="col-xs-12 col-md-6">
                    <div class="form-group float-label-control{if !empty($fehlendeAngaben_benachrichtigung.email)} has-error{/if} required">
                        <label for="email" class="control-label">
                            {lang key="email" section="account data"}
                        </label>
                        <input type="email" class="form-control" name="email" value="{if isset($Lieferadresse->cMail)}{$Lieferadresse->cMail}{/if}" id="email" required>
                        {if !empty($fehlendeAngaben_benachrichtigung.email)}
                            <div class="alert alert-danger">
                                {if $fehlendeAngaben_benachrichtigung.email == 1}
                                    {lang key="fillOut" section="global"}
                                {elseif $fehlendeAngaben_benachrichtigung.email == 2}
                                    {lang key="invalidEmail" section="global"}
                                {elseif $fehlendeAngaben_benachrichtigung.email == 3}
                                    {lang key="blockedEmail" section="global"}
                                {/if}
                            </div>
                        {/if}
                    </div>
                </div>
            </div>
    
            {if isset($fehlendeAngaben_benachrichtigung)}
                {include file='snippets/checkbox.tpl' nAnzeigeOrt=CHECKBOX_ORT_FRAGE_VERFUEGBARKEIT cPlausi_arr=$fehlendeAngaben_benachrichtigung cPost_arr=null}
            {else}
                {include file='snippets/checkbox.tpl' nAnzeigeOrt=CHECKBOX_ORT_FRAGE_VERFUEGBARKEIT cPlausi_arr=null cPost_arr=null}
            {/if}
    
        </fieldset>
        {if (!isset($smarty.session.bAnti_spam_already_checked) || $smarty.session.bAnti_spam_already_checked !== true) &&
            isset($Einstellungen.global.anti_spam_method) && $Einstellungen.global.anti_spam_method !== 'N' &&
            isset($Einstellungen.$tplscope.benachrichtigung_abfragen_captcha) && $Einstellungen.$tplscope.benachrichtigung_abfragen_captcha !== 'N' && empty($smarty.session.Kunde->kKunde)}
            <hr>
            <div class="row">
                <div class="col-xs-12 col-md-12">
                    {if !empty($fehlendeAngaben_benachrichtigung.captcha)}
                        <div class="alert alert-danger" role="alert">{lang key="invalidToken" section="global"}</div>
                    {/if}
                    <div class="g-recaptcha" data-sitekey="{$Einstellungen.global.global_google_recaptcha_public}"></div>
                    <hr>
                </div>
            </div>
        {/if}
    
        <input type="hidden" name="a" value="{if $Artikel->kVariKindArtikel}{$Artikel->kVariKindArtikel}{else}{$Artikel->kArtikel}{/if}" />
        <input type="hidden" name="show" value="1" />
        <input type="hidden" name="benachrichtigung_verfuegbarkeit" value="1" />
        <button type="submit" value="{lang key="requestNotification" section="global"}" class="btn btn-primary" >{lang key="requestNotification" section="global"}</button>
    </form>
</div>
