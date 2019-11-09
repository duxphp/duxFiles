<?php

/**
 * 本地驱动
 */

namespace dux\files;

class LocalDriver implements FilesInterface {

    protected $config = [
        'domain' => '',
        'save_path' => ''
    ];

    public function __construct(array $config = []) {
        $config['save_path'] = str_replace('\\', '/', $config['save_path']);
        $config['save_path'] = rtrim($config['save_path']);
        $this->config = array_merge($this->config, $config);
    }

    public function checkPath(string $dir) {
        $dir = $this->config['save_path'] . $dir;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        if (!is_writable($dir)) {
            throw new \Exception("Storage directory without permission!");
        }
        return true;
    }

    public function save($data, array $info) {
        $absolutePach = $this->config['save_path'] . $info['dir'] . $info['name'];
        $relativePath = $info['dir'] . $info['name'];
        $file = fopen($absolutePach, "w+");
        if (!stream_copy_to_stream($data, $file)) {
            throw new \Exception("The file save failed!");
        }
        fclose($file);
        return $this->config['domain'] . $relativePath;
    }

    public function del(string $name) {
        $dir = $this->config['save_path'] . '/' . trim($name, '/');
        if (is_file($name)) {
            return @unlink($name);
        }
        return true;
    }
}
