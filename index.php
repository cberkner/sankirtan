<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */
require dirname(__FILE__) . '/includes/globalinclude.php';
require PFAD_ROOT . PFAD_INCLUDES . 'smartyInclude.php';
/** @global JTLSmarty $smarty */
Shop::run();
$cParameter_arr = Shop::getParameters();
$NaviFilter     = Shop::buildNaviFilter($cParameter_arr);
Shop::checkNaviFilter($NaviFilter);
$https          = false;
$linkHelper     = LinkHelper::getInstance();
if (isset(Shop::$kLink) && (int)Shop::$kLink > 0) {
    $link = $linkHelper->getPageLink(Shop::$kLink);
    if (isset($link->bSSL) && $link->bSSL > 0) {
        $https = true;
        if ((int)$link->bSSL === 2) {
            pruefeHttps();
        }
    }
}
if ($https === false) {
    loeseHttps();
}
executeHook(HOOK_INDEX_NAVI_HEAD_POSTGET);
//prg
if (isset($_SESSION['bWarenkorbHinzugefuegt']) &&
    isset($_SESSION['bWarenkorbAnzahl']) &&
    isset($_SESSION['hinweis'])
) {
    $smarty->assign('bWarenkorbHinzugefuegt', $_SESSION['bWarenkorbHinzugefuegt'])
           ->assign('bWarenkorbAnzahl', $_SESSION['bWarenkorbAnzahl'])
           ->assign('hinweis', $_SESSION['hinweis']);
    unset($_SESSION['hinweis']);
    unset($_SESSION['bWarenkorbAnzahl']);
    unset($_SESSION['bWarenkorbHinzugefuegt']);
}
//wurde ein artikel in den Warenkorb gelegt?
checkeWarenkorbEingang();
if (!$cParameter_arr['kWunschliste'] &&
    strlen(verifyGPDataString('wlid')) > 0 &&
    verifyGPDataString('error') === ''
) {
    header(
        'Location: ' . $linkHelper->getStaticRoute('wunschliste.php', true) .
            '?wlid=' . verifyGPDataString('wlid') . '&error=1',
        true,
        303
    );
    exit();
}
//support for artikel_after_cart_add
if ($smarty->getTemplateVars('bWarenkorbHinzugefuegt')) {
    require_once PFAD_ROOT . PFAD_INCLUDES . 'artikel_inc.php';
    if (isset($_POST['a']) && function_exists('gibArtikelXSelling')) {
        $smarty->assign('Xselling', gibArtikelXSelling($_POST['a']));
    }
}
if (($cParameter_arr['kArtikel'] > 0 || $cParameter_arr['kKategorie'] > 0) &&
    !$_SESSION['Kundengruppe']->darfArtikelKategorienSehen
) {
    //falls Artikel/Kategorien nicht gesehen werden duerfen -> login
    header('Location: ' . $linkHelper->getStaticRoute('jtl.php', true) . '?li=1', true, 303);
    exit;
}
if ($cParameter_arr['kKategorie'] > 0 &&
    !Kategorie::isVisible($cParameter_arr['kKategorie'], $_SESSION['Kundengruppe']->kKundengruppe)
) {
    $cParameter_arr['kKategorie'] = 0;
    $oLink                        = Shop::DB()->select('tlink', 'nLinkart', LINKTYP_404);
    $kLink                        = $oLink->kLink;
    Shop::$kLink                  = $kLink;
}
Shop::getEntryPoint();
if (Shop::$is404 === true) {
    $cParameter_arr['is404'] = true;
    Shop::$fileName = null;
}
$smarty->assign('NaviFilter', $NaviFilter);
if (Shop::$fileName !== null) {
    require PFAD_ROOT . Shop::$fileName;
}
if ($cParameter_arr['is404'] === true) {
    if (!isset($seo)) {
        $seo = null;
    }
    executeHook(HOOK_INDEX_SEO_404, ['seo' => $seo]);
    if (!Shop::$kLink) {
        $hookInfos     = urlNotFoundRedirect([
            'key'   => 'kLink',
            'value' => $cParameter_arr['kLink']
        ]);
        $kLink         = $hookInfos['value'];
        $bFileNotFound = $hookInfos['isFileNotFound'];
        if (!$kLink) {
            $kLink       = $linkHelper->getSpecialPageLinkKey(LINKTYP_404);
            Shop::$kLink = $kLink;
        }
    }
    require_once PFAD_ROOT . 'seite.php';
} elseif (Shop::$fileName === null && Shop::getPageType() !== null) {
    require_once PFAD_ROOT . 'seite.php';
}
