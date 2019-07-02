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

namespace App\Controler;

use Org\Snje\Minifw as FW;
use App;

/**
 * Description of BaseRoute
 *
 * @author Yang Ming <yangming0116@163.com>
 */
abstract class Base extends FW\Controler {

    const DEFAULT_FUNCTION = '';

    public function dispatch($function, $args) {
        $noprev = ['login', 'setpwd'];
        if (static::class !== System::class || !in_array($function, $noprev)) {
            if (!$this->prev()) {
                return;
            }
        }
        parent::dispatch($function, $args);
    }

    protected function prev() {
        $system_obj = App\Model\System::get();
        FW\Tpl::assign('books', $system_obj->get_booklist());
        FW\Tpl::assign('title', $system_obj->title);
        $auth = $this->config->get_config('main', 'auth', true);
        if (!$auth) {
            return true;
        }
        if (isset($_SESSION[App\Model\System::SESSION_AUTH_KEY]) &&
                $_SESSION[App\Model\System::SESSION_AUTH_KEY] == true) {
            return true;
        }

        if (strlen($system_obj->password) == 0) {
            $this->redirect('/system/setpwd');
        }
        $this->redirect('/system/login');
        return false;
    }

}
