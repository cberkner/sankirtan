{includeMailTemplate template=header type=plain}

Sehr {if $Kunde->cAnrede == "w"}geehrte{else}geehrter{/if} {$Kunde->cAnredeLocalized} {$Kunde->cNachname},

vielen Dank f�r Ihre Bewertung eines Artikels. Ihr Guthaben Bonus in H�he von {$oBewertungGuthabenBonus->fGuthabenBonusLocalized} steht Ihnen ab sofort zur Verf�gung.
Sie k�nnen Ihr Guthaben jederzeit bei einem Ihrer n�chsten Eink�ufe einl�sen.

Mit freundlichem Gru�,
Ihr Team von {$Firma->cName}

{includeMailTemplate template=footer type=plain}