<?php
namespace Quafzi\ProductScore\Provider\Magento;

use Quafzi\ProductScore\Item\Consumer\ConsumerInterface as Consumer;
use Quafzi\ProductScore\Provider\Magento\PriceAbstract as Provider;
use Quafzi\ProductScore\Provider\FetchException;
use \Mage;

/**
 * Magento Price Ordered Product Score Provider
 *
 * @author Thomas Birke <magento@netextreme.de>
 */

class PriceOrdered extends Provider
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
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $table = $resource->getTableName('sales/order_item');
        $query = <<<SQL
SELECT product_id, SUM(price) AS price
FROM $table
WHERE created_at < DATE_SUB(NOW(), INTERVAL 3 MONTH)
GROUP BY sku
ORDER BY price DESC
SQL;
        $result = array_map(function ($item) {
            return $item['price'];
        }, $readConnection->fetchAssoc($query));

        $this->handlePriceRows($consumer, $result);
    }
}
