{includeMailTemplate template=header type=plain}

Hallo {$Kunde->cVorname},

anbei bekommst du ein Guthaben von {$Neukunde->fGuthaben} f�r {$Firma->cName}.

�brigens, ich werbe Dich im Rahmen der {$Firma->cName} Kunden werben Kunden Aktion.

Viele Gr��e,
{$Bestandskunde->cVorname} {$Bestandskunde->cNachname}

{includeMailTemplate template=footer type=plain}
