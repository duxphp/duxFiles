<?php

/**
 * 上传类
 */

namespace dux;

class Files {

    /**
     * 上传配置
     * @var array
     */
    private $config = [
        'type' => 'Local',
        'maxSize' => 1048576, //上传的文件大小限制 默认10M
        'allowExts' => [], //允许的文件后缀
        'savePath' => '', //保存路径
        'saveRule' => 'md5_file', //命名规则
    ];

    /**
     * 默认驱动
     * @var string
     */
    private $driver = 'Local';

    /**
     * 文件驱动
     * @var object
     */
    private $object = null;

    /**
     * 错误信息
     * @var string
     */
    private $error = '';

    /**
     * 实例化
     * @param array $config
     */
    public function __construct($config = []) {
        $this->driver = $config['type'] ? $config['type'] : [];
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 保存文件
     * @param $file
     * @param $name
     * @param bool $delete
     * @return mixed
     * @throws \Exception
     */
    public function save($file, $name, $delete = false) {
        $data = null;
        if (is_string($file) && is_file($file)) {
            throw new \Exception("The file does not exist!");
            $file = file_get_contents($file);
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->buffer($file);

        $ext = pathinfo($name, PATHINFO_EXTENSION);
        if (empty($ext)) {
            $ext = (new \Mimey\MimeTypes())->getExtension($mime);
        }

        $name = call_user_func($this->config['saveRule'], $name);
        $name = $name . '.' . $ext;
        $pathInfo = pathinfo($name);
        if (!$this->getObj()->checkPath($pathInfo['dirname'])) {
            throw new \Exception("Do not use the file directory!");
        }
        $dir = trim(str_replace('\\', '/', $this->config['savePath']), '/') . '/' . trim($pathInfo['dirname'], '/');
        $info = $this->getObj()->save($data, $dir, $pathInfo['basename'], $mime);
        if ($delete) {
            @unlink($file['tmp_name']);
        }
        return $info;
    }

    public function getObj() {
        if ($this->object) {
            return $this->object;
        }
        $class = "\\dux\\files\\{$this->driver}Driver";
        $this->object = new $class($this->config);
        return $this->object;
    }

    public function getError() {
        return $this->error;
    }

}