<?php

namespace App;

use Org\Snje\Minifw as FW;

require_once 'vendor/autoload.php';

$app = FW\System::get(__DIR__. '/src/defaults.php');

$app->reg_call('/^\/www\/([^?#]+).*$/', function($path) {
    $path = str_replace('..', '', $path);
    try {
        $router = new FW\Router();
        $router->resource_route($path, '/www/');
    } catch (\Exception $ex) {

    }
    return true;
});

$app->reg_call('/^([^?#]*)(.*)?$/', function($path, $nouse = '') {
    $cfg = FW\Config::get();
    $decode = $cfg->get_config('main', 'uri_decode', false);
    if ($decode) {
        $path = urldecode($path);
    }
    $encoding = $cfg->get_config('main', 'encoding', '');
    $uri_encoding = $cfg->get_config('main', 'uri_encoding', '');
    if ($uri_encoding != '' && $encoding != $uri_encoding) {
        $path = mb_convert_encoding($path, $encoding, $uri_encoding);
    }
    try {
        $router = new FW\Router();
        $router->single_layer_route($path, 'App\\Controler', '');
    } catch (\Exception $ex) {
        $controler = new FW\Controler();
        $controler->redirect('/book/view/');
    }
    return true;
});

$app->run();
