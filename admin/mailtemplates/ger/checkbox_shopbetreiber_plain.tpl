Sehr geehrter Shopbetreiber,

der Kunde {$oKunde->cVorname} {$oKunde->cNachname} hat im Bereich {$cAnzeigeOrt} folgende Checkboxoption gew�hlt:

{assign var=kSprache value=$oSprache->kSprache}
- {$oCheckBox->cName}, {$oCheckBox->oCheckBoxSprache_arr[$kSprache]->cText}
