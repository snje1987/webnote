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
class BookPage extends BookNode {

    /**
     *
     * @var \Org\Snje\Webnote\Model\Book
     */
    public static $always_compile;

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
        parent::__construct($book, $page);
        $path = $this->get_path();
        if (FW\File::call(
                        'is_file'
                        , $this->root . 'data/' . $path . '.md'
                        , $this->fsencoding)) {
            $this->type = self::TYPE_FILE;
        } else if (FW\File::call(
                        'is_dir'
                        , $this->root . 'data/' . $path
                        , $this->fsencoding)) {
            $this->dir = trim($path, '/');
            $this->file = '';
            $this->type = self::TYPE_DIR;
        }
    }


    public function get_real_path() {
        $path = $this->get_path();
        if ($this->is_file()) {
            return $this->root . 'data/' . $path . '.md';
        }
        else {
            return $this->root . 'data/' . $path;
        }
    }

    public function get_page_path($file) {
//        if ($unescape) {
//            $file = preg_replace_callback('/\\\\([0-8]{3})/', function($matches) {
//                return chr(octdec($matches[1]));
//            }, $file);
//        }
        //if (strncmp($file, 'data', 4) === 0) {
        //    return htmlspecialchars(substr(trim($file), 5, -3));
        //} elseif (strncmp($file, 'file', 4) === 0) {
        //    return htmlspecialchars(substr(trim($file), 5));
        //}
        return htmlspecialchars($file);
    }

    /**
     * @return \Org\Snje\Webnote\Model\BookPage
     */
    public function get_first_page() {
        return $this->first_sub_node('.md', __NAMESPACE__ . '\BookUtils::comp_pagefirst');
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
        if (!$this->is_file()) {
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
        if ($this->is_file()) {
            $file = $this->get_real_path();
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
            if ($this->is_file()) {
                $file = $this->get_real_path();
            }
            return $repository->getCommit($commit_hash, $file);
        } catch (\RuntimeException $ex) {
            return;
        }
    }

    public function edit($content, $msg) {
        if (!$this->is_file()) {
            return false;
        }
        $path = $this->get_real_path();
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

    public function addpage($content, $msg) {
        if (!$this->is_null()) {
            return false;
        }
        $path = $this->get_real_path() . '.md';
        if (FW\File::call('file_exists', $path, $this->fsencoding)) {
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
