<?php
/**
 * @copyright JTL-Software-GmbH
 * @package jtl\Systemcheck\Shop4
 */

/**
 * Systemcheck_Tests_Shop4_PhpImagickExtension
 */
class Systemcheck_Tests_Shop4_PhpImagickExtension extends Systemcheck_Tests_PhpModuleTest
{
    protected $name          = 'ImageMagick-Unterstützung';
    protected $requiredState = 'enabled';
    protected $description   = 'JTL-Shop4 benötigt, für die dynamische Generierung von Bildern, die PHP-Erweiterung <code>php-imagick</code>.<br>Diese Erweiterung ist auf Debian-Systemen als <code>php5-imagick,</code> sowie auf Fedora/RedHat-Systemen als <code>php-pecl-imagick</code> verfügbar.';
    protected $isOptional    = true;
    protected $isRecommended = true;

    public function execute()
    {
        $this->result = Systemcheck_Tests_Test::RESULT_FAILED;

        if (extension_loaded('imagick')) {
            $this->result = Systemcheck_Tests_Test::RESULT_OK;
        }
    }
}
