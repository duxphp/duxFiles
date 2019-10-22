<?php

/**
 * 本地上传驱动
 */

namespace dux\files;

class LocalDriver implements FilesInterface {

    protected $config = [];

    public function __construct($config) {
        $this->config = $config;
    }

    public function checkPath($dir) {
        if(!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        if (!is_writable($dir)) {
            throw new \Exception("Storage directory without permission!");
        }
        return true;
    }
}
