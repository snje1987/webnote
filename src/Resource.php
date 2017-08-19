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

    const COMPOSER_FILES = [
        'components/jquery/jquery.min.js' => 'www/lib/jquery.min.js',
        'jquery-form/form/dist/jquery.form.min.js' => 'www/lib/jquery.form.min.js',
        'components/highlightjs/highlight.pack.min.js' => 'www/lib/highlight.pack.min.js',
        'components/highlightjs/styles/github.css' => 'www/lib/github.css'
    ];
    const COMPOSER_DIRS = [
        'twbs/bootstrap/dist/' => 'www/lib/bootstrap/',
        'bootstrap-select/bootstrap-select/dist/' => 'www/lib/bootstrap-select/',
    ];

    public static function copy_composer_resource() {
        define('WEB_ROOT', str_replace(DIRECTORY_SEPARATOR, '/', dirname(dirname(__DIR__))));
        $src_root = WEB_ROOT . '/vendor/';
        $dest_root = WEB_ROOT . '/';

        foreach (self::COMPOSER_DIRS as $k => $v) {
            $from = $src_root . $k;
            $to = $dest_root . $v;
            if (file_exists($from)) {
                FW\File::copy_dir($from, $to);
            }
        }

        foreach (self::COMPOSER_FILES as $k => $v) {
            $from = $src_root . $k;
            $to = $dest_root . $v;
            if (file_exists($from)) {
                FW\File::copy($from, $to);
            }
        }
    }

}

$app = FW\System::get(__DIR__ . '/defaults.php');
