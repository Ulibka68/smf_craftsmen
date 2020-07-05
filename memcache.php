<?php

$cache = new Memcached();
$cache->addServer('localhost', 11211);
echo '<pre>';
var_dump($cache->getAllKeys());
echo '</pre>'; 