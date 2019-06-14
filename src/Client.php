<?php

namespace EtcdPHP;

use EtcdPHP\v2\ClientV2;
use GuzzleHttp\Client as gzClient;
use Psr\Http\Message\ResponseInterface;

class Client
{
    /**
     * @var gzClient
     */
    public $http;

    /**
     * @var string etcdEndpoints
     */
    public $endpoints;

    /**
     * @var string etcdApiVersion
     */
    public $apiVersion;

    /**
     * @var self
     */
    private static $_instance;

    /**
     * @param string $endpoints
     * @param string $apiVersion
     * @param array $options
     *
     * @return ClientInterface
     */
    public static function instance($endpoints = 'http://localhost:2379', $apiVersion = 'v2', $options = [])
    {
        switch ($apiVersion) {
            case "v3":
                die("ClientV3 还没实现");
            case "v2":
            default:
                 self::$_instance = new ClientV2();
                 break;
        }
        self::$_instance->endpoints = $endpoints;
        self::$_instance->apiVersion = $apiVersion;
        self::$_instance->options = $options;
        self::$_instance->http = new gzClient([
            'base_uri' => self::$_instance->endpoints
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

}

