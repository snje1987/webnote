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
 * Description of BaseRoute
 *
 * @author Yang Ming <yangming0116@163.com>
 */
abstract class BaseRoute {

    const DEF_METHOD = '';

    public function dispatch($path) {

        $path = urldecode($path);
        $matches = [];
        if (!preg_match('/^([a-z]*)(\/.*)?$/', $path, $matches)) {
            FW\Server::show_404();
        }

        $method = strval($matches[1]);
        $args = isset($matches[2]) ? strval($matches[2]) : '';
        if ($args != '') {
            $args = substr($args, 1);
        }

        if ($method == '') {
            $method = static::DEF_METHOD;
        }
        if ($method == '') {
            FW\Server::show_404();
        }

        $class = new \ReflectionClass(static::class);
        $method = 'c_' . $method;
        if (!method_exists($this, $method)) {
            FW\Server::show_404();
        }
        $method_obj = $class->getMethod($method);
        $doc = $method_obj->getDocComment();
        $doc = str_replace(' ', '', $doc);
        $matches = [];
        if (!preg_match('/^\*@route(\(prev=(true|false)\))?$/im', $doc, $matches)) {
            FW\Server::show_404();
        }
        if (!isset($matches[2]) || $matches[2] === 'true') {
            if (method_exists($this, 'prev')) {
                $this->prev();
            }
        }
        $method_obj->setAccessible(true);
        $method_obj->invoke($this, $args);
        die();
    }

    protected function prev() {
        $auth = FW\Config::get('main', 'auth', true);
        if (!$auth) {
            return true;
        }
        if (isset($_SESSION[Site\Model\System::SESSION_AUTH_KEY]) &&
                $_SESSION[Site\Model\System::SESSION_AUTH_KEY] == true) {
            return true;
        }
        $system_obj = Site\Model\System::get();
        if (strlen($system_obj->password) == 0) {
            FW\Server::redirect('/system/setpwd');
        }
        FW\Server::redirect('/system/login');
        FW\Tpl::assign('title', $system_obj->title);
    }

}
