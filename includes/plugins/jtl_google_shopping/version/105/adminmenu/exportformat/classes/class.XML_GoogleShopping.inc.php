<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

require_once PFAD_ROOT . PFAD_CLASSES . 'class.JTL-Shop.Artikel.php';

/**
 * Exportiert Artikel in Form einer XML-Datei f�r Google Base
 *
 * @author Andre Vermeulen
 */
class XML_GoogleShopping
{
    /**
     * Beinhaltet relevante Daten des Exportformates
     */
    private $exportformat;

    /**
     * Resource der zu schreibenen Datei
     *
     * @var Resource
     */
    private $f;

    /**
     * Head der zu schreibenden XML-Datei
     */
    private $cHead = "<?xml version=\"1.0\"?>\r<rss xmlns:g=\"http://base.google.com/ns/1.0\" version=\"2.0\">\r\t<channel>\r\t\t<title><![CDATA[###cShop###]]></title>\r\t\t<link><![CDATA[###cShopUrl###]]></link>\r\t\t<description><![CDATA[###cShopBeschreibung###]]></description>\r";

    /**
     * Foot der zu schreibenden XML-Dateo
     */
    private $cFoot = "\t</channel>\r</rss>";

    /**
     * Einstellungen des Plugins (z.B. Name des Shops usw)
     */
    private $cEinstellung_Arr;

    /**
     * Stellt sicher das der Head initialisiert wurde bevor der Head geschrieben wird
     */
    private $bInitHead = false;

    /**
     * Stellt sicher das der Head nicht doppelt geschrieben wird
     */
    private $bHeadWritten = false;

    /**
     * Array mit kArtikel (Artikel ID) der zu exportierenden Artikel
     */
    private $nExportArticleIds_arr = null;

    /**
     * Array mit Artikelobjekten
     */
    private $oExportArticle_arr = array();

    /**
     * Array mit Fehlern
     */
    private $cError_arr = array();

    /**
     * Array mit Pflicht-Attributen
     * Array-Key = Name des Google Attributs (z.B. g:id)
     * Value[0] = Hat Kind-Attribut 0 = Nein, >= 1= Ja
     * Value[1] = Attribut-Name im Artikelobjekt
     *
     * private $requiredAttributes_arr = array('title' => array(0 ,'cName'),
     * 'link' => array(0, 'cDeeplink'),
     * 'description' => array(0, 'cBeschreibung'),
     * 'g:id' => array(0, 'cArtNr'),
     * 'g:condition' => array(0, 'cZustand'),
     * 'g:price' => array(0,'fVKBrutto'),
     * 'g:availability' => array(0, 'cVerfuegbarkeit'),
     * 'g:image_link' => array(0, 'Artikelbild'),
     * 'g:shipping' => array(1, 'versand'),
     * 'g:product_type' => array(0, 'cCategorie_arr'),
     * 'g:google_product_category' => array(0, 'cGoogleCategorie')); */
    /**
     * Array mit optionalen Attributen
     * Array-Key = Name des Google Attributs (z.B. g:country)
     * Value[0] = Ist Funktionsattribut? 0 = Nein, 1 = Ja
     * Value[1] = Attribut-Name im Artikelobjekt oder im Funktionsattribut-Array
     *
     * private $optionalAttributes_arr = array('g:item_group_id' => array(0, 'cVaterArtNr'),
     * 'g:mpn' => array(0, 'cHAN'),
     * 'g:brand' => array(0, 'cHersteller'),
     * 'g:shipping_weight' => array(0, 'cGewicht'),
     * 'g:gtin' => array(0, 'cGtin'),
     * 'g:additional_image_link' => array(0, 'cArtikelbild_arr'),
     * 'g:color' => array(0, 'cFarbe'),
     * 'g:material' => array(0, 'cMaterial'),
     * 'g:pattern' => array(0, 'cMuster'),
     * 'g:size' => array(0, 'cGroesse'),
     * 'g:gender' => array(0, 'cGeschlecht'),
     * 'g:age_group' => array(0, 'cAltersgruppe')); */

    /**
     * Array mit Fehlern
     */
    private $oAttr_arr = array();

    /**
     * Array mit Pflicht-Kind-Attributen
     * Array-Key = Name des Google Attributs (z.B. g:country)
     * Value[0] = Hat Kind-Attribut 0 = Nein, >=1 = Ja
     * Value[1] = Attribut-Name im Artikelobjekt
     * private $requiredChildAttributes_arr = array (
     * 1 => array('g:country' => array(1, 'cLieferland'), 'g:service' => array(1, 'cVersandklasse'), 'g:price' => array(1, 'Versandkosten')));
     */

    /**
     * Konstruktor
     * setzt $exportformat, $f und $cEinstellung_arr
     *
     * @param object $exportformat
     * @param mixed  $f
     * @param array  $cEinstellung_Arr
     * @throws Exception
     */
    public function __construct($exportformat, $f, $cEinstellung_Arr)
    {
        if (isset($exportformat) && is_object($exportformat)) {
            $this->exportformat = $exportformat;
        } else {
            throw new Exception('Fehler beim Laden des Exportformates');
        }

        if (isset($f)) {
            $this->f = $f;
        } else {
            throw new Exception('Fehler beim Laden des Datei-Zeigers');
        }

        if (isset($cEinstellung_Arr)) {
            $this->cEinstellung_Arr = $cEinstellung_Arr;
        } else {
            throw new Exception('Fehler beim Laden der Einstellungen');
        }

        $this->loadAttr()
             ->initHead();
    }

    /**
     * L�dt optionale Attribute die der Benutzer in der Plugin-Einstellung selber definiert aus der DB
     *
     * @return $this
     */
    private function loadAttr()
    {
        $res = Shop::DB()->query("
            SELECT kAttribut, kVaterAttribut, cGoogleName, cWertName, eWertHerkunft
                FROM xplugin_" . $this->cEinstellung_Arr['cPluginID'] . "_attribut
                WHERE bAktiv = 1 ORDER BY kVaterAttribut ASC", 2
        );
        foreach ($res as $oAttr) {
            if ($oAttr->kVaterAttribut > 0) {
                if (isset($this->oAttr_arr[$oAttr->kVaterAttribut]->oKindAttr_arr) && count($this->oAttr_arr[$oAttr->kVaterAttribut]->oKindAttr_arr) > 0) {
                    $this->oAttr_arr[$oAttr->kVaterAttribut]->oKindAttr_arr[$oAttr->kAttribut] = $oAttr;
                } else {
                    $this->oAttr_arr[$oAttr->kVaterAttribut]->oKindAttr_arr = array($oAttr->kAttribut => $oAttr);
                }
            } else {
                $this->oAttr_arr[$oAttr->kAttribut] = $oAttr;
            }
        }

        return $this;
    }

    /**
     * Initialisiert den Head (ersetzt Platzhalter f�r Shopname, -beschreibung und -URL)
     *
     * @return $this
     */
    public function initHead()
    {
        $this->cHead     = str_replace('###cShop###', StringHandler::htmlentities($this->cEinstellung_Arr['shopname']), $this->cHead);
        $this->cHead     = str_replace('###cShopUrl###', URL_SHOP, $this->cHead);
        $this->cHead     = str_replace('###cShopBeschreibung###', StringHandler::htmlentities($this->cEinstellung_Arr['shopbeschreibung']), $this->cHead);
        $this->bInitHead = true;

        return $this;
    }

    /**
     * Schreibt $cHead in die Datei $f
     *
     * @return $this
     */
    public function writeHead()
    {
        if ($this->bInitHead === true && $this->bHeadWritten === false) {
            fwrite($this->f, utf8_encode($this->cHead));
            $this->bHeadWritten = true;
        }

        return $this;
    }

    /**
     * Setzt die zu exportierenden kArtikel
     *
     * @param array $nExportArticleIds
     * @return $this
     */
    public function setExportArticleIds($nExportArticleIds)
    {
        if (isset($nExportArticleIds) && is_array($nExportArticleIds) && $this->nExportArticleIds_arr === null) {
            $this->nExportArticleIds_arr = array();
            foreach ($nExportArticleIds as $value) {
                $this->nExportArticleIds_arr[] = (int)$value['kArtikel'];
            }
        }

        return $this;
    }

    /**
     * L�dt Artikelobjekte
     *
     * @param null $kArtikelLoadOne
     * @return $this|bool
     */
    public function loadExportArticle($kArtikelLoadOne = null)
    {
        $oArtikelOptionen                            = new stdClass();
        $oArtikelOptionen->nMerkmale                 = 1;
        $oArtikelOptionen->nAttribute                = 1;
        $oArtikelOptionen->nArtikelAttribute         = 1;
        $oArtikelOptionen->nKategorie                = 1;
        $oArtikelOptionen->nKeinLagerbestandBeachten = 1;

        if ($kArtikelLoadOne !== null) {
            $this->oExportArticle_arr[$kArtikelLoadOne] = new Artikel();
            $this->oExportArticle_arr[$kArtikelLoadOne]->fuelleArtikel($kArtikelLoadOne, $oArtikelOptionen, $this->exportformat->kKundengruppe, $this->exportformat->kSprache, true);
            $this->oExportArticle_arr[$kArtikelLoadOne]->cDeeplink = URL_SHOP . '/' . $this->oExportArticle_arr[$kArtikelLoadOne]->cURL;
            // Kampagne URL
            if (isset($this->exportformat->tkampagne_cParameter)) {
                $cSep = '?';
                if (strpos($this->oExportArticle_arr[$kArtikelLoadOne]->cDeeplink, '.php')) {
                    $cSep = '&';
                }

                $this->oExportArticle_arr[$kArtikelLoadOne]->cDeeplink .= $cSep . $this->exportformat->tkampagne_cParameter . '=' . $this->exportformat->tkampagne_cWert;
            }

            $oTemplate = new Template();
            if ($oTemplate->hasMobileTemplate()) {
                $cSep = '?';
                if (strpos($this->oExportArticle_arr[$kArtikelLoadOne]->cDeeplink, '?')) {
                    $cSep = '&';
                }
                $this->oExportArticle_arr[$kArtikelLoadOne]->cMobilLink = $this->oExportArticle_arr[$kArtikelLoadOne]->cDeeplink . $cSep . 'mt=1';
            }

            if (isset($this->oExportArticle_arr[$kArtikelLoadOne]->kStueckliste) && $this->oExportArticle_arr[$kArtikelLoadOne]->kStueckliste > 0) {
                $this->oExportArticle_arr[$kArtikelLoadOne]->bIsBundle = 'TRUE';
            }
            //Bruttopreis berechnen
            $this->oExportArticle_arr[$kArtikelLoadOne]->fUst      = gibUst($this->oExportArticle_arr[$kArtikelLoadOne]->kSteuerklasse);
            $this->oExportArticle_arr[$kArtikelLoadOne]->fVKBrutto = berechneBrutto($this->oExportArticle_arr[$kArtikelLoadOne]->Preise->fVKNetto * $_SESSION['Waehrung']->fFaktor, $this->oExportArticle_arr[$kArtikelLoadOne]->fUst) . ' ' . $_SESSION['Waehrung']->cISO;

            if ($this->oExportArticle_arr[$kArtikelLoadOne]->nIstVater == '0' && $this->oExportArticle_arr[$kArtikelLoadOne]->kVaterArtikel > 0) {
                $this->loadExportArticle($this->oExportArticle_arr[$kArtikelLoadOne]->kVaterArtikel);
                if (isset($this->oExportArticle_arr[$this->oExportArticle_arr[$kArtikelLoadOne]->kVaterArtikel]->kArtikel)) {
                    if (isset($this->cEinstellung_Arr['ext_artnr_child']) && (int)$this->cEinstellung_Arr['ext_artnr_child'] === 1) {
                        $this->oExportArticle_arr[$kArtikelLoadOne]->cArtNr = $this->oExportArticle_arr[$kArtikelLoadOne]->cArtNr . '_' . $this->oExportArticle_arr[$kArtikelLoadOne]->kArtikel;
                    }
                    $this->oExportArticle_arr[$kArtikelLoadOne]->cVaterArtNr = $this->oExportArticle_arr[$this->oExportArticle_arr[$kArtikelLoadOne]->kVaterArtikel]->cArtNr;
                    unset($this->oExportArticle_arr[$this->oExportArticle_arr[$kArtikelLoadOne]->kVaterArtikel]);
                } else {
                    unset($this->oExportArticle_arr[$kArtikelLoadOne]);
                    unset($this->oExportArticle_arr[$this->oExportArticle_arr[$kArtikelLoadOne]->kVaterArtikel]);
                    Jtllog::writeLog('GoogleShopping Plugin: Artikel mit kArtikel: ' . $kArtikelLoadOne . ' wurde nicht exportiert da kein Vaterartikel vorhanden ist.', JTLLOG_LEVEL_NOTICE);

                    return false;
                }
            }

            $this->loadArtikelMerkmale($kArtikelLoadOne)
                 ->loadAvailibility($kArtikelLoadOne)
                 ->loadImages($kArtikelLoadOne)
                 ->loadGtin($kArtikelLoadOne)
                 ->loadZustand($kArtikelLoadOne)
                 ->loadVersand($kArtikelLoadOne)
                 ->loadCategory($kArtikelLoadOne)
                 ->loadGoogleCategory($kArtikelLoadOne)
                 ->formatArticle($kArtikelLoadOne);
        }

        return $this;
    }

    /**
     * L�dt Artikelmerkmale (Gr��e, Farbe, ...) in das zugeh�rige Artikelobjekt
     *
     * @param int $kArtikel
     * @return $this
     */
    private function loadArtikelMerkmale($kArtikel)
    {
        if (isset($this->oExportArticle_arr[$kArtikel]->cMerkmalAssoc_arr) && is_array($this->oExportArticle_arr[$kArtikel]->cMerkmalAssoc_arr)) {
            $oMapping_arr             = Shop::DB()->query("SELECT cVon, cZu, cType FROM xplugin_" . $this->cEinstellung_Arr['cPluginID'] . "_mapping", 2);
            $cMappingMerkmal_arr      = array();
            $cMappingMerkmalwerte_arr = array();
            foreach ($oMapping_arr as &$oMapping) {
                if (strtolower($oMapping->cType) == 'merkmal') {
                    $cMappingMerkmal_arr[$oMapping->cVon] = $oMapping->cZu;
                } elseif (strtolower($oMapping->cType) == 'merkmalwert') {
                    $cMappingMerkmalwerte_arr[$oMapping->cVon] = $oMapping->cZu;
                }
            }

            foreach ($this->oExportArticle_arr[$kArtikel]->cMerkmalAssoc_arr as $key => $value) {
                if (in_array(strtolower($key), array_keys($cMappingMerkmal_arr))) {
                    $key = $cMappingMerkmal_arr[strtolower($key)];
                }
                if (in_array(strtolower($value), array_keys($cMappingMerkmalwerte_arr))) {
                    $value = $cMappingMerkmalwerte_arr[strtolower($value)];
                }
                if (str_replace(array('�', '�'), array('oe', 'ss'), strtolower($key)) === 'groesse') {
                    $this->oExportArticle_arr[$kArtikel]->cGroesse = $value;
                } elseif (strtolower($key) == 'farbe') {
                    $this->oExportArticle_arr[$kArtikel]->cFarbe = $value;
                } elseif (strtolower($key) == 'geschlecht') {
                    $this->oExportArticle_arr[$kArtikel]->cGeschlecht = $value;
                } elseif (strtolower($key) == 'altersgruppe') {
                    $this->oExportArticle_arr[$kArtikel]->cAltersgruppe = $value;
                } elseif (strtolower($key) == 'muster') {
                    $this->oExportArticle_arr[$kArtikel]->cMuster = $value;
                } elseif (strtolower($key) == 'material') {
                    $this->oExportArticle_arr[$kArtikel]->cMaterial = $value;
                }
            }
        }

        if ($this->oExportArticle_arr[$kArtikel]->nIstVater == '0' && $this->oExportArticle_arr[$kArtikel]->kVaterArtikel > 0) {
            foreach ($this->oExportArticle_arr[$kArtikel]->Variationen as $oVariation) {
                if (strtolower($oVariation->cName) === 'farbe') {
                    foreach ($this->oExportArticle_arr[$kArtikel]->oVariationenNurKind_arr as $oVariationArtikel) {
                        if (strtolower($oVariationArtikel->cName) === 'farbe') {
                            $this->oExportArticle_arr[$kArtikel]->cFarbe = $oVariationArtikel->Werte[0]->cName;
                        }
                    }
                } elseif (strtolower($oVariation->cName) === 'material') {
                    foreach ($this->oExportArticle_arr[$kArtikel]->oVariationenNurKind_arr as $oVariationArtikel) {
                        if (strtolower($oVariationArtikel->cName) == 'material') {
                            $this->oExportArticle_arr[$kArtikel]->cMaterial = $oVariationArtikel->Werte[0]->cName;
                        }
                    }
                } elseif (strtolower($oVariation->cName) === 'muster') {
                    foreach ($this->oExportArticle_arr[$kArtikel]->oVariationenNurKind_arr as $oVariationArtikel) {
                        if (strtolower($oVariationArtikel->cName) == 'muster') {
                            $this->oExportArticle_arr[$kArtikel]->cMuster = $oVariationArtikel->Werte[0]->cName;
                        }
                    }
                } elseif (str_replace(array('�', '�'), array('oe', 'ss'), strtolower($oVariation->cName)) === 'groesse') {
                    foreach ($this->oExportArticle_arr[$kArtikel]->oVariationenNurKind_arr as $oVariationArtikel) {
                        if (str_replace(array('�', '�'), array('oe', 'ss'), strtolower($oVariationArtikel->cName)) === 'groesse') {
                            $this->oExportArticle_arr[$kArtikel]->cGroesse = $oVariationArtikel->Werte[0]->cName;
                        }
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Pr�ft die Verf�gbarkeit und schreibt sie in das zugeh�rige Objekt
     *
     * @param int $kArtikel
     * @return $this
     */
    private function loadAvailibility($kArtikel)
    {
        if ($this->oExportArticle_arr[$kArtikel]->fLagerbestand > 0
            || $this->oExportArticle_arr[$kArtikel]->cLagerBeachten === 'N'
            || $this->oExportArticle_arr[$kArtikel]->cLagerKleinerNull === 'Y'
        ) {
            $this->oExportArticle_arr[$kArtikel]->cVerfuegbarkeit = 'in stock';
        } else {
            $this->oExportArticle_arr[$kArtikel]->cVerfuegbarkeit = 'out of stock';
        }

        return $this;
    }

    /**
     * L�dt die Bild-Links in das zugeh�rige Artikelobjekt
     *
     * @param int $kArtikel
     * @return $this
     */
    private function loadImages($kArtikel)
    {
        $shopURL = Shop::getURL() . '/';
        $this->oExportArticle_arr[$kArtikel]->Artikelbild = $shopURL . $this->oExportArticle_arr[$kArtikel]->Bilder[0]->cPfadGross;
        $imageCount                                       = count($this->oExportArticle_arr[$kArtikel]->Bilder);
        for ($i = 1; $i < $imageCount && $i <= 10; $i++) {
            $this->oExportArticle_arr[$kArtikel]->cArtikelbild_arr[] = $shopURL . $this->oExportArticle_arr[$kArtikel]->Bilder[$i]->cPfadGross;
        }

        return $this;
    }

    /**
     * Pr�ft, ob cBarcode oder cISBN gesetzt ist und l�dt es als Gtin ins Artikelobjekt
     *
     * @param int $kArtikel
     * @return $this
     */
    private function loadGtin($kArtikel)
    {
        if (isset($this->oExportArticle_arr[$kArtikel]->cBarcode) && !empty($this->oExportArticle_arr[$kArtikel]->cBarcode)) {
            $this->oExportArticle_arr[$kArtikel]->cGtin = $this->oExportArticle_arr[$kArtikel]->cBarcode;
        } elseif (isset($this->oExportArticle_arr[$kArtikel]->cISBN) && !empty($this->oExportArticle_arr[$kArtikel]->cISBN)) {
            $this->oExportArticle_arr[$kArtikel]->cGtin = $this->oExportArticle_arr[$kArtikel]->cISBN;
        }

        return $this;
    }

    /**
     * Pr�ft ob f�r den Artikel ein Zustand verf�gbar ist. Wenn ja wird dieser geladen sonst Standart-Wert aus den Plugineinstellungen
     *
     * @param int $kArtikel
     * @return $this
     */
    private function loadZustand($kArtikel)
    {
        if (isset($this->oExportArticle_arr[$kArtikel]->FunktionsAttribute[$this->cEinstellung_Arr['artikle_condition']]) &&
            is_string($this->oExportArticle_arr[$kArtikel]->FunktionsAttribute[$this->cEinstellung_Arr['artikle_condition']])) {
            $this->oExportArticle_arr[$kArtikel]->cZustand = $this->oExportArticle_arr[$kArtikel]->FunktionsAttribute[$this->cEinstellung_Arr['artikle_condition']];
        } else {
            $this->oExportArticle_arr[$kArtikel]->cZustand = $this->cEinstellung_Arr['artikle_condition_standard'];
        }

        return $this;
    }

    /**
     * L�dt Versanddaten (Versandklass, Lieferland und Versandkosten)
     *
     * @param int $kArtikel
     * @return $this
     */
    private function loadVersand($kArtikel)
    {
        $this->oExportArticle_arr[$kArtikel]->cLieferland = $this->cEinstellung_Arr['exportformate_lieferland'];

        $fVersandkosten = number_format(gibGuenstigsteVersandkosten(
            $this->cEinstellung_Arr['exportformate_lieferland'],
            $this->oExportArticle_arr[$kArtikel],
            0,
            $this->exportformat->kKundengruppe
        ), 2);
        if ($fVersandkosten < 0) {
            $fVersandkosten = 0.00;
        }
        $this->oExportArticle_arr[$kArtikel]->Versandkosten = $fVersandkosten . ' ' . $_SESSION['Waehrung']->cISO;

        return $this;
    }

    /**
     * L�dt Kategorie(n)
     *
     * @param int $kArtikel
     * @return $this
     */
    private function loadCategory($kArtikel)
    {
        $nCount = 0;
        if (count($this->oExportArticle_arr[$kArtikel]->oKategorie_arr) > 0) {
            foreach ($this->oExportArticle_arr[$kArtikel]->oKategorie_arr as $oCategorie) {
                $this->oExportArticle_arr[$kArtikel]->cCategorie_arr[] = implode(' &gt; ', $oCategorie->cKategoriePfad_arr);
                $nCount++;
                if ($nCount >= 10) {
                    return $this;
                }
            }
        } else {
            unset($this->oExportArticle_arr[$kArtikel]);
        }

        return $this;
    }

    /**
     * L�dt Google-Kategorie.
     * Wenn f�r den Artikel eine GoogleKategorie angegeben ist wird diese verwendet sonst die Google-Kategorie der Artikelkategorie im Shop
     *
     * @param int $kArtikel
     * @return $this
     */
    private function loadGoogleCategory($kArtikel)
    {
        if (isset($this->oExportArticle_arr[$kArtikel]->FunktionsAttribute[$this->cEinstellung_Arr['artikle_googlekat']]) &&
            is_string($this->oExportArticle_arr[$kArtikel]->FunktionsAttribute[$this->cEinstellung_Arr['artikle_googlekat']])) {
            $this->oExportArticle_arr[$kArtikel]->cGoogleCategorie[] = str_replace(
                array('"', '>'),
                array('', '&gt;'),
                $this->oExportArticle_arr[$kArtikel]->FunktionsAttribute[$this->cEinstellung_Arr['artikle_googlekat']]);
        } elseif (isset($this->oExportArticle_arr[$kArtikel]->oKategorie_arr) && is_array($this->oExportArticle_arr[$kArtikel]->oKategorie_arr)) {
            foreach ($this->oExportArticle_arr[$kArtikel]->oKategorie_arr as $oCategorie) {
                if (isset($oCategorie->KategorieAttribute[$this->cEinstellung_Arr['artikle_googlekat']]) &&
                    !empty($oCategorie->KategorieAttribute[$this->cEinstellung_Arr['artikle_googlekat']])) {
                    if (empty($this->oExportArticle_arr[$kArtikel]->cGoogleCategorie) || !is_array($this->oExportArticle_arr[$kArtikel]->cGoogleCategorie) ||
                        !in_array(str_replace(
                            array('"', '>'),
                            array('', '&gt;'),
                            $oCategorie->KategorieAttribute[$this->cEinstellung_Arr['artikle_googlekat']]),
                            $this->oExportArticle_arr[$kArtikel]->cGoogleCategorie)) {
                        $this->oExportArticle_arr[$kArtikel]->cGoogleCategorie[] = str_replace(
                            array('"', '>'),
                            array('', '&gt;'),
                            $oCategorie->KategorieAttribute[$this->cEinstellung_Arr['artikle_googlekat']]);

                        return $this;
                    }
                } elseif ($oCategorie->kOberKategorie > 0) {
                    if ($this->loadGoogleCategoryVater($oCategorie->kOberKategorie, $kArtikel)) {
                        return $this;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * L�dt Google-Kategorie wenn keine beim Artikel hinterlegt ist oder bei der Kategorie keine angegeben ist.
     * Verwendet die Google-Kategorie der Vater-Kategorie im Shop
     *
     * @param int $kKategorie
     * @param int $kArtikel
     * @return bool
     */
    private function loadGoogleCategoryVater($kKategorie, $kArtikel)
    {
        $oKategorie = new Kategorie($kKategorie, $this->exportformat->kSprache, $this->exportformat->kKundengruppe);
        if (isset($oKategorie->KategorieAttribute[$this->cEinstellung_Arr['artikle_googlekat']]) &&
            !empty($oKategorie->KategorieAttribute[$this->cEinstellung_Arr['artikle_googlekat']])) {
            if (!isset($this->oExportArticle_arr[$kArtikel]->cGoogleCategorie) ||
                !is_array($this->oExportArticle_arr[$kArtikel]->cGoogleCategorie) ||
                !in_array(str_replace(array('"', '>'), array('', '&gt;'), $oKategorie->KategorieAttribute[$this->cEinstellung_Arr['artikle_googlekat']]), $this->oExportArticle_arr[$kArtikel]->cGoogleCategorie)) {
                $this->oExportArticle_arr[$kArtikel]->cGoogleCategorie[] = str_replace(array('"', '>'), array('', '&gt;'), $oKategorie->KategorieAttribute[$this->cEinstellung_Arr['artikle_googlekat']]);

                return true;
            }
        } elseif ($oKategorie->kOberKategorie > 0) {
            if ($this->loadGoogleCategoryVater($oKategorie->kOberKategorie, $kArtikel)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Formatiert die Artikelattribute f�r die Ausgabe als XML
     *
     * @param int $kArtikel
     * @return $this
     */
    private function formatArticle($kArtikel)
    {
        if (isset($this->oExportArticle_arr[$kArtikel])) {
            $this->oExportArticle_arr[$kArtikel]->cBeschreibungHTML     = str_replace('"', '&quot;', $this->oExportArticle_arr[$kArtikel]->cBeschreibung);
            $this->oExportArticle_arr[$kArtikel]->cKurzBeschreibungHTML = str_replace('"', '&quot;', $this->oExportArticle_arr[$kArtikel]->cKurzBeschreibung);

            $find    = array("<br />", "<br>", "</");
            $replace = array(' ', ' ', ' </');

            //Wenn keine Beschreibung vorhanden ist dann nehme vordefinierten Text
            if (empty($this->oExportArticle_arr[$kArtikel]->cBeschreibung)) {
                $this->oExportArticle_arr[$kArtikel]->cBeschreibung = "Leider steht zu diesem Artikel keine Beschreibung bereit.";
            }
            if (empty($this->oExportArticle_arr[$kArtikel]->cKurzBeschreibung)) {
                $this->oExportArticle_arr[$kArtikel]->cKurzBeschreibung = "Leider steht zu diesem Artikel keine Beschreibung bereit.";
            }

            if ($this->cEinstellung_Arr['artikle_groundprice'] == 1 && isset($this->oExportArticle_arr[$kArtikel]->cLocalizedVPE[0]) && strlen($this->oExportArticle_arr[$kArtikel]->cLocalizedVPE[0]) > 0) {
                $cPricePerUnit                              = ' (' . str_replace(array($_SESSION['Waehrung']->cNameHTML, ' pro '), array($_SESSION['Waehrung']->cISO, '/'), $this->oExportArticle_arr[$kArtikel]->cLocalizedVPE[0]) . ')';
                $this->oExportArticle_arr[$kArtikel]->cName = substr(str_replace($find, $replace, $this->oExportArticle_arr[$kArtikel]->cName), 0, (70 - strlen($cPricePerUnit))) . $cPricePerUnit;
            } elseif ($this->cEinstellung_Arr['artikle_groundprice'] == 2 && isset($this->oExportArticle_arr[$kArtikel]->cLocalizedVPE[0]) && strlen($this->oExportArticle_arr[$kArtikel]->cLocalizedVPE[0]) > 0) {
                unset($this->oExportArticle_arr);

                return false;
            } else {
                $this->oExportArticle_arr[$kArtikel]->cName = substr(str_replace($find, $replace, $this->oExportArticle_arr[$kArtikel]->cName), 0, 70);
            }

            $this->oExportArticle_arr[$kArtikel]->cName             = strip_tags($this->oExportArticle_arr[$kArtikel]->cName);
            $this->oExportArticle_arr[$kArtikel]->cBeschreibung     = strip_tags($this->oExportArticle_arr[$kArtikel]->cBeschreibung);
            $this->oExportArticle_arr[$kArtikel]->cKurzBeschreibung = strip_tags($this->oExportArticle_arr[$kArtikel]->cKurzBeschreibung);

            $this->oExportArticle_arr[$kArtikel]->cBeschreibung     = substr(str_replace($find, $replace, $this->oExportArticle_arr[$kArtikel]->cBeschreibung), 0, 5000);
            $this->oExportArticle_arr[$kArtikel]->cKurzBeschreibung = str_replace($find, $replace, $this->oExportArticle_arr[$kArtikel]->cKurzBeschreibung);

            $find    = array("\r\n", "\r", "\n", "\x0B", "\x0");
            $replace = array(' ', ' ', ' ', ' ', '');
        }

        //$this->oExportArticle_arr[$kArtikel]->cName = unhtmlentities($this->oExportArticle_arr[$kArtikel]->cName);
        //$this->oExportArticle_arr[$kArtikel]->cBeschreibung = unhtmlentities($this->oExportArticle_arr[$kArtikel]->cBeschreibung);
        //$this->oExportArticle_arr[$kArtikel]->cKurzBeschreibung = unhtmlentities($this->oExportArticle_arr[$kArtikel]->cKurzBeschreibung);
        //$this->oExportArticle_arr[$kArtikel]->cName = removeWhitespace(str_replace($find, $replace, $this->oExportArticle_arr[$kArtikel]->cName));
        //$this->oExportArticle_arr[$kArtikel]->cBeschreibung = removeWhitespace(str_replace($find, $replace, $this->oExportArticle_arr[$kArtikel]->cBeschreibung));
        //$this->oExportArticle_arr[$kArtikel]->cKurzBeschreibung = removeWhitespace(str_replace($find, $replace, $this->oExportArticle_arr[$kArtikel]->cKurzBeschreibung));
        //$this->oExportArticle_arr[$kArtikel]->cBeschreibungHTML = removeWhitespace(str_replace($find, $replace, $this->oExportArticle_arr[$kArtikel]->cBeschreibungHTML));
        //$this->oExportArticle_arr[$kArtikel]->cKurzBeschreibungHTML = removeWhitespace(str_replace($find, $replace, $this->oExportArticle_arr[$kArtikel]->cKurzBeschreibungHTML));

        return $this;
    }

    /**
     * Schreibt $cFoot in die Datei $f
     *
     * @return $this
     */
    public function writeFoot()
    {
        fwrite($this->f, utf8_encode($this->cFoot));

        return $this;
    }

    /**
     * Ruft f�r jeden Artikel die Methode writeArticle auf
     *
     * @return $this
     */
    public function writeContent()
    {
        if (isset($this->oExportArticle_arr) && is_array($this->oExportArticle_arr) && isset($this->oAttr_arr) && count($this->oAttr_arr) > 0) {
            foreach ($this->nExportArticleIds_arr as $kArtikel) {
                $this->loadExportArticle($kArtikel);
                $this->writeArticle($this->oExportArticle_arr[$kArtikel]);
                unset($this->oExportArticle_arr[$kArtikel]);
            }
        }

        return $this;
    }

    /**
     * Schreibt Artikel in die Datei $f
     *
     * @param Object $oArticle_arr
     */
    private function writeArticle($oArticle_arr)
    {
        $cPreAttribute      = "\t\t\t";
        $cPreChildAttribute = "\t\t\t\t";

        if (isset($oArticle_arr) && is_object($oArticle_arr) && $oArticle_arr->kArtikel > 0) {
            $cXML = "\t\t<item>\r";
            foreach ($this->oAttr_arr as $oAttr) {
                if ($oAttr->eWertHerkunft === 'VaterAttribut') {
                    if (isset($oAttr->oKindAttr_arr) && count($oAttr->oKindAttr_arr) > 0) {
                        $cXML .= $cPreAttribute . "<" . $oAttr->cGoogleName . ">\r";
                        foreach ($oAttr->oKindAttr_arr as $oKindAttr) {
                            if ($oKindAttr->eWertHerkunft == 'WertName') {
                                $cXML .= $this->writeAttribute($cPreChildAttribute, $oKindAttr->cGoogleName, $oKindAttr->cWertName);
                            } elseif ($oKindAttr->eWertHerkunft === 'ArtikelEigenschaft') {
                                if (!empty($oArticle_arr->{$oKindAttr->cWertName})) {
                                    $cXML .= $this->writeAttribute($cPreChildAttribute, $oKindAttr->cGoogleName, $oArticle_arr->{$oKindAttr->cWertName});
                                }
                            } elseif ($oKindAttr->eWertHerkunft === 'FunktionsAttribut') {
                                if (isset($oArticle_arr->FunktionsAttribute[strtolower($oKindAttr->cWertName)]) && !empty($oArticle_arr->FunktionsAttribute[strtolower($oKindAttr->cWertName)])) {
                                    $cXML .= $this->writeAttribute($cPreChildAttribute, $oKindAttr->cGoogleName, $oArticle_arr->FunktionsAttribute[strtolower($oKindAttr->cWertName)]);
                                }
                            } elseif ($oKindAttr->eWertHerkunft === 'Attribut') {
                                if (isset($oArticle_arr->AttributeAssoc[$oKindAttr->cWertName]) && !empty($oArticle_arr->AttributeAssoc[$oKindAttr->cWertName])) {
                                    $cXML .= $this->writeAttribute($cPreChildAttribute, $oKindAttr->cGoogleName, $oArticle_arr->AttributeAssoc[$oKindAttr->cWertName]);
                                }
                            } elseif ($oKindAttr->eWertHerkunft === 'Merkmal') {
                                $cWertName = preg_replace("/[^�������a-zA-Z0-9\.\-_]/", "", $oKindAttr->cWertName);
                                if (isset($oArticle_arr->cMerkmalAssoc_arr[$cWertName]) && !empty($oArticle_arr->cMerkmalAssoc_arr[$cWertName])) {
                                    $cXML .= $this->writeAttribute($cPreChildAttribute, $oKindAttr->cGoogleName, $oArticle_arr->cMerkmalAssoc_arr[$cWertName]);
                                }
                            }
                        }
                        $cXML .= $cPreAttribute . "</" . $oAttr->cGoogleName . ">\r";
                    }
                } elseif ($oAttr->eWertHerkunft === 'WertName') {
                    $cXML .= $this->writeAttribute($cPreAttribute, $oAttr->cGoogleName, $oAttr->cWertName);
                } elseif ($oAttr->eWertHerkunft === 'ArtikelEigenschaft') {
                    if (!empty($oArticle_arr->{$oAttr->cWertName})) {
                        $cXML .= $this->writeAttribute($cPreAttribute, $oAttr->cGoogleName, $oArticle_arr->{$oAttr->cWertName});
                    }
                } elseif ($oAttr->eWertHerkunft === 'FunktionsAttribut') {
                    if (isset($oArticle_arr->FunktionsAttribute[strtolower($oAttr->cWertName)]) && !empty($oArticle_arr->FunktionsAttribute[strtolower($oAttr->cWertName)])) {
                        $cXML .= $this->writeAttribute($cPreAttribute, $oAttr->cGoogleName, $oArticle_arr->FunktionsAttribute[strtolower($oAttr->cWertName)]);
                    }
                } elseif ($oAttr->eWertHerkunft === 'Attribut') {
                    if (isset($oArticle_arr->AttributeAssoc[$oAttr->cWertName]) && !empty($oArticle_arr->AttributeAssoc[$oAttr->cWertName])) {
                        $cXML .= $this->writeAttribute($cPreAttribute, $oAttr->cGoogleName, $oArticle_arr->AttributeAssoc[$oAttr->cWertName]);
                    }
                } elseif ($oAttr->eWertHerkunft === 'Merkmal') {
                    $cWertName = preg_replace("/[^�������a-zA-Z0-9\.\-_]/", "", $oAttr->cWertName);
                    if (isset($oArticle_arr->cMerkmalAssoc_arr[$cWertName]) && !empty($oArticle_arr->cMerkmalAssoc_arr[$cWertName])) {
                        $cXML .= $this->writeAttribute($cPreAttribute, $oAttr->cGoogleName, $oArticle_arr->cMerkmalAssoc_arr[$cWertName]);
                    }
                }
            }

            //Pflicht Attribute
            /* foreach ($this->requiredAttributes_arr as $cGoogleAttribut => $jtlAttribut_arr) {
              if($jtlAttribut_arr[0] === 0) {
              if(empty ($oArticle_arr->$jtlAttribut_arr[1])) {
              $this->cError_arr[] = "F�r das Pflichtattribut ".$cGoogleAttribut." ist kein Wert vorhanden!";
              Jtllog::writeLog("F�r das Pflichtattribut ".$cGoogleAttribut." ist kein Wert vorhanden (".$oArticle_arr->cName.")!", JTLLOG_LEVEL_DEBUG);
              } else {
              $cXML .= $this->writeAttribute($cPreAttribute, $cGoogleAttribut, $oArticle_arr->$jtlAttribut_arr[1]);
              }
              } else {
              $cXML .= $cPreAttribute."<".$cGoogleAttribut.">\r";
              foreach ($this->requiredChildAttributes_arr[$jtlAttribut_arr[0]] as $cGoogleChildAttribut => $jtlChildAttribut_arr) {
              $oVersand = $oArticle_arr->$jtlAttribut_arr[1];
              if(empty ($oVersand->$jtlChildAttribut_arr[1])){
              $this->cError_arr[] = "F�r das Pflichtattribut ".$cGoogleChildAttribut." ist kein Wert vorhanden!";
              Jtllog::writeLog("F�r das Pflichtattribut ".$cGoogleChildAttribut." ist kein Wert vorhanden (".$oArticle_arr->cName.")!", JTLLOG_LEVEL_DEBUG);
              } else {
              $cXML .= $this->writeAttribute($cPreChildAttribute, $cGoogleChildAttribut, $oVersand->$jtlChildAttribut_arr[1]);
              }
              unset ($oVersand);
              }
              $cXML .= $cPreAttribute."</".$cGoogleAttribut.">\r";
              }
              }
              //Optionale Attribute
              foreach ($this->optionalAttributes_arr as $cGoogleAttribut => $jtlAttribut_arr) {
              //$jtlAttribut_arr[0] === 0 hei�t ist KEIN Funktionsattribut
              if($jtlAttribut_arr[0] === 0) {
              if(isset($oArticle_arr->$jtlAttribut_arr[1]) && !empty ($oArticle_arr->$jtlAttribut_arr[1])) {
              $cXML .= $this->writeAttribute($cPreAttribute, $cGoogleAttribut, $oArticle_arr->$jtlAttribut_arr[1]);
              }
              } else {
              if(isset($oArticle_arr->FunktionsAttribute[strtolower($jtlAttribut_arr[1])]) && !empty ($oArticle_arr->FunktionsAttribute[strtolower($jtlAttribut_arr[1])])) {
              $cXML .= $this->writeAttribute($cPreAttribute, $cGoogleAttribut, $oArticle_arr->FunktionsAttribute[strtolower($jtlAttribut_arr[1])]);
              }
              }
              } */
            $cXML .= "\t\t</item>\r";
            fwrite($this->f, utf8_encode($cXML));
        }
    }

    /**
     * Generiert einen XML-String f�r das Attribut: $cAttributeName mit dem Inhalt: $cContent und dem Prefix: $cPreAttribute
     * Wenn $cContent ein Array ist dann ruft sich die Methode rekursiv auf
     *
     * @param string $cPreAttribute
     * @param string $cAttributeName
     * @param string $cContent
     * @return string mit XML f�r das Attribut
     */
    private function writeAttribute($cPreAttribute, $cAttributeName, $cContent)
    {
        $cXML = '';
        if (isset($cContent) && is_array($cContent)) {
            foreach ($cContent as $value) {
                $cXML .= $this->writeAttribute($cPreAttribute, $cAttributeName, $value);
            }
        } else {
            $cXML .= $cPreAttribute . "<" . $cAttributeName . "><![CDATA[";
            $cXML .= trim($cContent);
            $cXML .= "]]></" . $cAttributeName . ">\r";
        }

        return $cXML;
    }
}
