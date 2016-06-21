<?php

//是否开启调试模式
$cfg['debug']['debug'] = 0;
//session域
$cfg['main']['domain'] = '.webnote.snje.org';
//文件系统的编码
$cfg['main']['fsencoding'] = 'utf-8';

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

$cfg['main']['encoding'] = 'utf-8';
$cfg['main']['db'] = '';
$cfg['main']['theme'] = 'def';
$cfg['mysql'] = [];
$cfg['sqlite'] = [];
$cfg['save'] = [
    'data' => '/data',
];
$cfg['upload'] = [];
