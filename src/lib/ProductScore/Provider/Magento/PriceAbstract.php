<?php
namespace Quafzi\ProductScore\Provider\Magento;

use Quafzi\ProductScore\Item\Consumer\ConsumerInterface as Consumer;
use Quafzi\ProductScore\Provider\ProviderAbstract as Provider;

/**
 * Magento Price Ordered Product Score Provider
 *
 * @author Thomas Birke <magento@netextreme.de>
 */

abstract class PriceAbstract extends Provider
{
    public function handlePriceRows(Consumer $consumer, $rows)
    {
        $factor = null;
        $maxPrice = null;
        foreach ($rows as $productId => $price) {
            if (is_null($factor)) {
                $maxPrice = $price;
                $factor = $this->maxScore/$price;
            }
            $consumer->addItem($productId, ($maxPrice-$price)*$factor);
        }
    }
}
