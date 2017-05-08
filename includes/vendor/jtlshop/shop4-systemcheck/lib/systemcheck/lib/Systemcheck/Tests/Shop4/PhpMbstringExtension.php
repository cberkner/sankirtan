<?php
/**
 * @copyright JTL-Software-GmbH
 * @package jtl\Systemcheck\Shop4
 */

/**
 * Systemcheck_Tests_Shop4_PhpMbstringExtension
 */
class Systemcheck_Tests_Shop4_PhpMbstringExtension extends Systemcheck_Tests_PhpModuleTest
{
    protected $name          = 'mbstring-Unterstützung';
    protected $requiredState = 'enabled';
    protected $description   = 'Die <code>mbstring</code>-Erweiterung ist zum Betrieb des JTL-Shop4 zwingend erforderlich.';
    protected $isOptional    = false;
    protected $isRecommended = false;

    public function execute()
    {
        $this->result = Systemcheck_Tests_Test::RESULT_FAILED;

        if (extension_loaded('mbstring')) {
            $this->result = Systemcheck_Tests_Test::RESULT_OK;
        }
    }
}
