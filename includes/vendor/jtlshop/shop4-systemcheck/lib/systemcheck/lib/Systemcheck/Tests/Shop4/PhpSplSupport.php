<?php

/**
 * @copyright 2010-2014 JTL-Software-GmbH
 * @author Christian Spoo <christian.spoo@jtl-software.com>
 * @package jtl\Systemcheck\Shop4
 */


/**
 * Systemcheck_Tests_Shop4_PhpSplSupport
 */
class Systemcheck_Tests_Shop4_PhpSplSupport extends Systemcheck_Tests_PhpModuleTest
{
    protected $name          = 'PHP-SPL-Unterstützung';
    protected $requiredState = 'enabled';
    protected $description   = 'Für JTL-Shop4 wird Unterstützung für die Standard PHP Library (SPL) benötigt.';
    protected $isOptional    = false;
    protected $isRecommended = false;

    public function execute()
    {
        $this->result = Systemcheck_Tests_Test::RESULT_OK;

        if (!extension_loaded('SPL')) {
            $this->result = Systemcheck_Tests_Test::RESULT_FAILED;
            return;
        }

        if (!function_exists('spl_autoload_register')) {
            $this->result = Systemcheck_Tests_Test::RESULT_FAILED;
            return;
        }
    }
}
