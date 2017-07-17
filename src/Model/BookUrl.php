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
 * Description of BookUrl
 *
 * @author Yang Ming <yangming0116@163.com>
 */
class BookUrl {

    protected $book = '';
    protected $page = '';

    public static function create($url) {
        try {
            return new self($url);
        } catch (FW\Exception $ex) {
            return null;
        }
    }

    public function __construct($url) {
        $matches = [];
        if (!preg_match('/^\/?([^\/]+)(\/(.*))?$/', $url, $matches)) {
            throw new FW\Exception();
        }
        $this->book = strval($matches[1]);
        if (isset($matches[3])) {
            $this->page = strval($matches[3]);
        }
        $this->page = str_replace('..', '', $this->page);
    }

    public function get_book() {
        return $this->book;
    }

    public function get_page() {
        return $this->page;
    }

    public function get_url() {
        if ($this->page != '') {
            return $this->book . '/' . $this->page;
        }
        return $this->book;
    }

}
