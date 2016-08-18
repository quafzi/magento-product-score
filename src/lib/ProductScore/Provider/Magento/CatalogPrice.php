<?php
namespace Quafzi\ProductScore\Provider\Magento;

use Quafzi\ProductScore\Item\Consumer\ConsumerInterface as Consumer;
use Quafzi\ProductScore\Provider\Magento\PriceAbstract as Provider;
use Quafzi\ProductScore\Provider\FetchException;
use \Mage;

/**
 * Magento Catalog Price Product Score Provider
 *
 * @author Thomas Birke <magento@netextreme.de>
 */

class CatalogPrice extends Provider
{
    /**
     * fetch score information
     *
     * @param Consumer $consumer Handler for fetched item score
     * @param array    $config   Provider configuration data
     *
     * @return $this
     */
    public function fetch(Consumer $consumer, array $config)
    {
        $storeId = $config['store_id'] ?? 1;
        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addStoreFilter($storeId)
            ->setVisibility(Mage::getSingleton('catalog/product_visibility')->getVisibleInSiteIds())
            ->addAttributeToSort('price', \Mage_Catalog_Model_Resource_Product_Collection::SORT_ORDER_DESC) ;
        $results = [];
        foreach ($collection as $product) {
            $results[$product->getId()] = $product->getPrice();
        }

        $this->handlePriceRows($consumer, $results);
    }
}
