<?php
/**
 * Created by PhpStorm.
 * User: xuechaoc
 * Date: 2019-06-03
 * Time: 18:23
 */

namespace EtcdPHP\clients\v2;

class Response
{
    public $action;

    public $node;

    public $errorCode;

    public $message;

    /**
     * @return Response
     */
    public static function instance()
    {
        return new self;
    }

    public function setAttributes($values)
    {
        if (!is_array($values)) {
            return false;
        }
        foreach ($values as $k => $v) {
            $this->$k = $v;
        }
        return true;
    }

}