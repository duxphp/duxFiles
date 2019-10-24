<?php

/**
 * 腾讯云Upyun
 */

namespace dux\files;

class UpyunDriver implements FilesInterface {

    protected $config = [
        'operator' => '',
        'password' => '',
        'bucket' => '',
        'domain' => '',
        'url' => ''
    ];

    public function __construct(array $config = []) {
        $config['password'] = md5($config['password']);
        $this->config = array_merge($this->config, $config);
    }

    public function checkPath(string $dir) {
        if (empty($this->config['operator']) || empty($this->config['password']) || empty($this->config['bucket']) || empty($this->config['domain']) || empty($this->config['url'])) {
            throw new \Exception("Upyun configuration does not exist!");
        }
        return true;
    }

    public function save($data, array $info) {
        $file = $info['dir'] . $info['name'];
        $uri = "/{$this->config['bucket']}{$file}";
        $date = gmdate('D, d M Y H:i:s \G\M\T');
        $response = (new \GuzzleHttp\Client())->request('PUT', $this->config['url'] . $uri, [
            'headers' => [
                'Date' => $date,
                'Authorization' => $this->getAuth('PUT', $uri, $date),
                'Content-Type' => $info['mime'],
                'Content-Length' => $info['size'],
            ],
            'body' => $data,
        ]);
        $reason = $response->getStatusCode();
        if ($reason <> 200) {
            throw new \Exception("Upyun Upload failed!");
        }
        return $this->config['domain'] . $file;
    }

    public function del(string $file) {
        $uri = "/{$this->config['bucket']}{$file}";
        $date = gmdate('D, d M Y H:i:s \G\M\T');
        $response = (new \GuzzleHttp\Client())->request('DELETE', $this->config['url'] . $uri, [
            'headers' => [
                'Date' => $date,
                'Authorization' => $this->getAuth('DELETE', $uri, $date),
            ]
        ]);
        $reason = $response->getStatusCode();
        if ($reason <> 200) {
            throw new \Exception("Upyun Delete failed!");
        }
        return true;
    }

    function getAuth($method, $uri, $date, $policy = null, $md5 = null) {
        $elems = [];
        foreach ([$method, $uri, $date, $policy, $md5] as $v) {
            if ($v) {
                $elems[] = $v;
            }
        }
        $value = implode('&', $elems);
        $sign = base64_encode(hash_hmac('sha1', $value, $this->config['password'], true));
        return 'UPYUN ' . $this->config['operator'] . ':' . $sign;
    }
}