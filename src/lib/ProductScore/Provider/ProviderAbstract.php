<?php
namespace Quafzi\ProductScore\Provider;

use Quafzi\ProductScore\Item\Consumer\ConsumerInterface as Consumer;
use Quafzi\ProductScore\Provider\ProviderInterface as Provider;
use Quafzi\ProductScore\Provider\FetchException;

/**
 * Abstract Product Score Provider
 *
 * @author Thomas Birke <magento@netextreme.de>
 */

abstract class ProviderAbstract implements Provider
{
    protected $minScore;
    protected $maxScore;
    protected $productIdentifiers;
    protected $consumer;

    /**
     * set score range
     *
     * @param float $min Lowest Score
     * @param float $max Highest Score
     *
     * @return $this
     */
    public function setScoreRange($min, $max)
    {
        $this->minScore = $min;
        $this->maxScore = $max;

        return $this;
    }

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
    public function setProductIdentifiers(array $productIdentifiers)
    {
        $this->productIdentifiers = $productIdentifiers;

        return $this;
    }
}
