<?php

namespace EtcdPHP;

interface ClientInterface
{
    /**
     * 新增一个Key
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     * @return Response
     * @throws \Exception
     */
    public function mk($key, $value, $ttl);

    /**
     * 新建一个文件夹.
     * @param $key
     * @param int $ttl
     * @return Response
     * @throws \Exception
     */
    public function mkdir($key, $ttl = 0);

    /**
     * 删除一个key.
     * @param $key
     * @return Response
     */
    public function rm($key);

    /**
     * 删除dir.
     * @param $key
     * @param bool $recursive
     * @return Response
     */
    public function rmdir($key, $recursive = false);

    /**
     * @param $key
     * @param array $params
     * @return Response
     */
    public function get($key, $params = []);

    /**
     * 列表.
     * @param string $key 开始的路径.
     * @param bool $recursive 是否递归.
     * @return mixed
     */
    public function ls($key = '/', $recursive = false);

    /**
     * 设置一个key，不存在则创建，存在则更新.
     * @param $key
     * @param $value
     * @return Response
     * @throws \Exception
     */
    public function set($key, $value);

    /**
     * 新建或者更新一个dir
     * @param $key
     * @param $ttl
     * @return Response
     * @throws \Exception
     */
    public function setDir($key, $ttl);

    /**
     * 更新一个Key
     * @param $key
     * @param $value
     * @param $ttl
     * @param array $condition
     * @return Response
     */
    public function update($key, $value, $ttl = 0, $condition = []);

    /**
     * 更新一个dir
     * @param $key
     * @param int $ttl
     * @return Response
     * @throws \Exception
     */
    public function updateDir($key, $ttl = 0);

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
    public function watch($key, $recursive = true);

    /**
     * 生成异步监听的Uri.
     * 配置管理客户端需要使用.
     *
     * @param $key
     * @param $recursive
     * @return string
     */
    public function getWatchUri($key, $recursive);

    /**
     * 登录认证
     * @param $username
     * @param $password
     * @return mixed
     */
    public function auth($username, $password);

    /**
     * 启用权限验证.
     * @return mixed
     */
    public function authEnable();

    /**
     * 关闭权限验证.
     * @return mixed
     */
    public function authDisable();

}

