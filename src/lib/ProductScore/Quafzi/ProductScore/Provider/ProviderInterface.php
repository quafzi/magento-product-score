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
     * @param int $min Lowest Score
     * @param int $max Highest Score
     *
     * @return $this
     */
    function setScoreRange(int $min, int $max);

    /**
     * fetch score information
     *
     * @param Consumer $consumer Handler for fetched item score
     * @param array    $config   Provider configuration data
     *
     * @return $this
     */
    function fetch(Consumer $consumer, array $config);
}
