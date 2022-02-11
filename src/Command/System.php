<?php

namespace App\Command;

use Org\Snje\Minifw as FW;
use Org\Snje\Minifw\Exception;

class System extends CommandBase {

    /**
     * 更新用于访问https接口的根证书
     */
    public function cmd_update_rootca($args) {
        $client = new FW\Client();
        $client->update_caroot();
    }

}
