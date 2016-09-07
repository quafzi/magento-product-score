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
class Quafzi_ProductScore_Helper_Product extends Mage_Core_Helper_Abstract
{

    protected function _getCollectionItems(Varien_Data_Collection $collection)
    {
        $ids = $collection->getAllIds();

        foreach ($ids as $id) {
            yield $collection->getItemById($id);
        }
    }

    public function fetchCalculatedScores()
    {
        $getItemScore = Mage::getModel('quafzi_productscore/calculator')->run();
        $product = Mage::getModel('catalog/product');
        $productCollection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('score_calculated')
            ->addAttributeToSelect('score_manual')
            ->addAttributeToSelect('score')
            ->load();
        foreach ($getItemScore() as $index=>$item) {
            $product = $productCollection->getItemById($item['product']);
            if ($product->getScoreManual()) {
                if (($product->getScoreCalculated() - $product->getScoreManual()) > 0.01) {
                    $product->setScoreCalculated($item['score']);
                    $product->getResource()->saveAttribute($product, 'score_calculated');
                }
                continue;
            }
            if (abs($product->getScore() - $item['score']) > 0.01) {
                $product->setScore($item['score']);
                $product->getResource()->saveAttribute($product, 'score');
            }
            if (abs($product->getScoreCalculated() - $item['score']) > 0.01) {
                $product->setScoreCalculated($item['score']);
                $product->getResource()->saveAttribute($product, 'score_calculated');
            }
        }
        return $this;
    }

    public function updateByManualScores()
    {
        $products = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('score_calculated')
            ->addAttributeToSelect('score_manual')
            ->addAttributeToSelect('score')
            ->addAttributeToFilter([
                ['attribute' => 'score_manual', 'notnull' => true],
                ['attribute' => 'score_manual', 'gte' => 0]
            ])->addAttributeToFilter('score_calculated', ['gte' => 0]);
        foreach ($this->_getCollectionItems($products) as $product) {
            $product->setScore($product->getScoreManual());
            $product->getResource()->saveAttribute($product, 'score');
        }
        return $this;
    }
}
