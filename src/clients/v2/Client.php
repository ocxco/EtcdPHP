<?php

namespace EtcdPHP\clients\v2;

class Client extends Base
{

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

