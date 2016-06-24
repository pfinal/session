<?php

require '../vendor/autoload.php';

$session = new \PFinal\Session\NativeSession();

$session->set('name', 'Ethan');

echo $session->get('name');

$session->setFlash('message', 'test');

if ($session->hasFlash('message')) {
    echo $session->getFlash('message');
}

var_dump($session->hasFlash('message'));
