<?php
use Quafzi\ProductScore\Item\Consumer\FileConsumer;
use Quafzi\ProductScore\Item\Consumer\InvalidConfigException;
use PHPUnit\Framework\TestCase;

/**
 * Product Score File Consumer
 *
 * @author Thomas Birke <magento@netextreme.de>
 */

class FileConsumerTest extends TestCase
{
    public function testInitWithoutConfig()
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('You need to specify a "path" to the file to be written.');
        $consumer = new FileConsumer();
        $consumer->init();
    }

    public function testInitWithNonWritableFile()
    {
        $path = '/dev/full/foobar_';
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('File at "' . $path . '_score" is not writable.');
        try {
            $consumer = new FileConsumer();
            $consumer->init(['path' => $path]);
        } finally {
            @unlink($path);
        }
    }

    public function testAddItem()
    {
        $path = tempnam(sys_get_temp_dir(), __CLASS__ . '_' . __FUNCTION__ . '_');
        try {
            $expectedScores = ['foo' => 3, 'bar' => 8, 'baz' => 5];
            $consumer = new FileConsumer();
            $consumer->init(['path' => $path]);
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
            echo ' (File written: ' . $path . '*)';
            throw $e;
        } finally {
            @unlink($path);
        }
    }

    public function testAddItemData()
    {
        $path = tempnam(sys_get_temp_dir(), __CLASS__ . '_' . __FUNCTION__ . '_');
        try {
            $consumer = new FileConsumer();
            $consumer->init(['path' => $path, 'temporary_fields' => ['bar', 'something']]);
            $consumer->addItemData('foo', 'bar', 3);
            $this->assertEquals(3, $consumer->getItemData('foo', 'bar'), 'Standard');
            $this->assertEquals('Standard', $consumer->getItemData('foo', 'something', 'Standard'));
            $this->assertEquals("", file_get_contents($path . '_something'));
            $this->assertEquals("foo,3\n", file_get_contents($path . '_bar'));
        } catch (\Exception $e) {
            echo ' (File written: ' . $path . '*)';
            throw $e;
        } finally {
            @unlink($path);
        }
    }
}
