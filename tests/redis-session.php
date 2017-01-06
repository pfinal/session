<?php

require '../vendor/autoload.php';

$session = new \PFinal\Session\RedisSession();

//$bool = $session->set('name', 'Ethan');
//$bool = $session->set('user.id', 18);
//
//var_dump($bool);
//
//echo $session->get('name');

//$session->clear();

$session->setFlash('message','test');

var_dump($session->hasFlash('message'));


var_dump($session->getFlash('message'));


var_dump($session->hasFlash('message'));


//$session->remove('name');
//
//var_dump( $session->get('name'));

//$session->setFlash('message', 'test');
//
//if ($session->hasFlash('message')) {
//    echo $session->getFlash('message');
//}
//
//var_dump($session->hasFlash('message'));
