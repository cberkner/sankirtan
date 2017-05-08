{$bcf = $bestFinancingOption->getCreditFinancing()}
{$bmp = $bestFinancingOption->getMonthlyPayment()}
{$btc = $bestFinancingOption->getTotalCost()}
{$bti = $bestFinancingOption->getTotalInterest()}

{if $bcf->getApr() > 0}
	<div class="ppf-container">
		<p class="rate-info">
			Finanzierung ab <span class="price">{gibPreisStringLocalized($bmp->getValue())}</span> in {$bcf->getTerm()} monatlichen Raten
			mit Ratenzahlung Powered by PayPal.
		</p>

		<p class="legal-info">Repr&auml;sentatives Beispiel gem. � 6a PAngV</p>

		<table class="table table-condensed table-financing-option">
			<tbody>
				<tr>
					<td>Nettodarlehensbetrag</td>
					<td class="value">{gibPreisStringLocalized($transactionAmount->getValue())}</td>
				</tr>
				<tr>
					<td>fester Sollzinssatz</td>
					<td class="value">{$bcf->getNominalRate()|string_format:"%.2f"} %</td>
				</tr>
				<tr>
					<td>effektiver Jahreszins</td>
					<td class="value">{$bcf->getApr()|string_format:"%.2f"} %</td>
				</tr>
				<tr>
					<td>zu zahlender Gesamtbetrag</td>
					<td class="value">{gibPreisStringLocalized($btc->getValue())}</td>
				</tr>
			</tbody>
			<tfoot class="total">
				<tr>
					<th>{$bcf->getTerm()} monatliche Raten in H&ouml;he von je</th>
					<th class="value">{gibPreisStringLocalized($bmp->getValue())}</th>
				</tr>
			</tfoot>
		</table>
		
		<a href="#ppf-modal" data-toggle="modal" data-target="#ppf-modal" class="show-details">
			<i class="fa fa-exclamation-circle" aria-hidden="true"></i> Informationen zu m&ouml;glichen Raten
		</a>
	</div>
{else}
	<div class="ppf-container">
		<p class="rate-info text-center">Finanzierung ab <span class="price">{gibPreisStringLocalized($bmp->getValue())}</span> im Monat.</p>
		
		<a href="#ppf-modal" data-toggle="modal" data-target="#ppf-modal" class="show-details">
			<i class="fa fa-exclamation-circle" aria-hidden="true"></i> Informationen zu m&ouml;glichen Raten
		</a>
	</div>
{/if}

<div class="modal fade" id="ppf-modal" tabindex="-1" role="dialog" aria-labelledby="ppf-modal-label">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">
                    <i class="fa fa-times" aria-hidden="true"></i>
                </button>
                <h4 class="modal-title text-center" id="ppf-modal-label">
                    <img src="{$plugin->cFrontendPfadURLSSL}/images/de-ppcc-logo-800px.png" class="ppf-image">
                </h4>
            </div>
            <div class="modal-body">
                {include file="{$plugin->cFrontendPfad}template/presentment.tpl"}
            </div>
            <div class="modal-footer">
                {include file="{$plugin->cFrontendPfad}template/presentment-legal.tpl"}
            </div>
        </div>
    </div>
</div>