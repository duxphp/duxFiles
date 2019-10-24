<?php

/**
 * 上传驱动接口
 */

namespace dux\files;

Interface FilesInterface {

    /**
     * 构建函数
     * FilesInterface constructor.
     * @param $config
     */
    public function __construct(array $config);

    /**
     * 路径检查
     * @param string $dir 目录路径
     * @return mixed
     */
    public function checkPath(string $dir);


    /**
     * 保存文件
     * @param $data 文件流
     * @param string $info 文件信息
     * @return string 文件路径
     */
    public function save($data, array $info);


    /**
     * 删除文件
     * @param string $name 文件名
     * @return mixed
     */
    public function del(string $name);

}