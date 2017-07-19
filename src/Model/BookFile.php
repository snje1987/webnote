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

namespace Org\Snje\Webnote\Model;

use Org\Snje\Minifw as FW;
use Org\Snje\Webnote as Site;

/**
 * Description of BookPage
 *
 * @author Yang Ming <yangming0116@163.com>
 */
class BookFile extends BookNode {

    public function __construct($book, $path) {
        parent::__construct($book, $path);
        $path = $this->get_path();
        if (FW\File::call(
                        'is_file'
                        , $this->root . 'file/' . $path
                        , $this->fsencoding)) {
            $this->type = self::TYPE_FILE;
        } else if (FW\File::call(
                        'is_dir'
                        , $this->root . 'file/' . $path
                        , $this->fsencoding)) {
            $this->dir = trim($path, '/');
            $this->file = '';
            $this->type = self::TYPE_DIR;
        }
    }

    public function get_real_path() {
        $path = $this->get_path();
        return $this->root . 'file/' . $path;
    }

}