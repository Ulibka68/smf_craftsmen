<?php
$memcache_obj = memcache_connect('localhost', 11211);
memcache_set($memcache_obj, 'key645868a', 'some variable', 0, 30);
echo memcache_get($memcache_obj, 'key645868a'), PHP_EOL;
//echo 'false : ', memcache_get($memcache_obj, 'key645868'), PHP_EOL;
