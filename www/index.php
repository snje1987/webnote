<?php

namespace Org\Snje\Webnote;

use Org\Snje\Minifw as FW;
use Org\Snje\Webnote as WN;

require_once '../vendor/autoload.php';

$app = FW\System::get([
            'web_root' => '',
            'cfg' => [
                '/src/default.php',
                '/config.php'
            ]
        ]);

$app->reg_call('/^([^?]*)(\?.*)?$/', function($path, $nouse = '') {

    $path = urldecode($path);
    $matches = [];
    $args = '';
    if (!preg_match('/^\/([a-z]*)\/(.*)?$/', $path, $matches)) {
        FW\Server::redirect('/book/view/');
    }
    $router = ucfirst(strval($matches[1]));
    $args = isset($matches[2]) ? strval($matches[2]) : '';
    $router = __NAMESPACE__ . '\\Route\\' . $router;
    if (!class_exists($router)) {
        FW\Server::redirect('/book/view/');
    }
    $router_obj = new $router();
    if (!method_exists($router_obj, 'dispatch')) {
        FW\Server::redirect('/book/view/');
    }

    $router_obj->dispatch($args);
    return true;
});

$app->run();
