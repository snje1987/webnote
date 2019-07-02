<?php

/**
 * @filename Book.php
 * @encoding UTF-8
 * @author Yang Ming <yangming0116@163.com>
 * @datetime 2016-2-28  16:19:59
 * @Description
 */

namespace App\Model;

use Org\Snje\Minifw as FW;
use App;

class Book {

    protected $root = '';
    protected $encoding = '';
    protected $fsencoding = '';
    protected $bookname = null;
    protected $autopush = false;

    const Type_All = 0;
    const Type_Enable = 1;
    const BOOK_E_ERROR = 0;
    const BOOK_E_REDIRECT = 1;

    /**
     * @param \App\Model\BookUrl $url
     * @return \App\Model\Book
     */
    public static function create($url) {
        $book_name = $url->get_book();
        $system_obj = App\Model\System::get();
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
        $this->load_info();
        $config = FW\Config::get();
        $this->encoding = $config->get_config('main', 'encoding', 'utf-8');
        $this->fsencoding = $config->get_config('main', 'fsencoding', 'utf-8');
    }

    public function load_info() {
        $info_file = $this->root . 'book.json';
        $str = FW\File::get_content($info_file, $this->fsencoding);
        if ($str == '') {
            throw new FW\Exception('读取信息失败');
        }
        $data = \json_decode($str, true);
        $this->bookname = $data['name'];
        $this->autopush = isset($data['autopush']) ? $data['autopush'] : false;
    }

    /**
     * 返回一个页面，如果不存在则返回null
     *
     * @param \App\Model\BookUrl $url
     * @param boolean $allow_suggest 页面不存在时是否提供备用选项
     * @return \App\Model\BookPage
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

    /**
     * 返回一个文件，如果不存在则返回null
     *
     * @param \App\Model\BookUrl $url
     * @return \App\Model\BookFile
     */
    public function get_file($url, $allow_suggest = false) {
        $file_obj = $url->get_page();
        $file_obj = new BookFile($this, $file_obj);
        if ($file_obj->is_null()) {
            if ($allow_suggest) {
                $parent = $file_obj->get_parent();
                if ($parent != null) {
                    throw new FW\Exception($parent->get_path());
                }
            }
            return null;
        }
        return $file_obj;
    }

    public function get_book_root() {
        return $this->root;
    }

    public function get_fsencoding() {
        return $this->fsencoding;
    }

    public function open($post) {
        $system_obj = System::get();
        $system_obj->add_book($this->bookname, $this->root);
        return ['returl' => '/book/view/' . $this->bookname];
    }

    public function git_cmd($cmd, $args = '') {
        $repository = $this->get_Repository();
        if ($repository == null) {
            return false;
        }
        try {
            if ($cmd == 'commit') {
                $cfg = FW\Config::get();
                $encoding = $cfg->get_config('main', 'encoding', '');
                $bash_encoding = $cfg->get_config('main', 'bash_encoding', '');
                if ($bash_encoding != '' && $encoding != $bash_encoding) {
                    $args = mb_convert_encoding($args, $bash_encoding, $encoding);
                }
                $repository->addAll();
                $repository->commit($args);
                if ($this->autopush && FW\Config::get()->get_config('git', 'autopush', false)) {
                    $repository->push("origin", "master");
                }
            } elseif ($cmd == 'push') {
                $repository->push("origin", "master");
            }
            else {
                $repository->pull("origin", "master");
            }
        } catch (\Exception $ex) {
            $repository->reset();
            throw new FW\Exception($ex->getMessage());
        }
        return true;
    }

    public function get_book_name() {
        return $this->bookname;
    }

    /**
     *
     * @return \Gitter\Repository 版本库
     */
    public function get_Repository() {
        $config = FW\Config::get();
        $dir = $this->root;
        $dir = FW\File::conv_to($dir, $this->fsencoding);
        $client = new \Gitter\Client($config->get_config('git', 'path', null));
        try {
            $repository = $client->getRepository($dir);
            $repository->setConfig('core.quotepath', 'false');
            $repository->setConfig('user.name', $config->get_config('git', 'user'));
            $repository->setConfig('user.email', $config->get_config('git', 'email'));
            return $repository;
        } catch (\Exception $ex) {
            return null;
        }
    }

}
