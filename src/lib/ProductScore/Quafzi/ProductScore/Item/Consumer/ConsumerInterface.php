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
     * init consumer, optionally update (some?) configuration options
     *
     * @param array $config Configuration options
     * @throws InvalidConfigException
     */
    public function init(array $config=[]);

    /**
     * add item
     *
     * @param string $itemIdentifier Item identifier (sku or id)
     * @param int    $score          Item Score
     * @throws InvalidConfigException
     *
     * @return $this
     */
    public function addItem($identifier, $score);

    /**
     * get score
     *
     * @param string $itemIdentifier Item identifier (sku or id)
     * @param string $default        Default Value
     *
     * @return string
     */
    public function getItem($identifier, $default=null);

    /**
     * add temporary item data
     *
     * @param string $itemIdentifier Item identifier (sku or id)
     * @param string $field          Field
     * @param string $value          Value
     * @throws NoSuchFieldException
     *
     * @return $this
     */
    public function addItemData($identifier, $field, $value);

    /**
     * add temporary item data
     *
     * @param string $itemIdentifier Item identifier (sku or id)
     * @param string $field          Field
     * @param string $default          Default Value
     * @throws NoSuchFieldException
     *
     * @return string
     */
    public function getItemData($identifier, $field, $default=null);

    /**
     * get result iterator
     *
     * @return Generator
     */
    public function getResultIterator();
}
