{includeMailTemplate template=header type=plain}

Sehr {if $Kunde->cAnrede == "w"}geehrte{else}geehrter{/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

Ihre Bestellung bei {$Einstellungen.global.global_shopname} wurde soeben reaktivert.
Bestellnummer: {$Bestellung->cBestellNr}

Mit freundlichem Gru�,
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}