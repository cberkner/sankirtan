<?php
/**
 * @package jtl\Systemcheck\Shop4
 * @author Clemens Rudolph <clemens.rudolph@jtl-software.com>
 * @copyright 2016 JTL-Software-GmbH
 */

/**
 * Systemcheck_Tests_Shop4_PhpPdoMysqlSupport
 */
class Systemcheck_Tests_Shop4_PhpPdoMysqlSupport extends Systemcheck_Tests_PhpModuleTest
{
    protected $name          = 'PDO::MySQL - Unterstützung';
    protected $requiredState = 'enabled';
    protected $description   = 'Für JTL-Shop4 wird die Unterstützung für PHP-Data-Objects (<code>php-pdo</code>) benötigt.';
    protected $isOptional    = false;
    protected $isRecommended = false;

    /**
     * Execute the test for PDO and its drivers we need
     *
     * @return void
     */
    public function execute()
    {
        // helper closure:
        // (some extensions- and/or driver-names are written in camel-case or big letters only)
        $lowercase = function ($element) {
            return strtolower($element);
        };

        $this->result = Systemcheck_Tests_Test::RESULT_FAILED;  // there is no PDO installed at all

        // check, if the PDO is there and loaded
        //
        if (in_array('pdo', array_map($lowercase, get_loaded_extensions())) && extension_loaded('pdo')) {
            // check, if MySQL-driver is available
            // (and the "pdo_mysql" extension is ther too)
            $vAvailableDrivers = array_map($lowercase, PDO::getAvailableDrivers());
            if (0 === count($vAvailableDrivers)) {
                $this->result = Systemcheck_Tests_Test::RESULT_UNKNOWN;  // we got a PDO, but no drivers at all
                return;
            }
            if (in_array('mysql', $vAvailableDrivers) && extension_loaded('pdo_mysql')) {
                $this->result = Systemcheck_Tests_Test::RESULT_OK;
                return;
            }
        }
    }
}

