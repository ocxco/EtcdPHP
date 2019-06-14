<?php

namespace EtcdPHP\v2;

use EtcdPHP\Client;
use EtcdPHP\ClientInterface;
use EtcdPHP\Response;
use GuzzleHttp\Exception\BadResponseException;

class ClientV2 extends Client implements ClientInterface
{

    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;

    /**
     * 根据key获取Api路径.
     * @param $key
     * @return string
     */
    private function getKeyPath($key)
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
    private function _get($key, $params = [])
    {
        $data = [
            'query' => $params,
        ];
        if ($this->username && $this->password) {
            $data['headers'] =  [
                'Authorization' => 'Basic ' . base64_encode("{$this->username}:{$this->password}")
            ];
        }
        try {
            $response = $this->http->get($this->getKeyPath($key), $data);
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
    private function _set($key, $value = null, $dir = null, $ttl = null, $cond = [])
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
        $data = [
            'query' => $cond,
            'form_params' => $data,
        ];
        if ($this->username && $this->password) {
            $data['headers'] =  [
                'Authorization' => 'Basic ' . base64_encode("{$this->username}:{$this->password}")
            ];
        }
        try {
            $response = $this->http->put($this->getKeyPath($key), $data);
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
    private function _delete($key, $dir = null, $recursive = false)
    {
        $cond = [];
        if ($dir) {
            $cond['dir'] = 'true';
        }
        if ($recursive) {
            $cond['recursive'] = 'true';
        }

        $data = [
            'query' => $cond,
        ];
        if ($this->username && $this->password) {
            $data['headers'] =  [
                'Authorization' => 'Basic ' . base64_encode("{$this->username}:{$this->password}")
            ];
        }
        try {
            $response = $this->http->delete($this->getKeyPath($key), $data);
        } catch (BadResponseException $e) {
            $response = $e->getResponse();
        }
        $body = $this->_result($response);
        return $body;
    }

    /**
     * 用户认证.
     * @param $username
     * @param $password
     * @return mixed|void
     */
    public function auth($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * 启用权限验证,
     * @return mixed
     * @throws
     */
    public function authEnable()
    {
        $data = [];
        if ($this->username && $this->password) {
            $data['headers'] =  [
                'Authorization' => 'Basic ' . base64_encode("{$this->username}:{$this->password}")
            ];
        }
        try {
            $res = $this->http->put('/v2/auth/enable', $data);
        } catch (BadResponseException $e) {
            $res = $e->getResponse();
        }
        if ($res->getStatusCode() == 200 && $res->getBody()->read(200) == "") {
            // 启用禁用成功时返回数据为空字符串.MDZZ
            $res = Response::instance();
            $res->setAttributes(['msg' => 'Auth Enabled']);
            return $res;
        }
        return $this->_result($res);
    }

    /**
     * 关闭权限验证.
     * @return Response|mixed
     * @throws \Exception
     */
    public function authDisable()
    {
        $data = [];
        if ($this->username && $this->password) {
            $data['headers'] =  [
                'Authorization' => 'Basic ' . base64_encode("{$this->username}:{$this->password}")
            ];
        }
        try {
            $res = $this->http->delete('/v2/auth/enable', $data);
        } catch (BadResponseException $e) {
            $res = $e->getResponse();
        }
        if ($res->getStatusCode() == 200 && $res->getBody()->read(200) == "") {
            // 启用禁用成功时返回数据为空字符串.MDZZ
            $res = Response::instance();
            $res->setAttributes(['msg' => 'Auth Disabled']);
            return $res;
        }
        return $this->_result($res);
    }

    /**
     * 新增一个Key
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return Response
     * @throws \Exception
     */
    public function mk($key, $value, $ttl = 0)
    {
        $body = $this->_set($key, $value, null, $ttl, ['prevExist' => 'false']);
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
        $body = $this->_set($key, null, true, $ttl, [
            'prevExist' => 'false'
        ]);
        return $body;
    }

    /**
     * 删除一个key.
     * @param $key
     * @return Response
     */
    public function rm($key)
    {
        $body = $this->_delete($key);
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
        $body = $this->_delete($key, true, $recursive);
        return $body;
    }

    /**
     * @param $key
     * @param array $params
     * @return Response
     */
    public function get($key, $params = [])
    {
        return $this->_get($key, $params);
    }

    /**
     * 列表.
     * @param string $key 开始的路径.
     * @param bool $recursive 是否递归.
     * @return mixed
     */
    public function ls($key = '/', $recursive = false)
    {
        $query = [];
        if ($recursive === true) {
            $query['recursive'] = 'true';
        }
        $body = $this->get($key, $query);
        return $body;
    }

    /**
     * 设置一个key，不存在则创建，存在则更新.
     * @param $key
     * @param $value
     * @return Response
     * @throws \Exception
     */
    public function set($key, $value)
    {
        try {
            $res = $this->mk($key, $value);
        } catch (\Exception $e) {
            if (105 == $e->getCode()) {
                // key已经存在.
                $res = $this->update($key, $value);
            } else {
                throw $e;
            }
        }
        return $res;
    }

    /**
     * 新建或者更新一个dir
     * @param $key
     * @param $ttl
     * @return Response
     * @throws \Exception
     */
    public function setDir($key, $ttl)
    {
        try {
            $res = $this->mkdir($key, $ttl);
        } catch (\Exception $e) {
            if (105 == $e->getCode()) {
                $res = $this->updateDir($key, $ttl);
            } else {
                throw $e;
            }
        }
        return $res;
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
        $body = $this->_set($key, $value, null, $ttl, $condition);
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
        $cond = [
            'dir' => 'true',
            'prevExist' => 'true',
        ];
        $body = $this->_set($key, null, true, $ttl, $cond);
        return $body;
    }

    /**
     * 监听数据变更.
     *
     * @param $key
     * @param bool $recursive
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function watch($key, $recursive = true)
    {
        $keyPath = $this->getKeyPath($key);
        $params = [
            'wait' => true,
            'recursive' => $recursive ? 'true' : 'false',
        ];
        $waitUri = $keyPath . '?' . http_build_query($params);
        $response = $this->http->get($waitUri);
        $body = $this->_result($response);
        return $body;
    }

    /**
     * 生成异步监听的Uri.
     * 配置管理客户端需要使用.
     *
     * @param $key
     * @param $recursive
     * @return string
     */
    public function getWatchUri($key, $recursive)
    {
        $keyPath = $this->getKeyPath($key);
        $params = [
            'wait' => true,
            'recursive' => $recursive ? 'true' : 'false',
        ];
        $watchUri = $keyPath . '?' . http_build_query($params);
        return $watchUri;
    }

}

