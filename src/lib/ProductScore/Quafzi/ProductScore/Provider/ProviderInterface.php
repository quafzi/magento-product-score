<?php
namespace Quafzi\ProductScore\Provider;

use Quafzi\ProductScore\Item\Consumer\ConsumerInterface as Consumer;

/**
 * Product Score Provider Interface
 *
 * @author Thomas Birke <magento@netextreme.de>
 */

interface ProviderInterface
{
    /**
     * set score range
     *
     * @param float $min Lowest Score
     * @param float $max Highest Score
     *
     * @return $this
     */
    public function setScoreRange($min, $max);

    /**
     * set identifiers of products to fetch scoring data for
     * This should be an associative array [ productId => sku, … ]
     *
     * Attention: You should submit your whole catalog here, because score will be normalized
     *
     * @param array $productIdentifiers Identifiers of products [ productId => sku, … ]
     *
     * @return $this
     */
    public function setProductIdentifiers(array $productIdentifiers);

    /**
     * fetch score information
     *
     * @param Consumer $consumer Handler for fetched item score
     * @param array    $config   Provider configuration data
     *
     * @return $this
     */
    public function fetch(Consumer $consumer, array $config);
}
