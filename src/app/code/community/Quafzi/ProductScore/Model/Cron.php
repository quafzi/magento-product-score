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
class Quafzi_ProductScore_Model_Cron
{
    public function fetch()
    {
        try {
            Mage::helper('quafzi_productscore/product')
                ->fetchCalculatedScores()
                ->mapConsecutiveScores()
                ->updateByManualScores();
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
