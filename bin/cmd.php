#!/usr/bin/env php
<?php

namespace App;

use Org\Snje\Minifw as FW;
use Org\Snje\Minifw\Exception;

set_time_limit(0);
bcscale(2);
$root = dirname(__DIR__);
require_once $root . '/vendor/autoload.php';
$app = FW\System::get($root . '/src/defaults.php');

$cmd = isset($argv[1]) ? strval($argv[1]) : '';

if (empty($cmd)) {
    echo "未指定对象\n";
    return;
}

if (count($argv) > 2) {
    $args = array_slice($argv, 2);
}
else {
    $args = [];
}

$class = __NAMESPACE__ . '\\Command\\' . str_replace('_', '', ucwords($cmd, '_'));

if (class_exists($class)) {
    $obj = new $class;
    $obj->dispatch($args);
}
else {
    echo "对象不存在\n";
}
