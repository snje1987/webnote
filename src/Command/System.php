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

    /**
     * 复制资源文件
     */
    public function cmd_copy_resource($args) {
        echo "clear old files\n";

        FW\File::clear_dir('/www/theme', false);
        FW\File::clear_dir('/www/lib', false);
        FW\File::clear_dir('/www/static', false);

        echo "copy new files\n";
        $obj = new FW\Resource();
        $obj->compile_all();

        echo "done\n";
    }

}
