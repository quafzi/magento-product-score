<?php
namespace Quafzi\ProductScore\Item\Consumer;

/**
 * Product Score File Consumer
 *
 * @author Thomas Birke <magento@netextreme.de>
 */

class Files implements ConsumerInterface
{
    protected $fileNames = [];

    protected $fileHandles = [];
    protected $accessModes = [];

    protected $fields = [];

    protected $config;

    public function __construct(array $config=[])
    {
        $this->config = $config;
    }

    public function init(array $configUpdate=[])
    {
        foreach ($configUpdate as $key=>$value) {
            $this->config[$key] = $value;
        }
        if (!isset($this->config['path']) || !($this->config['path'])) {
            $msg = 'You need to specify a "path" to the file to be written.';
            throw new InvalidConfigException($msg);
        }
        $prefixRegex = '/^[a-z0-9_]*$/';
        if (!isset($this->config['prefix']) || !($this->config['prefix'] || !preg_match($prefixRegex, $this->config['prefix']))) {
            $msg = 'You need to specify a prefix per provider (must match ' . $prefixRegex . ').';
            throw new InvalidConfigException($msg);
        }
        if (isset($this->config['temporary_fields'])) {
            $this->fields = $this->config['temporary_fields'];
        }
        array_unshift($this->fields, 'score');
        foreach ($this->fields as $field) {
            $this->fileNames[$field] = $this->config['path'] . '_' . $this->config['prefix']. '_' . $field;
            @touch($this->fileNames[$field]);
            if (!is_writable($this->fileNames[$field])) {
                $msg = 'File at "%s" is not writable.';
                throw new InvalidConfigException(sprintf($msg, $this->fileNames[$field]));
            }
        }
    }

    /**
     * open file in given mode, if it is not already opened
     *
     * @param string $field Field name
     * @param string $mode  File access mode (see fopen)
     *
     * @return Resource File handle
     */
    protected function open($field, $mode)
    {
        if (isset($this->fileHandles[$field])
            && is_resource($this->fileHandles[$field])
        ) {
            if (isset($this->accessModes[$field]) 
                && $this->accessModes[$field] == $mode
            ) {
                return $this->fileHandles[$field];
            }
            $this->close($field);
        }
        $this->fileHandles[$field] = fopen($this->fileNames[$field], $mode);
        $this->accessModes[$field] = $mode;
        return $this->fileHandles[$field];
    }

    /**
     * close file handle
     */
    protected function close($field)
    {
        fclose($this->fileHandles[$field]);
        $this->accessModes[$field] = null;
    }

    protected function checkFieldExists($field)
    {
        if (!in_array($field, $this->fields)) {
            $msg = 'You need to add "%s" to the list of temporary_fields before using it';
            throw new NoSuchFieldException(sprintf($msg, $field));
        }
    }

    public function addItem($identifier, $score)
    {
        return $this->addItemData($identifier, 'score', $score);
    }

    public function getItem($identifier, $default=null)
    {
        return $this->getItemData($identifier, 'score', $default);
    }

    public function addItemData($identifier, $field, $value)
    {
        $this->checkFieldExists($field);
        $file = $this->open($field, 'a');
        fputcsv($file, [$identifier, $value]);
    }

    protected function getFileIterator($field)
    {
        $file = $this->open($field, 'r');
        return function () use ($file, $field) {
            while (($data = fgetcsv($file)) !== false) {
                yield ['product' => $data[0], $field => $data[1]];
            }
        };
    }

    public function getItemData($identifier, $field, $default=null)
    {
        $this->checkFieldExists($field);
        $read = $this->getFileIterator($field);
        foreach ($read() as $row) {
            if ($row['product'] == $identifier) {
                return $row[$field];
            }
        }
        return $default;
    }

    public function getResultIterator()
    {
        return $this->getFileIterator('score');
    }
}
