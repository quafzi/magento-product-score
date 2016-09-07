<?php
use Quafzi\ProductScore\Calculator;
use Quafzi\ProductScore\Calculator\InvalidConsumerException;
use Quafzi\ProductScore\Calculator\InvalidProviderException;
use Quafzi\ProductScore\Calculator\MissingConsumerException;
use PHPUnit\Framework\TestCase;

/**
 * Calculator test
 *
 * @author Thomas Birke <magento@netextreme.de>
 */

class CalculatorTest extends TestCase
{
    public function testAddInvalidProvider()
    {
        $calculator = new Calculator();
        $this->expectException(InvalidProviderException::class);
        $this->expectExceptionMessage('Invalid provider name "Google Analytics". Please specify provider class name below namespace Quafzi\\ProductScore\\Provider.');
        $calculator->addProvider('Google Analytics', 20);
        $this->assertEmpty($calculator->getProviders());
    }

    public function testAddNonExistingProvider()
    {
        $calculator = new Calculator();
        $this->expectException(InvalidProviderException::class);
        $this->expectExceptionMessage('Provider "Dev\\Null" does not exist.');
        $calculator->addProvider('Dev\\Null', 20);
        $this->assertEmpty($calculator->getProviders());
    }

    public function testAddProvider()
    {
        $calculator = new Calculator();

        $provider = $this->getMockBuilder('Quafzi\\ProductScore\\Provider\\Foo\\Bar')->getMock();
        $calculator->addProvider('Foo\\Bar', 20, ['foo' => 'bar']);

        $provider = $this->getMockBuilder('Quafzi\\ProductScore\\Provider\\Another\\Provider')->getMock();
        $calculator->addProvider('Another\\Provider', 4, ['a' => '1', 'b' => 2]);

        $providers = $calculator->getProviders();
        $this->assertEquals(2, count($providers), 'Expected both added providers to be registered.');
        $this->assertInstanceOf('Quafzi\\ProductScore\\Provider\\Foo\\Bar', $providers[0]['instance']);
        $this->assertEquals(20, $providers[0]['weight']);
        $this->assertEquals(['foo' => 'bar'], $providers[0]['config']);
        $this->assertInstanceOf('Quafzi\\ProductScore\\Provider\\Another\\Provider', $providers[1]['instance']);
        $this->assertEquals(4, $providers[1]['weight']);
        $this->assertEquals(['a' => '1', 'b' => 2], $providers[1]['config']);
    }

    public function testAddInvalidConsumer()
    {
        $calculator = new Calculator();
        $this->expectException(InvalidConsumerException::class);
        $this->expectExceptionMessage('Invalid consumer name "Dev Null". Please specify consumer class name below namespace Quafzi\\ProductScore\\Item\\Consumer.');
        $calculator->setConsumer('Dev Null', ['foo' => 'bar']);
    }

    public function testAddNonExistingConsumer()
    {
        $calculator = new Calculator();
        $this->expectException(InvalidConsumerException::class);
        $this->expectExceptionMessage('Consumer "Dev\\Null" does not exist.');
        $calculator->setConsumer('Dev\\Null', ['foo' => 'bar']);
    }

    public function testAddConsumer()
    {
        $calculator = new Calculator();

        $this->getMockBuilder('Quafzi\\ProductScore\\Item\\Consumer\\Foo\\Bar')->getMock();
        $calculator->setConsumer('Foo\\Bar', ['foo' => 'bar']);
    }

    public function testRunWithoutConsumer()
    {
        $calculator = new Calculator();
        $this->expectException(MissingConsumerException::class);
        $this->expectExceptionMessage('You need to set an item consumer before running the calculator.');
        $calculator->run();
    }

    public function testRunWithoutProvidersAndProducts()
    {
        $calculator = new Calculator();
        $consumer = $this->getMockBuilder('Quafzi\\ProductScore\\Item\\Consumer\\Foo\\Bar')
            ->setMethods(['addItem'])
            ->getMock();
        $consumer->expects($this->never())
            ->method('addItem');
        $calculator->setConsumer('Foo\\Bar');
        $calculator->run();
    }

    public function testRun()
    {
        $calculator = new Calculator();

        $productIdentifiers = [123 => 'abc', 456 => 'def'];
        $calculator->setProductIdentifiers($productIdentifiers);

        $consumer = $this->getMockBuilder('Quafzi\\ProductScore\\Item\\Consumer\\ConsumerInterface')->getMock();
        $calculator->setConsumer($consumer);

        $provider = $this->getMockBuilder('Quafzi\\ProductScore\\Provider\\ProviderInterface')
            ->setMethods(['setScoreRange', 'setProductIdentifiers', 'fetch', 'getResultIterator'])
            ->getMock();
        $provider->expects($this->once())
            ->method('setScoreRange')
            ->with($this->equalTo(0), $this->equalTo(100))
            ->will($this->returnSelf());
        $provider->expects($this->once())
            ->method('setProductIdentifiers')
            ->with($this->equalTo($productIdentifiers))
            ->will($this->returnSelf());
        $provider->expects($this->once())
            ->method('fetch')
            ->with($this->equalTo($consumer), $this->equalTo(['foo' => 'bar']))
            ->will($this->returnSelf());
        $calculator->addProvider($provider, 20, ['foo' => 'bar']);

        $calculator->run();
    }
}
