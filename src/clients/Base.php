<?php

namespace EtcdPHP\clients;

use GuzzleHttp\Client as gzClient;
use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\ResponseInterface;

class Base
{
    /**
     * @var gzClient
     */
    public $http;

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
    private static $_instance = [];

    public static function instance($server = 'http://localhost:2379', $apiVersion = 'v2')
    {
        $class = get_called_class();
        if (!@self::$_instance[$class] instanceof $class) {
            self::$_instance[$class] = new $class;
        }
        self::$_instance[$class]->server = $server;
        self::$_instance[$class]->apiVersion = $apiVersion;
        self::$_instance[$class]->http = new gzClient([
            'base_uri' => self::$_instance[$class]->server
        ]);
        return self::$_instance[$class];
    }

    /**
     * 解析返回的数据.
     * @param ResponseInterface $response
     * @return Response
     *
     * @throws
     */
    protected function _result(ResponseInterface $response)
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
        $path = "/{$this->apiVersion}/keys{$key}";
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
    protected function _get($key, $params = [])
    {
        try {
            $response = $this->http->get($this->getKeyPath($key), ['query' => $params]);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        }
        $body = $this->_result($response);
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
    protected function _set($key, $value = null, $dir = null, $ttl = null, $cond = [])
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
        $body = $this->_result($response);
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
    protected function _delete($key, $dir = null, $recursive = false)
    {
        $cond = [];
        if ($dir) {
            $cond['dir'] = 'true';
        }
        if ($recursive) {
            $cond['recursive'] = 'true';
        }

        try {
            $response = $this->http->delete($this->getKeyPath($key), [
                'query' => $cond,
            ]);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        }
        $body = $this->_result($response);
        return $body;
    }

}

