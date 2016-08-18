<?php
use Quafzi\ProductScore\Provider\Magento\PriceOrdered as Provider;
use PHPUnit\Framework\TestCase;

/**
 * Magento Price Ordered Product Score Provider
 *
 * @author Thomas Birke <magento@netextreme.de>
 */

class PriceOrderedTest extends TestCase
{
    public function testHandleRows()
    {
        $consumer = $this->getMockBuilder('Quafzi\\ProductScore\\Item\\Consumer\\ConsumerInterface')
            ->setMethods(['addItem', 'init', '__construct', 'addItemData', 'getItem', 'getItemData', 'getResultIterator'])
            ->getMock();
        $consumer->expects($this->at(0))
            ->method('addItem')
            ->with($this->equalTo(3010), $this->equalTo(0.0, 0.001));
        $consumer->expects($this->at(1))
            ->method('addItem')
            ->with($this->equalTo(2345), $this->equalTo(67.9776, 0.001));
        $consumer->expects($this->at(2))
            ->method('addItem')
            ->with($this->equalTo(1111), $this->equalTo(67.9776, 0.001));
        $consumer->expects($this->at(3))
            ->method('addItem')
            ->with($this->equalTo(2350), $this->equalTo(84.8286, 0.001));
        $consumer->expects($this->at(4))
            ->method('addItem')
            ->with($this->equalTo(1000), $this->equalTo(86.0439, 0.001));

        $provider = new Provider([]);
        $provider->setScoreRange(0, 100);
        $provider->handlePriceRows($consumer, [
            3010 => 732.3,
            2345 => 234.5,
            1111 => 234.5,
            2350 => 111.1,
            1000 => 102.2,
        ]);
    }
}
