<?php
namespace Quafzi\ProductScore\Item\Consumer;

/**
 * Product Score Ram Consumer
 *
 * @author Thomas Birke <magento@netextreme.de>
 */

class Ram implements ConsumerInterface
{
    protected $config;
    protected $data;
    protected $fields = [];

    public function __construct(array $config=[])
    {
        $this->config = $config;
    }

    public function init(array $configUpdate=[])
    {
        foreach ($configUpdate as $key=>$value) {
            $this->config[$key] = $value;
        }
        $prefixRegex = '/^[a-z0-9_]*$/';
        if (!isset($this->config['prefix']) || !($this->config['prefix'])) {
            $msg = 'You need to specify a prefix per provider (must match ' . $prefixRegex . ').';
            throw new InvalidConfigException($msg);
        }
        array_unshift($this->fields, 'score');
    }

    public function addItem($identifier, $score)
    {
        return $this->addItemData($identifier, 'score', $score);
    }

    public function getItem($identifier, $default=null)
    {
        return $this->getItemData($identifier, 'score', $default);
    }

    public function addItemData($identifier, $field, $fieldValue)
    {
        if (!isset($this->data[$identifier])) {
            $this->data[$identifier] = [];
        }
        if (!isset($this->data[$this->config['prefix']])) {
            $this->data[$this->config['prefix']] = [];
        }
        if (!isset($this->data[$this->config['prefix']][$identifier])) {
            $this->data[$this->config['prefix']][$identifier] = [];
        }
        $this->data[$this->config['prefix']][$identifier][$field] = $fieldValue;
    }

    public function getResultIterator()
    {
        return function () {
            foreach ($this->data[$this->config['prefix']] as $productId => $item) {
                yield [
                    'product' => $productId,
                    'score'   => $item['score']
                ];
            }
        };
    }

    public function getItemData($identifier, $field, $default=null)
    {
        if (!isset($this->data[$identifier])
            || !isset($this->data[$this->config['prefix']])
            || !isset($this->data[$this->config['prefix']][$identifier])
        ) {
            return $default;
        }
        return $this->data[$this->config['prefix']][$identifier][$field];
    }
}
