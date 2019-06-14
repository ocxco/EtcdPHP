<?php
/**
 * Created by PhpStorm.
 * User: xuechaoc
 * Date: 2019-06-03
 * Time: 17:28
 */

require __DIR__ . '/vendor/autoload.php';


$client = \EtcdPHP\Client::instance();
//
$client->auth('root', 'go2admin');
$r0 = $client->authEnable();
$r1 = $client->authDisable();
var_dump($r0, $r1);
exit;
$curl = curl_init('http://127.0.0.1:2379/v2/auth/enable');
curl_setopt($curl, CURLOPT_HTTPHEADER, ['authorization:Basic cm9vdDpnbzJhZG1pbg==']);
curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
$res = curl_exec($curl);
var_dump($res);
exit;
///**
// * Test Key
// */
$a1 = $client->mk('a123', 123456);
$a2 = $client->get('a123');
$a3 = $client->set('a123', '11111');
$a4 = $client->update('a123', '12345');
$a5 = $client->rm('a123');
var_dump($a1, $a2, $a3, $a4, $a5);

//
///**
// * Test Dir
// */
//$a1 = $client->mkdir('dirA');
//$a2 = $client->set('dirA/aaaa', 'aaaa');
//$a3 = $client->ls('dirA');
//$a4 = $client->updateDir('dirA', 200);
//$a5 = $client->ls('dirA');
//$a6 = $client->setDir('dirA', 100);
//$a7 = $client->ls('dirA');
//$a8 = $client->rmdir('dirA', true);
//var_dump($a1, $a2, $a3, $a4, $a5, $a6, $a7, $a8);

/**
 * Watch
 */

//$res = $client->watch('db_go2', true);
