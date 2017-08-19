<?php

namespace Org\Snje\Webnote;

use Org\Snje\Minifw as FW;
use Org\Snje\Webnote as WN;

require_once '../vendor/autoload.php';

$app = FW\System::get(dirname(__DIR__) . '/src/defaults.php');

$app->reg_call('/^([^?#]*)(.*)?$/', function($path, $nouse = '') {
    $path = urldecode($path);
    try {
        $router = new FW\Router();
        $router->single_layer_route($path, 'Org\Snje\Webnote\Controler', '');
    } catch (\Exception $ex) {
        $controler = new FW\Controler();
        $controler->redirect('/book/view/');
    }
    return true;
});

$app->run();
