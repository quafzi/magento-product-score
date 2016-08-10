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
    public function testConstructorWithoutConfig()
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('You need to specify a "path" to the file to be written.');
        new FileConsumer([]);
    }

    public function testConstructorWithNonWritableFile()
    {
        $this->expectException(InvalidConfigException::class);
        $this->expectExceptionMessage('File at "/dev/full/foobar.csv" is not writable.');
        new FileConsumer(['path' => '/dev/full/foobar.csv']);
    }
}
