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
        'max_size' => 1048576, //保存文件大小限制 默认10M
        'allow_exts' => [], //允许的文件后缀
        'save_rule' => 'md5', //命名规则
    ];

    private $driverConfig = [
        'type' => 'Local',
        'save_path' => '',
    ];

    /**
     * 文件驱动
     * @var object
     */
    private $object = null;

    /**
     * 实例化
     * @param array $config 上传配置
     * @param array $driverConfig 驱动配置
     */
    public function __construct(array $config = [], array $driverConfig = []) {
        $driverConfig['url'] = trim(str_replace('\\', '/', $config['url']), '/');
        $driverConfig['domain'] = trim(str_replace('\\', '/', $config['domain']), '/');
        $this->driverConfig = array_merge($this->driverConfig, $driverConfig);
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 保存文件
     * @param $file 文件流或地址
     * @param $name 保存文件名
     * @param bool $verify 强验证格式
     * @return string 文件路径
     * @throws \Exception
     */
    public function save($file, string $name, bool $verify = false) {
        if (is_string($file)) {
            $file = fopen($file, 'r');
            if (!$file) {
                throw new \Exception("The file does not exist!");
            }
        }
        $name = str_replace('\\', '/', $name);
        if (!$verify) {
            $ext = pathinfo($name, PATHINFO_EXTENSION);
        }
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $content = stream_get_contents($file);
        rewind($file);
        $size = strlen($content);
        $mime = $finfo->buffer($content);
        if (empty($ext)) {
            $ext = (new \Mimey\MimeTypes())->getExtension($mime);
        }
        $ext = strtolower($ext);
        if ($this->config['allow_exts'] && !in_array($ext, $this->config['allow_exts'])) {
            throw new \Exception("Save the format is not supported!");
        }
        $pathInfo = pathinfo($name);
        $dir = trim(trim($pathInfo['dirname'], '.'), '/');
        $dir = $dir ? "/{$dir}/" : '/';
        if (!$this->getObj()->checkPath($dir)) {
            throw new \Exception("Do not use the file directory!");
        }
        $name = $pathInfo['filename'];
        $fun = $this->config['save_rule'];
        if ($fun) {
            $name = call_user_func($fun, $content);
        }
        $name = $name . '.' . $ext;
        $info = $this->getObj()->save($file, [
            'dir' => $dir,
            'name' => $name,
            'size' => $size,
            'mime' => $mime
        ]);
        @fclose($file);
        return $info;
    }

    /**
     * 删除文件
     * @param $name 文件名
     * @return mixed
     */
    public function del(string $name) {
        $name = trim(str_replace('\\', '/', $name), '/');
        $name = '/' . $name;
        return $this->getObj()->del($name);
    }

    /**
     * 驱动对象
     * @return object
     */
    public function getObj() {
        if ($this->object) {
            return $this->object;
        }
        $driver = ucfirst($this->driverConfig['type']);
        $class = "\\dux\\files\\{$driver}Driver";
        $this->object = new $class($this->driverConfig);
        return $this->object;
    }

}