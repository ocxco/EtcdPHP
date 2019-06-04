<?php
/**
 * Created by PhpStorm.
 * User: xuechaoc
 * Date: 2019-06-03
 * Time: 17:28
 */

require __DIR__ . '/vendor/autoload.php';


$client = \EtcdPHP\clients\Client::instance();

//$a1 = $client->get('a1');
//
//var_dump($a1);

//$res = $client->getNode('a3');
//var_dump($res->node);
//
//
//$res = $client->listDir('/');
//var_dump($res->node);
//$client->setRoot('3e3e');
//$res = $client->add('a1', '132');
$res = $client->listDir('/', true);
var_dump($res->action, $res->node);