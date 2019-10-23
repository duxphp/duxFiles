<?php

/**
 * 腾讯云Cos
 */

namespace dux\files;

class QiniuDriver implements FilesInterface {

    protected $config = [
        'access_key' => '',
        'secret_key' => '',
        'bucket' => '',
        'domain' => '',
        'url' => ''
    ];

    public function __construct($config = []) {
        $config['url'] = trim(str_replace('\\', '/', $config['url']), '/');
        $config['domain'] = trim(str_replace('\\', '/', $config['domain']), '/');
        $this->config = array_merge($this->config, $config);
    }

    public function checkPath($dir) {
        if (empty($this->config['access_key']) || empty($this->config['secret_key']) || empty($this->config['bucket']) || empty($this->config['domain']) || empty($this->config['url'])) {
            throw new \Exception("Qiniu configuration does not exist!");
        }
        return true;
    }

    public function save($data, $info) {
        $file = ltrim($info['dir'], '/') . $info['name'];
        $auth = $this->getSign();
        $response = (new \GuzzleHttp\Client())->request('POST', $this->config['url'], [
            'multipart' => [
                [
                    'name' => 'token',
                    'contents' => $auth
                ],
                [
                    'name' => 'key',
                    'contents' => $file
                ],
                [
                    'name' => 'file',
                    'contents' => $data
                ],
            ],
        ]);
        $reason = $response->getStatusCode();
        if ($reason <> 200) {
            throw new \Exception("Qiniu Upload failed!");
        }
        return $this->config['domain'] . '/' . $file;
    }

    public function del($file) {
        $auth = $this->getAuth('delete', $file);
        $response = (new \GuzzleHttp\Client())->request('POST', 'https://rs.qiniu.com' . $auth['path'], [
            'headers' => [
                'Authorization' => $auth['auth']
            ],
        ]);
        $reason = $response->getStatusCode();
        if ($reason <> 200) {
            throw new \Exception("Qiniu Delete failed!");
        }
        return true;
    }

    /**
     * 获取管理签名
     * @param $type
     * @param $file
     * @return array
     */
    private function getAuth($type, $file) {
        $file = ltrim($file, '/');
        $entry = $this->encode($this->config['bucket'] . ':' . $file);
        $path = '/' . $type . '/' . $entry;
        $sign = $this->encode(hash_hmac('sha1', $path . "\n", $this->config['secret_key'], true));
        $auth = 'QBox ' . $this->config['access_key'] . ':' . $sign;
        return [
            'auth' => $auth,
            'path' => $path
        ];
    }

    /**
     * 获取上传签名
     * @return string
     */
    private function getSign() {
        $time = time() + 1800;
        $data = ['scope' => $this->config['bucket'], 'deadline' => $time];
        $data = $this->encode(json_encode($data));
        return $this->sign($this->config['secret_key'], $this->config['access_key'], $data) . ':' . $data;
    }

    private function encode($str) {
        return str_replace(['+', '/'], ['-', '_'], base64_encode($str));
    }

    private function sign($sk, $ak, $data) {
        $sign = hash_hmac('sha1', $data, $sk, true);
        return $ak . ':' . $this->encode($sign);
    }
}