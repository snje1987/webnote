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

$app->reg_call('/^(.*)$/', [new Router(), 'dispatch']);

$app->run();
