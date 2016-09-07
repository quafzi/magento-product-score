<?php
namespace Quafzi\ProductScore\Provider\Magento;

use Quafzi\ProductScore\Item\Consumer\ConsumerInterface as Consumer;
use Quafzi\ProductScore\Provider\ProviderAbstract as Provider;
use Quafzi\ProductScore\Provider\FetchException;
use \Mage;

/**
 * Magento Ordered Amount Product Score Provider
 *
 * @author Thomas Birke <magento@netextreme.de>
 */

class Ordered extends Provider
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
SELECT product_id, SUM(qty_ordered - qty_refunded) AS ordered
FROM $table
WHERE created_at < DATE_SUB(NOW(), INTERVAL 3 MONTH)
GROUP BY product_id
ORDER BY ordered DESC
SQL;
        $result = array_map(function ($item) {
            return $item['ordered'];
        }, $readConnection->fetchAssoc($query));

        $factor = null;
        foreach ($result as $productId => $ordered) {
            if (is_null($factor)) {
                $factor = $this->maxScore/$ordered;
            }
            $consumer->addItem($productId, $ordered*$factor);
        }
    }
}
