<?php
/**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 */

/**
 * Class Status
 */
class Status
{
    use SingletonTrait;

    /**
     * @var array
     */
    protected $cache = [];

    /**
     * @param string $name
     * @param mixed $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        if (!isset($this->cache[$name]) || $this->cache[$name] !== null) {
            $this->cache[$name] = call_user_func_array([&$this, $name], $arguments);
        }

        return $this->cache[$name];
    }

    /**
     * @return JTLCache
     */
    protected function getObjectCache()
    {
        $cache = JTLCache::getInstance();
        $cache->setJtlCacheConfig();

        return $cache;
    }

    /**
     * @return object
     */
    protected function getImageCache()
    {
        return MediaImage::getStats(Image::TYPE_PRODUCT, false);
    }

    /**
     * @return object
     */
    protected function getSystemLogInfo()
    {
        $flags = getSytemlogFlag(false);

        return (object)[
            'error'  => Jtllog::isBitFlagSet(JTLLOG_LEVEL_ERROR, $flags) > 0,
            'notice' => Jtllog::isBitFlagSet(JTLLOG_LEVEL_NOTICE, $flags) > 0,
            'debug'  => Jtllog::isBitFlagSet(JTLLOG_LEVEL_DEBUG, $flags) > 0
        ];
    }

    /**
     * @return bool
     */
    protected function validDatabateStruct()
    {
        require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'dbcheck_inc.php';

        $current  = getDBStruct();
        $original = getDBFileStruct();

        if (is_array($current) && is_array($original)) {
            return count(compareDBStruct($original, $current)) === 0;
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function validFileStruct()
    {
        require_once PFAD_ROOT . PFAD_ADMIN . PFAD_INCLUDES . 'filecheck_inc.php';

        $files = $stats = [];
        if (getAllFiles($files, $stats) === 1) {
            return end($stats) === 0;
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function validFolderPermissions()
    {
        $oFsCheck = new Systemcheck_Platform_Filesystem(PFAD_ROOT);

        $permissionStat = $oFsCheck->getFolderStats();

        return $permissionStat->nCountInValid === 0;
    }

    /**
     * @return array
     */
    protected function getPluginSharedHooks()
    {
        $sharedPlugins = [];
        $sharedHookIds = Shop::DB()->executeQuery(
            "SELECT nHook 
                FROM tpluginhook 
                GROUP BY nHook 
                HAVING COUNT(DISTINCT kPlugin) > 1", 2
        );

        array_walk($sharedHookIds, function (&$val, $key) {
            $val = (int)$val->nHook;
        });

        foreach ($sharedHookIds as $hookId) {
            $sharedPlugins[$hookId] = [];
            $plugins                = Shop::DB()->executeQuery(
                "SELECT DISTINCT tpluginhook.kPlugin, tplugin.cName, tplugin.cPluginID 
                    FROM tpluginhook 
                    INNER JOIN tplugin 
                        ON tpluginhook.kPlugin = tplugin.kPlugin 
                    WHERE tpluginhook.nHook = " . $hookId . " 
                        AND tplugin.nStatus = 2", 2
            );
            foreach ($plugins as $plugin) {
                $sharedPlugins[$hookId][$plugin->cPluginID] = $plugin;
            }
        }

        return $sharedPlugins;
    }

    /**
     * @return bool
     */
    protected function hasPendingUpdates()
    {
        $updater = new Updater();

        return $updater->hasPendingUpdates();
    }

    /**
     * @return bool
     */
    protected function hasActiveProfiler()
    {
        return Profiler::getIsActive() !== 0;
    }

    /**
     * @return bool
     */
    protected function hasInstallDir()
    {
        return is_dir(PFAD_ROOT . 'install');
    }

    /**
     * @return bool
     */
    protected function hasDifferentTemplateVersion()
    {
        $template = Template::getInstance();

        return JTL_VERSION != $template->getShopVersion();
    }

    /**
     * @return bool
     */
    protected function hasMobileTemplateIssue()
    {
        $oTemplate = Shop::DB()->select('ttemplate', 'eTyp', 'standard');
        if (isset($oTemplate)) {
            $oTplData = TemplateHelper::getInstance(false)->getData($oTemplate->cTemplate);
            if ($oTplData->bResponsive) {
                $oMobileTpl = Shop::DB()->select('ttemplate', 'eTyp', 'mobil');
                if ($oMobileTpl !== null) {
                    $cXMLFile = PFAD_ROOT . PFAD_TEMPLATES . $oMobileTpl->cTemplate .
                        DIRECTORY_SEPARATOR . TEMPLATE_XML;
                    if (file_exists($cXMLFile)) {
                        return true;
                    }
                    // Wenn ein Template aktiviert aber physisch nicht vorhanden ist,
                    // ist der DB-Eintrag falsch und wird gelöscht
                    Shop::DB()->delete('ttemplate', 'eTyp', 'mobil');
                }
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    protected function hasStandardTemplateIssue()
    {
        $oTemplate = Shop::DB()->select('ttemplate', 'eTyp', 'standard');

        return $oTemplate === null;
    }

    /**
     * @return mixed|null
     */
    protected function getSubscription()
    {
        if (!isset($_SESSION['subscription']) || $_SESSION['subscription'] === null) {
            $_SESSION['subscription'] = jtlAPI::getSubscription();
        }
        if (is_object($_SESSION['subscription']) &&
            isset($_SESSION['subscription']->kShop) &&
            (int)$_SESSION['subscription']->kShop > 0
        ) {
            return $_SESSION['subscription'];
        }

        return null;
    }

    /**
     * @return bool
     */
    protected function hasValidEnvironment()
    {
        $systemcheck = new Systemcheck_Environment();
        $systemcheck->executeTestGroup('Shop4');

        return $systemcheck->getIsPassed();
    }

    /**
     * @return array
     */
    protected function getEnvironmentTests()
    {
        $systemcheck = new Systemcheck_Environment();

        return $systemcheck->executeTestGroup('Shop4');
    }

    /**
     * @return Systemcheck_Platform_Hosting
     */
    protected function getPlatform()
    {
        return new Systemcheck_Platform_Hosting();
    }

    /**
     * @return array
     */
    protected function getMySQLStats()
    {
        $stats = Shop::DB()->stats();
        $info  = Shop::DB()->info();
        $lines = explode('  ', $stats);

        $lines = array_map(function ($v) {
            @list($key, $value) = @explode(':', $v, 2);

            return ['key' => trim($key), 'value' => trim($value)];
        }, $lines);

        $lines = array_merge([['key' => 'Version', 'value' => $info]], $lines);

        return $lines;
    }

    /**
     * @return array
     */
    protected function getPaymentMethodsWithError()
    {
        $incorrectPaymentMethods = [];
        $paymentMethods          = Shop::DB()->selectAll(
            'tzahlungsart',
            'nActive',
            1,
            '*',
            'cAnbieter, cName, nSort, kZahlungsart'
        );

        if (is_array($paymentMethods)) {
            foreach ($paymentMethods as $i => $method) {
                $log  = new ZahlungsLog($method->cModulId);
                $logs = $log->holeLog();

                if (!is_array($logs)) {
                    continue;
                }

                foreach ($logs as $entry) {
                    if (intval($entry->nLevel) === JTLLOG_LEVEL_ERROR) {
                        $method->logs              = $logs;
                        $incorrectPaymentMethods[] = $method;
                        break;
                    }
                }
            }
        }

        return $incorrectPaymentMethods;
    }

    /**
     * @return bool
     */
    protected function hasInvalidPollCoupons()
    {
        $aPollCoupons        = Shop::DB()->selectAll('tumfrage', 'nAktiv', 1);
        $invalidCouponsFound = false;

        if (count($aPollCoupons) > 0) {
            foreach ($aPollCoupons as $Kupon) {
                if ($Kupon->kKupon > 0){
                    $kKupon = Shop::DB()->select(
                        'tkupon',
                        'kKupon',
                        $Kupon->kKupon,
                        'cAktiv',
                        'Y',
                        null,
                        null,
                        false,
                        'kKupon'
                    );
                    $invalidCouponsFound = empty($kKupon);
                }
            }
        }

        return $invalidCouponsFound;
    }

    /**
     * @param bool $has
     * @return array|bool
     */
    protected function getOrphanedCategories($has = true)
    {
        $categories = Shop::DB()->query("
            SELECT kKategorie, cName 
                FROM tkategorie 
                WHERE kOberkategorie > 0 
                    AND kOberkategorie NOT IN (SELECT DISTINCT kKategorie FROM tkategorie)", 2
        );

        return ($has === true)
            ? count($categories) === 0
            : $categories;
    }
}
