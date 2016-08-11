<?php
namespace Quafzi\ProductScore\Item\Consumer;

/**
 * Product Score Consumer Interface
 *
 * @author Thomas Birke <magento@netextreme.de>
 */

interface ConsumerInterface
{
    /**
     * constructor may require configuration options
     *
     * @param array $config Configuration options
     * @throws InvalidConfigException
     */
    public function __construct(array $config=[]);

    /**
     * add item
     *
     * @param string $itemIdentifier Item identifier (sku or id)
     * @param int    $score          Item Score
     *
     * @return $this
     */
    public function addItem($identifier, $score);
}
