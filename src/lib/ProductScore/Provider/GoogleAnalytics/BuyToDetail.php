<?php
namespace Quafzi\ProductScore\Provider\GoogleAnalytics;

use Quafzi\ProductScore\Item\Consumer\ConsumerInterface as Consumer;
use Quafzi\ProductScore\Provider\ProviderInterface as Provider;
use Quafzi\ProductScore\Provider\FetchException;
use Quafzi\ProductScore\Provider\GoogleAnalytics\GoogleAnalyticsAbstract as GoogleAnalytics;

/**
 * Google Analytics Buy-to-Detail Product Score Provider
 *
 * @author Thomas Birke <magento@netextreme.de>
 */

class BuyToDetail extends GoogleAnalytics
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
        $this->consumer = $consumer;
        $this->fetchAnalyticsColumn('ga:buyToDetailRate', $config);

        return $this;
    }
}
