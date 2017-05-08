<?php
/**
 * @copyright JTL-Software-GmbH
 * @package jtl\Systemcheck\Shop4
 */

/**
 * Systemcheck_Tests_Shop4_PhpVersion
 */
class Systemcheck_Tests_Shop4_PhpVersion extends Systemcheck_Tests_ProgramTest
{
    protected $name          = 'PHP-Version';
    protected $requiredState = '>= 5.4.0';
    protected $description   = '';
    protected $isOptional    = false;
    protected $isRecommended = false;

    public function execute()
    {
        $version            = phpversion();
        $this->currentState = $version;
        $this->result       = Systemcheck_Tests_Test::RESULT_FAILED;

        if (version_compare($version, '5.4.0', '>=')) {
            $this->result = Systemcheck_Tests_Test::RESULT_OK;
        }

    }
}
