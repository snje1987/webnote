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

namespace Org\Snje\Webnote;

use Org\Snje\Minifw as FW;

/**
 * Description of Resource
 *
 * @author Yang Ming <yangming0116@163.com>
 */
class Resource {

    public static function copy_composer_resource() {

        FW\File::clear_dir(WEB_ROOT . '/www/lib', true);
        FW\File::clear_dir(WEB_ROOT . '/www/theme', true);

        $recource_obj = new FW\Resource();
        $recource_obj->compile_all();
    }

}

$app = FW\System::get(__DIR__ . '/defaults.php');
