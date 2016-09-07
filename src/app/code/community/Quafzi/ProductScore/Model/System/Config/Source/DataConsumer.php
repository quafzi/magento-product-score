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
class Quafzi_ProductScore_Model_System_Config_Source_DataConsumer
{
    const CONSUMER_FILES = 'Files';
    const CONSUMER_RAM   = 'Ram';
    const CONSUMER_REDIS = 'Redis';
    /**
     * Returns array with product weight unit
     *
     * @return array    $return
     */
    public function toOptionArray()
    {
        $helper = Mage::helper('quafzi_productscore');
        return [
            [
                'value' => self::CONSUMER_FILES,
                'label' => $helper->__('temporary files')
            ],
            [
                'value' => self::CONSUMER_RAM,
                'label' => $helper->__('RAM')
            ],
            [
                'value' => self::CONSUMER_REDIS,
                'label' => $helper->__('Redis')
            ]
        ];
    }
}
