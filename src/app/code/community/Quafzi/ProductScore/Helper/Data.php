<?php
/**
 * Quafzi_ProductScore
 *
 * This file is part of the Quafzi_ProductScore extension.
 * Please do not edit or add to this file if you wish to upgrade it to newer
 * versions in the future.
 *
 * @category   Quafzi_ProductScore
 * @package    Quafzi_ProductScore
 * @author     Thomas Birke <magento@netextreme.de>
 * @copyright  ©2016 by Thomas Birke <magento@netextreme.de>
 * @license    OSL-3.0
 */
class Quafzi_ProductScore_Helper_Data extends Mage_Core_Helper_Data
{
    public function registerAutoloader()
    {
        spl_autoload_register(
            function($class) {
                $prefix = 'Quafzi\\ProductScore\\';
                $baseDir = Mage::getBaseDir('lib') . '/ProductScore/';
                $len = strlen($prefix);
                if (strncmp($prefix, $class, $len) !== 0) {
                    return;
                }
                $relativeClass = substr($class, $len);
                $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
                if (file_exists($file)) {
                    require $file;
                }
            }
        );
    }
}
