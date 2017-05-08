{**
 * @copyright (c) 2006-2015 JTL-Software-GmbH, all rights reserved
 * @author JTL-Software-GmbH (www.jtl-software.de)
 *
 * use is subject to license terms
 * http://jtl-software.de/jtlshop3license.html
 *}

{if !empty($hinweis)}
    <div class="alert alert-info">{$hinweis}</div>{/if}
{if !empty($fehlendeAngaben) && !$hinweis}
    <div class="alert alert-danger">{lang key="yourDataDesc" section="account data"}</div>
{/if}
{if isset($fehlendeAngaben.email_vorhanden) && $fehlendeAngaben.email_vorhanden == 1}
    <div class="alert alert-danger">{lang key="emailAlreadyExists" section="account data"}</div>
{/if}
{if isset($fehlendeAngaben.formular_zeit) && $fehlendeAngaben.formular_zeit == 1}
    <div class="alert alert-danger">{lang key="formToFast" section="account data"}</div>
{/if}

{if !isset($checkout)}
    {include file='register/inc_vcard_upload.tpl' id='registrieren.php'}
{/if}
    
<form method="post" action="{get_static_route id='registrieren.php'}">
    {$jtl_token}
    {include file='checkout/inc_billing_address_form.tpl'}
    {if !$editRechnungsadresse}
        <hr>
        <div class="row">
            <div class="col-xs-6">
                <div class="form-group float-label-control{if isset($fehlendeAngaben.pass_zu_kurz) || isset($fehlendeAngaben.pass_ungleich)} has-error{/if} required">
                    <label for="password" class="control-label">{lang key="password" section="account data"}</label>
                    <input type="password" name="pass" maxlength="20" id="password" class="form-control" placeholder="{lang key="password" section="account data"}" required>
                    {if isset($fehlendeAngaben.pass_zu_kurz)}
                        <div class="alert alert-danger">{$warning_passwortlaenge}</div>
                    {/if}
                </div>
            </div>
            <div class="col-xs-6">
                <div class="form-group float-label-control{if isset($fehlendeAngaben.pass_ungleich)} has-error{/if} required">
                    <label for="password2" class="control-label">{lang key="passwordRepeat" section="account data"}</label>
                    <input type="password" name="pass2" maxlength="20" id="password2" class="form-control" placeholder="{lang key="passwordRepeat" section="account data"}" required>
                    {if isset($fehlendeAngaben.pass_ungleich)}
                        <div class="alert alert-danger">{lang key="passwordsMustBeEqual" section="account data"}</div>
                    {/if}
                </div>
            </div>
        </div>
    {/if}

    <hr>
    <input type="hidden" name="checkout" value="{if isset($checkout)}{$checkout}{/if}">
    <input type="hidden" name="form" value="1">
    <input type="hidden" name="editRechnungsadresse" value="{$editRechnungsadresse}">
    <input type="submit" class="btn btn-primary btn-lg pull-right submit submit_once" value="{lang key="sendCustomerData" section="account data"}">
</form>