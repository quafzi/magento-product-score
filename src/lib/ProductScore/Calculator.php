<?php
namespace Quafzi\ProductScore;

use Quafzi\ProductScore\Calculator\InvalidConsumerException;
use Quafzi\ProductScore\Calculator\InvalidProviderException;
use Quafzi\ProductScore\Calculator\MissingConsumerException;
use Quafzi\ProductScore\Provider\ProviderInterface as Provider;
use Quafzi\ProductScore\Item\Consumer\ConsumerInterface as Consumer;

/**
 * Product Score Calculator
 *
 * @author Thomas Birke <magento@netextreme.de>
 */

class Calculator
{
    protected $providers = [];
    protected $consumer;
    protected $productIdentifiers = [];

    protected $minScore = 0;
    protected $maxScore = 100;

    /**
     * add a score provider
     *
     * @param string|Provider $provider Provider or provider name, which is a class in namespace Quafzi\ProductScore\Provider
     * @param int             $weight   Weight of the provider in resulting product score
     * @param array           $config   Provider config
     *
     * @return $this
     */
    public function addProvider($provider, $weight=null, array $config=null)
    {
        if (is_string($provider)) {
            if (!preg_match('/^[A-Z][A-Za-z0-9]*(\\\\[A-Z][A-Za-z0-9]*)*$/', $provider)) {
                $msg = 'Invalid provider name "%s". Please specify provider class name below namespace Quafzi\\ProductScore\\Provider.';
                throw new InvalidProviderException(sprintf($msg, $provider));
            }
            $className = __NAMESPACE__ . '\\Provider\\' . $provider;
            if (!class_exists($className)) {
                $msg = 'Provider "%s" does not exist.';
                throw new InvalidProviderException(sprintf($msg, $provider));
            }
            $provider = new $className($this->getConsumer(), $config);
        } elseif (false === $provider instanceof Provider) {
            $msg = '"%s" is no provider.';
            throw new InvalidProviderException(sprintf($msg, get_class($provider)));
        }
        $this->providers[] = [
            'instance' => $provider,
            'config'   => $config,
            'weight'   => $weight
        ];
        return $this;
    }

    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Set product identifiers
     *
     * @param array $productIdentifiers Identifiers of products [ productId => sku, … ]
     */
    public function setProductIdentifiers(array $productIdentifiers)
    {
        $this->productIdentifiers = $productIdentifiers;
        return $this;
    }

    /**
     * set customer
     *
     * @param string|Consumer $consumer Consumer or consumer name, which is a class in namespace Quafzi\ProductScore\Item\Consumer
     * @param array           $config   Consumer configuration
     */
    public function setConsumer($consumer, array $config=[])
    {
        if (is_string($consumer)) {
            if (!preg_match('/^[A-Z][A-Za-z0-9]*(\\\\[A-Z][A-Za-z0-9]*)*$/', $consumer)) {
                $msg = 'Invalid consumer name "%s". Please specify consumer class name below namespace Quafzi\\ProductScore\\Item\\Consumer.';
                throw new InvalidConsumerException(sprintf($msg, $consumer));
            }
            $className = __NAMESPACE__ . '\\Item\\Consumer\\' . $consumer;
            if (!class_exists($className)) {
                $msg = 'Consumer "%s" does not exist.';
                throw new InvalidConsumerException(sprintf($msg, $consumer));
            }
            $this->consumer = new $className($config);
        } else {
            if (false === $consumer instanceof Consumer) {
                $msg = '"%s" is no consumer.';
                throw new InvalidConsumerException(sprintf($msg, get_class($consumer)));
            }
            $this->consumer = $consumer;
        }
        return $this;
    }

    public function getConsumer()
    {
        return $this->consumer;
    }

    public function aggregateResults($providerResults)
    {
        $totalWeight = array_reduce($providerResults, function ($sum, $result) {
            return $sum + $result['weight'];
        }, 0);
        $this->consumer->init(['prefix' => 'final']);
        foreach (array_keys($this->productIdentifiers) as $productId) {
            $score = $this->minScore;
            foreach ($providerResults as $result) {
                $score += ($result['consumer']->getItem($productId, 0) * $result['weight'] / $totalWeight);
            }
            $this->consumer->addItem($productId, $score);
        }
    }

    public function run()
    {
        if (empty($this->consumer)) {
            $msg = 'You need to set an item consumer before running the calculator.';
            throw new MissingConsumerException($msg);
        }
        $results = [];
        foreach ($this->getProviders() as $prefix => $providerData) {
            $consumer = clone $this->consumer;
            $consumer->init(['prefix' => 'provider' . $prefix]);
            $providerData['instance']
                ->setScoreRange($this->minScore, $this->maxScore)
                ->setProductIdentifiers($this->productIdentifiers)
                ->fetch($consumer, $providerData['config']);
            $results[] = [
                'consumer' => $consumer,
                'weight' => $providerData['weight']
            ];
        }
        $this->aggregateResults($results);
        return $this->consumer->getResultIterator();
    }
}
