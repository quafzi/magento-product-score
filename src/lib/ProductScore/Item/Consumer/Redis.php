<?php
namespace Quafzi\ProductScore\Item\Consumer;

require_once(dirname(__FILE__) . '/RedisConsumer/predis_1.1.2-dev.phar');

use Predis\Collection\Iterator;

/**
 * Product Score Redis Consumer
 *
 * @author Thomas Birke <magento@netextreme.de>
 */

class Redis implements ConsumerInterface
{
    protected $config;
    protected $client;
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
        if (!isset($this->config['scheme']) || !($this->config['scheme'])) {
            $this->config['scheme'] = 'tcp';
        }
        if (!isset($this->config['host']) || !($this->config['host'])) {
            $this->config['host'] = '127.0.0.1';
        }
        if (!isset($this->config['port']) || !($this->config['port'])) {
            $this->config['port'] = '6379';
        }
        if (!isset($this->config['database']) || !($this->config['database'])) {
            $this->config['database'] = null;
        }
        $this->client = new \Predis\Client([
            'scheme' => $this->config['scheme'],
            'host' => $this->config['host'],
            'port' => $this->config['port'],
            'database' => $this->config['database']
        ]);
        if (isset($this->config['temporary_fields'])) {
            $this->fields = $this->config['temporary_fields'];
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
        $field = $this->config['prefix'] . '/' . $field;
        $value = $this->client->get($identifier);
        if ($value) {
            $value = json_decode($value, $assoc=true);
        } else {
            $value = [];
        }
        $value[$field] = $fieldValue;
        $this->client->set($identifier, json_encode($value));
    }

    public function getResultIterator()
    {
        return new Iterator\HashKey($this->client, 'predis:hash');
    }

    public function getItemData($identifier, $field, $default=null)
    {
        $field = $this->config['prefix'] . '/' . $field;
        $value = $this->client->get($identifier);
        if ($value) {
            $value = json_decode($value, $assoc=true);
            return isset($value[$field]) ? $value[$field] : $default;
        }
        return $default;
    }
}
