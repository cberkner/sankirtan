<?php

require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Template.php';

function getDefaultTemplate()
{
    return Template::getInstance()->getFrontendTemplate();
}

function getDefaultTheme()
{
    $cTemplate = Shop::DB()->escape(getDefaultTemplate());

    $oDefaultTheme = $GLOBALS['DB']->executeQuery("SELECT cWert
                                                    FROM ttemplateeinstellungen
                                                    WHERE cName = 'theme_default'
                                                        AND cTemplate = '{$cTemplate}'", 1);

    return $oDefaultTheme->cWert;
}

function setDefaultTheme($cTheme)
{
	$cTheme = Shop::DB()->escape($cTheme);
    $cTemplate = Shop::DB()->escape(getDefaultTemplate());

    $oDefaultTheme = $GLOBALS['DB']->executeQuery("UPDATE ttemplateeinstellungen
													SET cWert = '{$cTheme}'
                                                    WHERE cName = 'theme_default'
                                                        AND cTemplate = '{$cTemplate}'", 1);

    return $oDefaultTheme->cWert;
}

function getThemes()
{
    $cTemplateOrdner = getDefaultTemplate();

    if (is_dir(PFAD_ROOT . PFAD_TEMPLATES . $cTemplateOrdner)) {
        $oEinstellungenXML_arr = Template::getInstance()->leseEinstellungenXML($cTemplateOrdner);
        $oTheme_arr            = array();

        if (is_array($oEinstellungenXML_arr) && count($oEinstellungenXML_arr) > 0) {
            foreach ($oEinstellungenXML_arr as $oSection) {
                if ($oSection->cName === "Theme") {
                    foreach ($oSection->oSettings_arr[0]->oOptions_arr as $oTheme) {
                        $cInfo_arr = explode(' - ', $oTheme->cName);
                        $oTheme_arr[] = (object) [
                            'cName' => $cInfo_arr[0],
                            'cValue' => $oTheme->cValue,
                            'cDesc' => isset($cInfo_arr[1]) ? $cInfo_arr[1] : ''
                        ];
                    }
                }
            }
        }

        return $oTheme_arr;
    }

    return [];
}
