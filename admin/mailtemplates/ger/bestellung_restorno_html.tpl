{includeMailTemplate template=header type=html}

Sehr {if $Kunde->cAnrede == "w"}geehrte{else}geehrter{/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},<br>
<br>
Ihre Bestellung bei {$Einstellungen.global.global_shopname} wurde soeben reaktivert.<br>
<strong>Bestellnummer:</strong> {$Bestellung->cBestellNr}<br>
<br>
Mit freundlichem Gru�,<br>
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=html}