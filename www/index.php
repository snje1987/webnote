<?php

namespace Org\Snje\Webnote;

use Org\Snje\Minifw as FW;
use Org\Snje\Webnote as WN;

require_once '../vendor/autoload.php';

$app = new FW\System([
    '/src/default.php',
    '/config.php'
        ]);

$app->reg_call('/^(.*)$/', [new Router(), 'dispatch']);

$app->run();
