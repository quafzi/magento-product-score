<?php
namespace Quafzi\ProductScore\Item\Consumer;

/**
 * Product Score File Consumer
 *
 * @author Thomas Birke <magento@netextreme.de>
 */

class FileConsumer implements ConsumerInterface
{
    protected $fileHandle;

    public function __construct(array $config=[])
    {
        if (!isset($config['path'])) {
            $msg = 'You need to specify a "path" to the file to be written.';
            throw new InvalidConfigException($msg);
        }
        $this->fileHandle = @fopen($config['path'], 'w');
        if (!$this->fileHandle) {
            $msg = 'File at "%s" is not writable.';
            throw new InvalidConfigException(sprintf($msg, (string)$config['path']));
        }
    }

    function addItem($identifier, $score)
    {
        fputcsv($this->fileHandle, [$identifier, $score]);
    }
}
