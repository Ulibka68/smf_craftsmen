<?php

if ( function_exists('memcache_connect')) {
    echo 'memcache_connect present',PHP_EOL;
} else {
    echo 'NOOO memcache_connect present',PHP_EOL;
}

$memcache_obj = memcache_connect('localhost', 11211);
if ( function_exists('memcache_set')) {
    echo 'memcache_set present',PHP_EOL;
} else {
    echo 'NOOO memcache_set present',PHP_EOL;
}

memcache_set($memcache_obj, 'key645868a', 'some variable99');
echo memcache_get($memcache_obj, 'key645868a'), PHP_EOL;
//echo 'false : ', memcache_get($memcache_obj, 'key645868'), PHP_EOL;
