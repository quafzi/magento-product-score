<?php
use Quafzi\ProductScore\Item\Consumer\Ram as RamConsumer;
use Quafzi\ProductScore\Item\Consumer\InvalidConfigException;
use PHPUnit\Framework\TestCase;

/**
 * Product Score File Consumer
 *
 * @author Thomas Birke <magento@netextreme.de>
 */

class RamTest extends TestCase
{
    public function testAddItem()
    {
        try {
            $expectedScores = ['foo' => 3, 'bar' => 8, 'baz' => 5];
            $consumer = new RamConsumer();
            $consumer->init(['prefix' => 'foobar']);
            $consumer->addItem('foo', $expectedScores['foo']);
            $consumer->addItem('bar', $expectedScores['bar']);
            $consumer->addItem('baz', $expectedScores['baz']);

            $read = $consumer->getResultIterator();

            $offset = 0;
            foreach ($read() as $row) {
                $this->assertEquals($expectedScores[$row['product']], $row['score']);
                unset($expectedScores[$row['product']]);
            }
            $this->assertEmpty($expectedScores);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function testAddItemData()
    {
        try {
            $consumer = new RamConsumer();
            $consumer->init(['temporary_fields' => ['bar', 'something'], 'prefix' => 'quz']);
            $consumer->addItemData('foo', 'bar', 3);
            $this->assertEquals(3, $consumer->getItemData('foo', 'bar'), 'Standard');
            $this->assertEquals('Standard', $consumer->getItemData('foo', 'something', 'Standard'));
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
