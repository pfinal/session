<?php

require '../vendor/autoload.php';

$session = new \PFinal\Session\FileSession(['savePath' => __DIR__ . '/sess']);

$session->set('name', 'Ethan');

echo $session->get('name') . '<br>';

$session->setFlash('message', 'test');

if ($session->hasFlash('message')) {
    echo $session->getFlash('message') . '<br>';
}

var_dump($session->hasFlash('message'));
