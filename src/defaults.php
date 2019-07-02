<?php

//是否开启调试模式
$cfg['debug']['debug'] = 0;

$cfg['main']['encoding'] = 'utf-8';
//文件系统的编码
$cfg['main']['fsencoding'] = '';
$cfg['main']['bash_encoding'] = '';
$cfg['main']['uri_encoding'] = '';
$cfg['main']['uri_decode'] = false;
$cfg['main']['cache'] = 3600;

$cfg['main']['auth'] = false;

$cfg['git'] = [
    'user' => '',
    'email' => '',
    'autopush' => false,
];

$cfg['main']['err_404'] = '/www/static/error/404.html';

$cfg['book'] = [
    'always_compile' => 0,
];

$cfg['code'] = [
    'dot' => [
        'path' => '',
        'cmd' => '%p -Tsvg',
        'callback' => function($str) {
            $pos = strpos($str, '<svg');
            if ($pos !== false) {
                return substr($str, $pos);
            }
            return $str;
        },
    ],
    'plantuml' => [
        'path' => '',
        'cmd' => '%p -tsvg -p',
        'callback' => function($str) {
            $pos = strpos($str, '<svg');
            if ($pos !== false) {
                return substr($str, $pos);
            }
            return $str;
        },
    ],
];

$cfg['main']['db'] = '';
$cfg['main']['theme'] = 'def';
$cfg['main']['resource_map'] = '/config/resource_map.php';
$cfg['mysql'] = [];
$cfg['sqlite'] = [];
$cfg['save'] = [
    'data' => '/data',
];
$cfg['upload'] = [];

$cfg['path']['web_root'] = dirname(__DIR__);


require dirname(__DIR__) . '/config.php';
