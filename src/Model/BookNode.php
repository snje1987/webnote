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
abstract class BookNode {

    /**
     *
     * @var \Org\Snje\Webnote\Model\Book
     */
    protected $book_obj;
    protected $dir;
    protected $file;
    protected $root;
    protected $type = self::TYPE_NULL;
    protected $fsencoding;

    const TYPE_NULL = 0;
    const TYPE_FILE = 1;
    const TYPE_DIR = 2;

    /**
     * @param \Org\Snje\Webnote\Model\Book $book
     * @param string $path 页面的路径
     * @return \Org\Snje\Webnote\Model\BookPage
     */
    public function __construct($book, $path) {
        $path = str_replace('..', '', $path);
        $this->book_obj = $book;
        $this->root = $this->book_obj->get_book_root();
        $this->fsencoding = $this->book_obj->get_fsencoding();
        if ($path == '') {
            $this->dir = '';
            $this->file = '';
            $this->type = self::TYPE_DIR;
        } elseif (FW\File::call(
                        'is_file'
                        , $this->root . 'data/' . $path . '.md'
                        , $this->fsencoding)) {
            $this->file = trim(BookUtils::basename($path), '/');
            $this->dir = trim(BookUtils::dirname($path), '/');
            $this->type = self::TYPE_FILE;
        } else if (FW\File::call(
                        'is_dir'
                        , $this->root . 'data/' . $path
                        , $this->fsencoding)) {
            $this->dir = trim($path, '/');
            $this->file = '';
            $this->type = self::TYPE_DIR;
        } else {
            $this->file = trim(BookUtils::basename($path), '/');
            $this->dir = trim(BookUtils::dirname($path), '/');
            $this->type = self::TYPE_NULL;
        }
    }

    public function get_dir() {
        return $this->dir;
    }

    public function get_name() {
        return $this->file;
    }

    public function get_path() {
        if ($this->dir != '' && $this->file != '') {
            return $this->dir . '/' . $this->file;
        } elseif ($this->dir != '') {
            return $this->dir;
        }
        return $this->file;
    }

    public function get_url() {
        $path = $this->get_path();
        if ($path != '') {
            return $this->book_obj->get_book_name() . '/' . $path;
        }
        return $this->book_obj->get_book_name();
    }

    abstract public function get_real_path();

    public function get_node_name() {
        if ($this->type == self::TYPE_FILE || $this->type == self::TYPE_NULL) {
            return $this->get_name();
        } elseif ($this->dir != '') {
            return BookUtils::basename($this->dir);
        }
        return $this->get_book_name();
    }

    public function get_book_name() {
        return $this->book_obj->get_book_name();
    }

    /**
     * @return \Org\Snje\Webnote\Model\Book
     */
    public function get_book() {
        return $this->book_obj;
    }

    public function is_file() {
        return $this->type === self::TYPE_FILE;
    }

    public function is_dir() {
        return $this->type === self::TYPE_DIR;
    }

    public function is_null() {
        return $this->type === self::TYPE_NULL;
    }

    public function is_root() {
        return $this->dir == '' && $this->file == '';
    }

    /**
     * @return static
     */
    public function first_sub_node($ext = '', $cmp_func = null) {
        if ($this->type !== self::TYPE_DIR) {
            return null;
        }
        $path = $this->get_real_path();
        $list = FW\File::ls($path, $ext, false, $this->fsencoding);
        if (is_callable($cmp_func)) {
            usort($list, $cmp_func);
        }
        reset($list);
        if (count($list) == 0) {
            return null;
        }
        $ext_len = strlen($ext);
        $v = current($list);
        if ($v['dir']) {
            return new static($this->book_obj, $this->get_path() . '/' . $v['name']);
        } else {
            return new static($this->book_obj, $this->get_path() . '/' . substr($v['name'], 0, -1 * $ext_len));
        }
    }

    /**
     * @return \Org\Snje\Webnote\Model\BookPage
     */
    public function get_parent() {
        if ($this->file != '') {
            return new static($this->book_obj, $this->dir);
        } elseif ($this->dir != '') {
            return new static($this->book_obj, BookUtils::dirname($this->dir));
        } else {
            return null;
        }
    }

    public function get_breadcrumb() {
        $dir = $this->dir;
        $book_name = $this->book_obj->get_book_name();
        $data = [];
        while ($dir != '' && $dir != '.' && $dir != '/') {
            $data[] = [
                'name' => BookUtils::basename($dir),
                'path' => $book_name . '/' . $dir,
            ];
            $dir = BookUtils::dirname($dir);
        }
        $data[] = [
            'name' => $book_name,
            'path' => $book_name,
        ];
        $data = array_reverse($data);
        if ($this->type == self::TYPE_FILE) {
            $data[] = [
                'name' => $this->file,
                'path' => $this->get_url(),
            ];
        }
        return $data;
    }

    public function get_file_content() {
        if (!$this->is_file()) {
            return null;
        }
        $path = $this->get_real_path();
        if (!FW\File::call('is_file', $path, $this->fsencoding)) {
            return null;
        }
        $str = FW\File::get_content($path, $this->fsencoding);
        return $str;
    }

    /**
     * @return array
     */
    public function get_sub_nodes($ext = '', $cmp_func = null) {
        if (!$this->is_dir()) {
            return [];
        }
        $path = $this->get_real_path();
        $list = FW\File::ls($path, $ext, false, $this->fsencoding);
        $base = $this->get_path();
        if (is_callable($cmp_func)) {
            usort($list, $cmp_func);
        }
        $ret = [];
        $ext_len = strlen($ext);
        foreach ($list as $v) {
            if ($v['dir'] === true) {
                $ret[] = new static($this->book_obj, $base . '/' . $v['name']);
            } else {
                $name = substr($v['name'], 0, strlen($v['name']) - $ext_len);
                $ret[] = new static($this->book_obj, $base . '/' . $name);
            }
        }
        return $ret;
    }

    /**
     * @return array
     */
    public function get_siblings_nodes($ext = '', $cmp_func = null) {
        $parent = $this->get_parent();
        if ($parent != null && $parent->is_dir()) {
            return $parent->get_sub_nodes($ext, $cmp_func);
        }
        return [];
    }

    public function adddir() {
        if (!$this->is_null()) {
            return false;
        }
        $path = $this->get_real_path();
        if (FW\File::call('file_exists', $path, $this->fsencoding)) {
            return false;
        }
        FW\File::mkdir($path, $this->fsencoding);
        return true;
    }

    public function delete($msg = '') {
        if ($this->is_file()) {
            $path = $this->get_real_path();
            FW\File::delete($path, true);
            return $this->book_obj->git_cmd('commit', $msg);
        } elseif ($this->is_dir()) {
            $list = $this->get_sub_nodes();
            if (!empty($list)) {
                throw new FW\Exception('目录不为空');
            }
            $path = $this->get_real_path();
            FW\File::delete($path, true);
            return true;
        } else {
            return false;
        }
    }

}
