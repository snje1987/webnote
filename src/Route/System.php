<?php

/*
 * Copyright (C) 2017 Yang Ming <yangming0116@163.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Org\Snje\Webnote\Route;

use Org\Snje\Webnote as Site;
use Org\Snje\Minifw as FW;

/**
 * Description of System
 *
 * @author Yang Ming <yangming0116@163.com>
 */
class System extends BaseRoute {

    /**
     * @route(prev=false)
     */
    private function c_setpwd($args) {
        if (isset($_SESSION[Site\Model\System::SESSION_AUTH_KEY]) &&
                $_SESSION[Site\Model\System::SESSION_AUTH_KEY] == true) {
            FW\Server::redirect('/book/view/');
        }
        $system_obj = Site\Model\System::get();
        if (strlen($system_obj->password) != 0) {
            FW\Server::redirect('/system/login');
        }
        if ($_POST) {
            FW\Common::json_call($_POST, [$system_obj, 'setpwd']);
        }
        FW\Tpl::prepend('title', '设置密码-' . $system_obj->title);
        FW\Tpl::display('/system/setpwd', []);
    }

    /**
     * @route(prev=false)
     */
    private function c_login($args) {
        if (isset($_SESSION[Site\Model\System::SESSION_AUTH_KEY]) &&
                $_SESSION[Site\Model\System::SESSION_AUTH_KEY] == true) {
            FW\Server::redirect('/book/view/');
        }
        $system_obj = Site\Model\System::get();
        if (strlen($system_obj->password) == 0) {
            FW\Server::redirect('/system/setpwd');
        }
        if ($_POST) {
            FW\Common::json_call($_POST, [$system_obj, 'login']);
        }
        FW\Tpl::prepend('title', '登陆系统-' . $system_obj->title);
        FW\Tpl::display('/system/login', []);
    }

}
