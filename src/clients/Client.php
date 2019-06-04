<?php

namespace EtcdPHP\clients;

use GuzzleHttp\Client as gzClient;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\ResponseInterface;

class Client
{
    /**
     * @var gzClient
     */
    public $http;

    /**
     * @var string storeRoot
     */
    public $root = '';

    /**
     * @var string etcdServer
     */
    public $server;

    /**
     * @var string etcdApiVersion
     */
    public $apiVersion;

    /**
     * @var self
     */
    private static $_instance;

    public static function instance($server = 'http://localhost:2379', $apiVersion = 'v2')
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }
        self::$_instance->server = $server;
        self::$_instance->apiVersion = $apiVersion;
        self::$_instance->http = new gzClient([
            'base_uri' => self::$_instance->server
        ]);
        return self::$_instance;
    }

    /**
     * 解析返回的数据.
     * @param ResponseInterface $response
     * @return Response
     *
     * @throws
     */
    private function result(ResponseInterface $response)
    {
        $body = json_decode($response->getBody(), true);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception("Response error to parse");
        }
        $resp = Response::instance();
        $resp->setAttributes($body);
        if ($resp->errorCode) {
            throw new \Exception($resp->message, $resp->errorCode);
        }
        return $resp;
    }

    /**
     * 根据key获取Api路径.
     * @param $key
     * @return string
     */
    protected function getKeyPath($key)
    {
        if (strpos($key, '/') !== 0) {
            $key = '/' . $key;
        }
        $path = "/{$this->apiVersion}/keys{$this->root}{$key}";
        return $path;
    }

    /**
     * 根据key获取value
     * @param $key
     * @param array $params
     * @throws
     *
     * @return Response
     */
    private function get($key, $params = [])
    {
        try {
            $response = $this->http->get($this->getKeyPath($key), ['query' => $params]);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        }
        $body = $this->result($response);
        return $body;
    }

    /**
     * 设置一个值.
     * @param $key
     * @param $value
     * @param $dir
     * @param null $ttl
     * @param array $cond
     * @return Response
     *
     * @throws
     */
    private function set($key, $value = null, $dir = null, $ttl = null, $cond = [])
    {
        $data = [];
        if ($value) {
            $data['value'] = $value;
        } elseif ($dir) {
            $data['dir'] = 'true';
        }
        if ($ttl) {
            $data['ttl'] = $ttl;
        }

        try {
            $response = $this->http->put($this->getKeyPath($key), [
                'query' => $cond,
                'form_params' => $data
            ]);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        }
        $body = $this->result($response);
        return $body;
    }

    /**
     * 删除一个key
     * @param $key
     * @param null $dir
     * @param bool $recursive
     * @return Response
     *
     * @throws
     */
    private function delete($key, $dir = null, $recursive = false)
    {
        $cond = [];
        if ($dir) {
            $data['dir'] = 'true';
        }
        if ($recursive) {
            $data['recursive'] = 'true';
        }

        try {
            $response = $this->http->delete($this->getKeyPath($key), [
                'query' => $cond,
            ]);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        }
        $body = $this->result($response);
        return $body;
    }

    /**
     * 设置存储根路径.
     * @param $root
     * @return $this
     */
    public function setRoot($root)
    {
        if (strpos($root, '/') !== 0) {
            $root = '/' . $root;
        }
        $this->root = rtrim($root, '/');
        return $this;
    }

    public function getNode($key, $params = [])
    {
        return $this->get($key, $params);
    }

    /**
     * 新增一个Key
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return Response
     * @throws \Exception
     */
    public function add($key, $value, $ttl = 0)
    {
        $body = $this->set($key, $value, null, $ttl, ['prevExist' => 'false']);
        return $body;
    }

    /**
     * 更新一个Key
     * @param $key
     * @param $value
     * @param $ttl
     * @param array $condition
     * @return Response
     */
    public function update($key, $value, $ttl = 0, $condition = [])
    {
        $condition['prevExist'] = 'true';
        $body = $this->set($key, $value, null, $ttl, $condition);
        return $body;
    }

    /**
     * 删除一个key.
     * @param $key
     * @return Response
     */
    public function rm($key)
    {
        $body = $this->delete($key);
        return $body;
    }

    /**
     * 新建一个文件夹.
     * @param $key
     * @param int $ttl
     * @return Response
     * @throws \Exception
     */
    public function mkdir($key, $ttl = 0)
    {
        $body = $this->set($key, null, true, $ttl, [
            'prevExist' => 'false'
        ]);
        return $body;
    }

    /**
     * 列表.
     * @param string $key 开始的路径.
     * @param bool $recursive 是否递归.
     * @return mixed
     */
    public function listDir($key = '/', $recursive = false)
    {
        $query = [];
        if ($recursive === true) {
            $query['recursive'] = 'true';
        }
        $body = $this->get($key, $query);
        return $body;
    }

    /**
     * 更新一个dir
     * @param $key
     * @param int $ttl
     * @return Response
     * @throws \Exception
     */
    public function updateDir($key, $ttl = 0)
    {
        if (!$ttl) {
            throw new \Exception("TTL is required", 204);
        }
        $cond = [
            'dir' => 'true',
            'prevExist' => 'true',
        ];
        $body = $this->set($key, null, true, $ttl, $cond);
        return $body;
    }

    /**
     * 删除dir.
     * @param $key
     * @param bool $recursive
     * @return Response
     */
    public function rmdir($key, $recursive = false)
    {
        $body = $this->delete($key, true, $recursive);
        return $body;
    }

}

