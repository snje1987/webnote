<?php

/**
 * @filename Book.php
 * @encoding UTF-8
 * @author Yang Ming <yangming0116@163.com>
 * @datetime 2016-2-28  16:19:59
 * @Description
 */

namespace Org\Snje\Webnote\Model;

use Org\Snje\Minifw as FW;
use Org\Snje\Webnote as Site;

class Book {

    protected $root = '';
    protected $data = [];
    protected $encoding = '';
    protected $fsencoding = '';

    const Type_All = 0;
    const Type_Enable = 1;
    const BOOK_E_ERROR = 0;
    const BOOK_E_REDIRECT = 1;

    /**
     * @param \Org\Snje\Webnote\Model\BookUrl $url
     * @return \Org\Snje\Webnote\Model\Book
     */
    public static function create($url) {
        $book_name = $url->get_book();
        $system_obj = Site\Model\System::get();
        $books = $system_obj->get_booklist();
        try {
            if (!isset($books[$book_name])) {
                return null;
            }
            $book_obj = new self($books[$book_name]['path']);
            $system_obj->enable_book($book_name);
            return $book_obj;
        } catch (FW\Exception $ex) {
            $system_obj->disable_book($book_name);
            return null;
        }
    }

    public function __construct($path) {
        $path = str_replace('\\', '/', $path);
        $this->root = rtrim(strval($path), '/') . '/';
        $info_file = $this->root . 'book.json';
        $str = FW\File::get_content($info_file, $this->fsencoding);
        if ($str == '') {
            throw new FW\Exception('读取信息失败');
        }
        $this->data = \json_decode($str, true);
        $this->cur_page = '';
        $this->encoding = FW\Config::get('main', 'encoding', 'utf-8');
        $this->fsencoding = FW\Config::get('main', 'fsencoding', 'utf-8');
    }

    /**
     * 返回一个页面，如果不存在则返回null
     *
     * @param \Org\Snje\Webnote\Model\BookUrl $url
     * @param boolean $allow_suggest 页面不存在时是否提供备用选项
     * @return \Org\Snje\Webnote\Model\BookPage
     */
    public function get_page($url, $allow_suggest = false) {
        $page = $url->get_page();

        $page = new BookPage($this, $page);
        if ($page->is_file()) {
            return $page;
        }

        if (!$allow_suggest) {
            if ($page->is_dir()) {
                return $page;
            }
            return null;
        }
        if ($page->is_dir()) {
            $subpage = $page->get_first_page();
            if ($subpage == null) {
                return $page;
            }
            if (!$subpage->is_null()) {
                throw new FW\Exception($subpage->get_path());
            }
            return $page;
        } else {
            $parent = $page->get_parent();
            if ($parent != null) {
                throw new FW\Exception($parent->get_path());
            }
            return null;
        }
    }

    public function get_book_root() {
        return $this->root;
    }

    public function get_fsencoding() {
        return $this->fsencoding;
    }

    public function open($post) {
        $system_obj = System::get();
        $system_obj->add_book($this->data['name'], $this->root);
        return ['returl' => '/book/view/' . $this->data['name']];
    }

    public function git_cmd($cmd, $args = '') {
        $repository = $this->get_Repository();
        if ($repository == null) {
            return false;
        }
        try {
            if ($cmd == 'commit') {
                $repository->addAll();
                $repository->commit($args);
            } elseif ($cmd == 'push') {
                $repository->push("origin", "master");
            }
            else {
                $repository->pull("origin", "master");
            }
        } catch (\RuntimeException $ex) {
            $repository->reset();
            throw new FW\Exception($ex->getMessage());
        }
        return true;
    }

    public function get_book_name() {
        return $this->data['name'];
    }

    public function get_list() {
        $dir = $this->cur_dir;
        if ($dir != '') {
            $dir .= '/';
        }

        $path = $this->root . 'data/' . $dir;
        $list = FW\File::ls($path, '.md', false, $this->fsencoding);

        usort($list, __NAMESPACE__ . '\Book::comp_pagefirst');
        $pages = [];
        $dirs = [];
        foreach ($list as $v) {
            if ($v['dir'] === true) {
                $dirs[] = [
                    'name' => $v['name'],
                    'path' => $this->data['name'] . '/' . $dir . $v['name'],
                ];
            } else {
                if (strlen($v['name']) > 3 && substr($v['name'], -3) === '.md') {
                    $page = substr($v['name'], 0, strlen($v['name']) - 3);
                    $pages[] = [
                        'name' => $page,
                        'path' => $this->data['name'] . '/' . $dir . $page,
                    ];
                }
            }
        }
        return [
            'pages' => $pages,
            'dirs' => $dirs,
        ];
    }

    public function read_file($file) {
        if ($file === '') {
            return true;
        }
        $path = $this->root . 'file/' . $file;
        FW\File::readfile($path, $this->fsencoding);
    }

    /**
     *
     * @return \Gitter\Repository 版本库
     */
    public function get_Repository() {
        $dir = $this->root;
        $dir = FW\File::conv_to($dir, $this->fsencoding);
        $client = new \Gitter\Client();
        $system_obj = System::get();
        try {
            $repository = $client->getRepository($dir);
            $repository->setConfig('core.quotepath', 'false');
            $repository->setConfig('user.name', $system_obj->git_name);
            $repository->setConfig('user.email', $system_obj->git_email);
            return $repository;
        } catch (\RuntimeException $ex) {
            return null;
        }
    }

}
