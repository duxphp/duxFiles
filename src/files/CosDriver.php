<?php

/**
 * 腾讯云Cos
 */

namespace dux\files;

class CosDriver implements FilesInterface {

    protected $config = [
        'secret_id' => '',
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
        if (empty($this->config['secret_id']) || empty($this->config['secret_key']) || empty($this->config['bucket']) || empty($this->config['domain']) || empty($this->config['url'])) {
            throw new \Exception("Cos configuration does not exist!");
        }
        return true;
    }

    public function save($data, $info) {
        $file = $info['dir'] . $info['name'];
        $headers = [
            'Content-Type' => $info['mime'],
            'Content-Length' => $info['size'],
        ];
        $auth = $this->getSign($file, 'PUT', [], $headers);
        $response = (new \GuzzleHttp\Client())->request('PUT', $this->config['url'] . $file, [
            'body' => $data,
            'headers' => array_merge($headers, [
                'Authorization' => $auth
            ])
        ]);
        $reason = $response->getStatusCode();
        if ($reason <> 200) {
            throw new \Exception("Oss Upload failed!");
        }
        return $this->config['domain'] . $file;
    }

    public function del($file) {
        $auth = $this->getSign($file, 'DELETE');
        $response = (new \GuzzleHttp\Client())->request('DELETE', $this->config['url'] . $file, [
            'headers' => [
                'Authorization' => $auth
            ]
        ]);
        $reason = $response->getStatusCode();
        if ($reason <> 204) {
            throw new \Exception("Oss Delete failed!");
        }
        return true;
    }

    private function getSign($name, $type, $query = [], $header = []) {
        $time = time();
        $expiredTime = $time + 1800;
        $keyTime = $time . ';' . $expiredTime;
        $signKey = hash_hmac("sha1", $keyTime, $this->config['secret_key']);
        $httpString = implode("\n", [strtolower($type), $name, $this->httpParameters($query), $this->httpParameters($header), '']);
        $stringToSign = implode("\n", ['sha1', $keyTime, sha1($httpString), '']);
        $signature = hash_hmac('sha1', $stringToSign, $signKey);
        $data = [];
        $data['q-sign-algorithm'] = 'sha1';
        $data['q-ak'] = $this->config['secret_id'];
        $data['q-sign-time'] = $keyTime;
        $data['q-key-time'] = $keyTime;
        $data['q-header-list'] = $this->urlParamList($header);
        $data['q-url-param-list'] = $this->urlParamList($query);
        $data['q-signature'] = $signature;
        $sign = [];
        foreach ($data as $key => $vo) {
            $sign[] = $key . '=' . $vo;
        }
        return implode('&', $sign);
    }

    private function urlParamList($data) {
        $list = array_keys($data);
        sort($list);
        $list = array_map(function ($vo) {
            return urlencode($vo);
        }, $list);
        return strtolower(implode(';', $list));
    }

    private function httpParameters($data) {
        $keys = array_keys($data);
        sort($keys);
        $data = array_merge(array_flip($keys), $data);
        $tmp = [];
        foreach ($data as $key => $vo) {
            $tmp[strtolower($key)] = $vo;
        }
        return http_build_query($tmp);
    }
}