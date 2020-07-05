<?php
$memcached = new Memcached();
$memcached->addServer('localhost', 11211);

$memcached->set('key645868', 'value записал Я12');

echo $memcached->get('key645868'); // Если всё ок, то выведет value
