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
class Quafzi_ProductScore_Model_Calculator
{
    protected static $calculator;

    protected function getCalculator()
    {
        if (!self::$calculator) {
            Mage::helper('quafzi_productscore')
                ->loadLibraries()
                ->registerAutoloader()
                ;
            self::$calculator = new \Quafzi\ProductScore\Calculator();
        }
        return self::$calculator;
    }

    protected function getProductIdentifiers()
    {
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('catalog/product');
        return array_map(function ($item) {
            return $item['sku'];
        }, $readConnection->fetchAssoc('SELECT entity_id, sku FROM ' . $table));
    }

    public function run()
    {
        $calculator = $this->getCalculator();
        $consumer = Mage::getStoreConfig('quafzi_productscore/system/consumer');
        $consumerConfig = [];
        foreach (Mage::getStoreConfig('quafzi_productscore/system') as $key => $value) {
            if (0 === strpos($key, strtolower($consumer))) {
                $consumerConfig[substr($key, strlen($consumer)+1)] = $value;
            }
        }
        foreach (Mage::getStoreConfig('quafzi_productscore') as $provider => $config) {
            if ('system' === $provider) {
                // skip system config
                continue;
            }
            if ($config['enabled'] === '0') {
                continue;
            }
            $providerConfig = [];
            $weights = [];
            foreach ($config as $key => $value) {
                if (0 === strpos($key, 'weight_')) {
                    $weights[ucfirst($provider) . '\\' . ucfirst(substr($key, strlen('weight_')))] = $value;
                } else {
                    $providerConfig[$key] = $value;
                }
            }
            foreach ($weights as $providerCode => $weight) {
                $calculator->addProvider($providerCode, $weight, $providerConfig);
            }
        }
        return $calculator->setProductIdentifiers($this->getProductIdentifiers())
            ->setConsumer($consumer, $consumerConfig)
            ->run();
    }
}
