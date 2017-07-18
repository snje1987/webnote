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
class BookPage {

    /**
     *
     * @var \Org\Snje\Webnote\Model\Book
     */
    protected $book_obj;
    protected $dir;
    protected $page;
    protected $root;
    protected $type = self::TYPE_NULL;
    protected $fsencoding;
    public static $always_compile;

    const TYPE_NULL = 0;
    const TYPE_PAGE = 1;
    const TYPE_DIR = 2;
    const LINK_METHOD = [
        'F' => 'file',
        'V' => 'view',
    ];
    const COUNT_PER_PAGE = 20;

    /**
     * @param \Org\Snje\Webnote\Model\Book $book
     * @param string $page 页面的路径
     * @return \Org\Snje\Webnote\Model\BookPage
     */
    public function __construct($book, $page) {
        $this->book_obj = $book;
        $this->root = $this->book_obj->get_book_root();
        $this->fsencoding = $this->book_obj->get_fsencoding();
        if ($page == '') {
            $this->dir = '';
            $this->page = '';
            $this->type = self::TYPE_DIR;
        } elseif (FW\File::call(
                        'is_file'
                        , $this->root . 'data/' . $page . '.md'
                        , $this->fsencoding)) {
            $this->page = trim(BookUtils::basename($page), '/');
            $this->dir = trim(BookUtils::dirname($page), '/');
            $this->type = self::TYPE_PAGE;
        } else if (FW\File::call(
                        'is_dir'
                        , $this->root . 'data/' . $page
                        , $this->fsencoding)) {
            $this->dir = trim($page, '/');
            $this->page = '';
            $this->type = self::TYPE_DIR;
        } else {
            $this->page = trim(BookUtils::basename($page), '/');
            $this->dir = trim(BookUtils::dirname($page), '/');
            $this->type = self::TYPE_NULL;
        }
    }

    public function get_dir() {
        return $this->dir;
    }

    public function get_page() {
        return $this->page;
    }

    public function get_path() {
        if ($this->dir != '' && $this->page != '') {
            return $this->dir . '/' . $this->page;
        } elseif ($this->dir != '') {
            return $this->dir;
        }
        return $this->page;
    }

    public function get_url() {
        $path = $this->get_path();
        if ($path != '') {
            return $this->book_obj->get_book_name() . '/' . $path;
        }
        return $this->book_obj->get_book_name();
    }

    public function get_file_path() {
        $path = $this->get_path();
        return $this->root . 'data/' . $path . '.md';
    }

    public function get_page_path($file) {
//        if ($unescape) {
//            $file = preg_replace_callback('/\\\\([0-8]{3})/', function($matches) {
//                return chr(octdec($matches[1]));
//            }, $file);
//        }
        if (strncmp($file, 'data', 4) === 0) {
            return htmlspecialchars(substr(trim($file), 5, -3));
        }
        return htmlspecialchars($file);
    }

    public function get_node_name() {
        if ($this->type == self::TYPE_PAGE || $this->type == self::TYPE_NULL) {
            return $this->get_page();
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

    public function is_page() {
        return $this->type === self::TYPE_PAGE;
    }

    public function is_dir() {
        return $this->type === self::TYPE_DIR;
    }

    public function is_null() {
        return $this->type === self::TYPE_NULL;
    }

    public function is_root() {
        return $this->dir == '' && $this->page == '';
    }

    /**
     * @return \Org\Snje\Webnote\Model\BookPage
     */
    public function get_first_page() {
        if ($this->type !== self::TYPE_DIR) {
            return null;
        }
        $path = $this->root . 'data/' . $this->get_path();
        $list = FW\File::ls($path, '.md', false, $this->fsencoding);
        usort($list, __NAMESPACE__ . '\BookUtils::comp_pagefirst');
        reset($list);
        $v = current($list);
        if ($v['dir']) {
            return new BookPage($this->book_obj, $this->get_path() . '/' . $v['name']);
        } else {
            return new BookPage($this->book_obj, $this->get_path() . '/' . substr($v['name'], 0, -3));
        }
    }

    /**
     * @return \Org\Snje\Webnote\Model\BookPage
     */
    public function get_parent() {
        if ($this->page != '') {
            return new BookPage($this->book_obj, $this->dir);
        } elseif ($this->dir != '') {
            return new BookPage($this->book_obj, BookUtils::dirname($this->dir));
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
        if ($this->type == self::TYPE_PAGE) {
            $data[] = [
                'name' => $this->page,
                'path' => $this->get_url(),
            ];
        }
        return $data;
    }

    /**
     * 笔记本的内部引用链接，格式[[方法:笔记本名//目录路径//文件路径]]
     */
    public function parse_link($link) {
        $matches = [];
        if (!preg_match('/^\[\[(F|V):((([^\/]*)\/\/)?(.*)\/\/)?([^\]]*)\]\]$/i', $link, $matches)) {
            return $link;
        }

        $method = $matches[1];
        if (!isset(self::LINK_METHOD[$method])) {
            return $link;
        }

        $bookname = $matches[4] == '' ? $this->get_book_name() : $matches[4];
        $path = '/book/' . self::LINK_METHOD[$method] . '/' . $bookname;

        $dir = $matches[5] == '' ? $this->dir : $matches[5];
        if ($dir != '') {
            $path .= '/' . $dir;
        }

        $page = $matches[6];

        return $path . '/' . $page;
    }

    public function get_content() {
        if ($this->type != self::TYPE_PAGE) {
            return null;
        }
        $path = $this->get_path();

        $src = $this->root . 'data/' . $path . '.md';
        if (!FW\File::call('is_file', $src, $this->fsencoding)) {
            return '';
        }
        $dest = $this->root . 'html/' . $path . '.html';
        $srctime = FW\File::call('filemtime', $src, $this->fsencoding);
        $desttime = 0;
        if (FW\File::call('file_exists', $dest, $this->fsencoding)) {
            $desttime = FW\File::call('filemtime', $dest, $this->fsencoding);
        }
        $str = '';
        //只在更新后重新编译
        if (self::$always_compile == 1 || $desttime == 0 || $desttime <= $srctime) {
            $str = FW\File::get_content($src, $this->fsencoding);
            $transform = new \Michelf\MarkdownExtra();
            $transform->url_filter_func = [$this, 'parse_link'];
            $transform->custom_code_parser = __NAMESPACE__ . 'BookUtils::parse_codebock';
            $str = $transform->transform($str);
            FW\File::mkdir(dirname($dest), $this->fsencoding);
            FW\File::put_content($dest, $str, $this->fsencoding);
        } else {
            $str = FW\File::get_content($dest, $this->fsencoding);
        }
        $system_obj = System::get();
        $system_obj->set_last_page($this->get_url());
        return $str;
    }

    public function get_raw() {
        if (!$this->is_page()) {
            return '';
        }
        $path = $this->get_file_path();
        if (!FW\File::call('is_file', $path, $this->fsencoding)) {
            return '';
        }
        $str = FW\File::get_content($path, $this->fsencoding);
        return $str;
    }

    /**
     * @return array
     */
    public function get_file_list() {
        if (!$this->is_dir()) {
            return [];
        }
        $path = $this->root . 'data/' . $this->get_path();
        $list = FW\File::ls($path, '.md', false, $this->fsencoding);
        $base = $this->get_path();

        usort($list, __NAMESPACE__ . '\BookUtils::comp_dirfirst');
        $ret = [];
        foreach ($list as $v) {
            if ($v['dir'] === true) {
                $ret[] = new BookPage($this->book_obj, $base . '/' . $v['name']);
            } else {
                if (strlen($v['name']) > 3 && substr($v['name'], -3) === '.md') {
                    $page = substr($v['name'], 0, strlen($v['name']) - 3);
                    $ret[] = new BookPage($this->book_obj, $base . '/' . $page);
                }
            }
        }
        return $ret;
    }

    /**
     * @return array
     */
    public function get_siblings() {
        $parent = $this->get_parent();
        if ($parent != null && $parent->is_dir()) {
            return $parent->get_file_list();
        }
        return [];
    }

    public function get_history($page) {
        $repository = $this->book_obj->get_Repository();
        if ($repository === null) {
            throw new FW\Exception();
        }
        $page = intval($page);
        if ($page < 1) {
            $page = 1;
        }

        $file = null;
        if ($this->is_page()) {
            $file = $this->get_file_path();
        }

        $count = $repository->getTotalCommits($file);
        $max_page = intval($count / self::COUNT_PER_PAGE);

        if ($count % self::COUNT_PER_PAGE != 0) {
            $max_page ++;
        }
        if ($page > $max_page) {
            $page = $max_page;
        }
        $data = [];
        try {
            $data = $repository->getCommits($file, ($page - 1) * self::COUNT_PER_PAGE, self::COUNT_PER_PAGE);
        } catch (\RuntimeException $ex) {
            $data = [];
        }
        return [
            'historys' => $data,
            'max_page' => $max_page,
            'page' => $page,
        ];
    }

    public function get_diff($commit_hash) {
        $repository = $this->book_obj->get_Repository();
        if ($repository === null) {
            throw new FW\Exception();
        }
        try {
            $file = null;
            if ($this->is_page()) {
                $file = $this->get_file_path();
            }
            return $repository->getCommit($commit_hash, $file);
        } catch (\RuntimeException $ex) {
            return;
        }
    }

    public function edit($content, $msg) {
        if (!$this->is_page()) {
            return false;
        }
        $path = $this->get_file_path();
        if (!FW\File::call('is_file', $path, $this->fsencoding)) {
            return false;
        }

        $content = str_replace("\r", '', $content);
        if (substr($content, -1, 1) != "\n") {
            $content .= "\n";
        }

        if (!FW\File::put_content($path, $content, $this->fsencoding)) {
            return false;
        }
        return $this->book_obj->git_cmd('commit', $msg);
    }

}

BookPage::$always_compile = FW\Config::get('book', 'always_compile', 0);
