# [Session](http://pfinal.cn)

PHP Session操作类，支持Redis

PHP交流 QQ 群：`16455997`

环境要求：PHP >= 5.3

使用 [composer](https://getcomposer.org/)

```shell
composer require pfinal/session
```

```php
<?php

require 'vendor/autoload.php';

$session = new \PFinal\Session\NativeSession();

$session->set('name', 'Ethan');

echo $session->get('name');

$session->setFlash('message', 'test');

if ($session->hasFlash('message')) {
 echo $session->getFlash('message');
}

```