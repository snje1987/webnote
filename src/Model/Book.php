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

    protected $path = '';
    protected $data = [];
    protected $cur_page = '';
    protected $cur_dir = '';
    protected $encoding = '';
    protected $fsencoding = '';
    public static $always_compile;
    protected static $link_method = [
        'F' => 'file',
        'V' => 'view',
    ];

    const Type_All = 0;
    const Type_Enable = 1;

    public function __construct($path) {
        $path = str_replace('\\', '/', $path);
        $this->path = strval($path);
        if (substr($this->path, -1) !== '/') {
            $this->path .= '/';
        }
        $info_file = $this->path . 'book.json';
        $str = FW\File::get_content($info_file, $this->fsencoding);
        if ($str === false) {
            throw new FW\Exception('读取信息失败');
        }
        $this->data = \json_decode($str, true);
        $this->cur_page = '';
        $this->encoding = FW\Config::get('main', 'encoding', 'utf-8');
        $this->fsencoding = FW\Config::get('main', 'fsencoding', 'utf-8');
    }

    public function save() {
        $info_file = $this->path . 'book.json';
        $str = \json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return FW\File::put_content($info_file, $str, $this->fsencoding);
    }

    public function set_path($path) {
        if ($path != '') {
            if (FW\File::call('is_file', $this->path . 'data/' . $path . '.md', $this->fsencoding)) {//存在相应的文件
                $this->cur_page = self::basename($path);
                $this->cur_dir = self::dirname($path);
            } else if (FW\File::call('is_dir', $this->path . 'data/' . $path, $this->fsencoding)) {//存在相应目录
                $page = $this->get_first_page('data/' . $path);
                if ($page != '') {//目录中存在页面就显示
                    FW\Server::redirect('/book/view/' . $this->data['name'] . '/' . $path . '/' . $page);
                }
                //不存在则显示空模板
                $this->cur_dir = $path;
                $this->cur_page = '';
            } else {//文件不存在
                FW\Server::redirect('/book/view/' . $this->data['name'] . '/' . self::dirname($path));
            }
        } else {
            $path = $this->get_first_page();
            if ($path != '') {
                FW\Server::redirect('/book/view/' . $this->data['name'] . '/' . $path);
            } else {
                $this->cur_dir = '';
                $this->cur_page = '';
            }
        }
    }

    public function open($post) {
        $system_obj = System::get();
        $system_obj->add_book($this->data['name'], $this->path);
        return ['returl' => '/book/view/' . $this->data['name']];
    }

    public function get_content() {
        if ($this->cur_page == '') {
            return '';
        }

        if ($this->cur_dir != '') {
            $this->cur_dir .= '/';
        }

        $src = $this->path . 'data/' . $this->cur_dir . $this->cur_page . '.md';
        if (!FW\File::call('is_file', $src, $this->fsencoding)) {
            return '';
        }
        $dest = $this->path . 'html/' . $this->cur_dir . $this->cur_page . '.html';
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
            $transform->custom_code_parser = __CLASS__ . '::parse_codebock';
            $str = $transform->transform($str);
            FW\File::mkdir(dirname($dest), $this->fsencoding);
            FW\File::put_content($dest, $str, $this->fsencoding);
        } else {
            $str = FW\File::get_content($dest, $this->fsencoding);
        }
        $system_obj = System::get();
        $system_obj->set_last_page($this->data['name'] . '/' . $this->cur_dir . $this->cur_page);
        return $str;
    }

    public function get_book_name() {
        return $this->data['name'];
    }

    public function get_cur_dir() {
        return $this->cur_dir;
    }

    public function get_cur_page() {
        return $this->cur_page;
    }

    public function get_breadcrumb() {
        $dir = $this->cur_dir;
        $data = [];
        while ($dir != '' && $dir != '.') {
            $data[] = [
                'name' => self::basename($dir),
                'path' => $this->data['name'] . '/' . $dir,
            ];
            $dir = dirname($dir);
        }
        $data[] = [
            'name' => $this->data['name'],
            'path' => $this->data['name'],
        ];
        $data = array_reverse($data);
        $dir = $this->cur_dir;
        if ($dir != '') {
            $dir = '/' . $dir;
        }
        $data[] = [
            'name' => $this->cur_page,
            'path' => $this->data['name'] . $dir . '/' . $this->cur_page,
        ];
        //print_r($data);
        return $data;
    }

    public function get_list() {
        $dir = $this->cur_dir;
        if ($dir != '') {
            $dir .= '/';
        }

        $path = $this->path . 'data/' . $dir;
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

    public function get_siblings($page) {
        $pages = [];
        $dirs = [];
        if ($page !== '') {
            $parent = self::dirname($page);
            if ($parent == '/') {
                $parent = '';
            }
            if ($parent != '') {
                $parent .= '/';
            }

            $path = $this->path . 'data/' . $parent;
            $list = FW\File::ls($path, '.md', false, $this->fsencoding);

            usort($list, __NAMESPACE__ . '\Book::comp_pagefirst');

            foreach ($list as $v) {
                if ($v['dir'] === true) {
                    $dirs[] = [
                        'name' => $v['name'],
                        'path' => $this->data['name'] . '/' . $parent . $v['name'],
                    ];
                } else {
                    if (strlen($v['name']) > 3 && substr($v['name'], -3) === '.md') {
                        $page = substr($v['name'], 0, strlen($v['name']) - 3);
                        $pages[] = [
                            'name' => $page,
                            'path' => $this->data['name'] . '/' . $parent . $page,
                        ];
                    }
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
        $path = $this->path . 'file/' . $file;
        FW\File::readfile($path, $this->fsencoding);
    }

    public static function ajax_siblings($args) {
        $matches = [];
        if (!preg_match('/^([^\/]*)(\/(.*))?$/', $args, $matches)) {
            die();
        }
        $book = strval($matches[1]);
        $page = '';
        if (isset($matches[3])) {
            $page = strval($matches[3]);
        }
        $system_obj = System::get();
        $books = $system_obj->get_booklist();
        try {
            if (!isset($books[$book])) {
                die();
            }
            $book_obj = new Book($books[$book]['path']);
            $book_obj->get_siblings($page);
        } catch (FW\Exception $ex) {//只有笔记本不存在的时候才会抛出异常
            $system_obj->disable_book($book);
            die();
        }
    }

    public static function comp_pagefirst($a, $b) {
        if ($a['dir'] == $b['dir']) {
            return strcmp($a['name'], $b['name']);
        } elseif ($a['dir'] === true) {
            return 1;
        } else {
            return -1;
        }
    }

    public static function dirname($page) {
        $page = \dirname($page);
        if ($page == '.') {
            $page = '';
        }
        return $page;
    }

    public static function basename($page) {
        $pos = strrpos($page, '/');
        if ($pos === false) {
            return $page;
        }
        return substr($page, $pos + 1);
    }

    /*
     * 笔记本的内部引用链接，格式[[方法:笔记本名//目录路径//文件路径]]
     */

    public function parse_link($link) {
        $matches = [];
        if (!preg_match('/^\[\[(F|V):((([^\/]*)\/\/)?(.*)\/\/)?([^\]]*)\]\]$/i', $link, $matches)) {
            return $link;
        }

        $method = $matches[1];
        if (!isset(self::$link_method[$method])) {
            return $link;
        }

        $bookname = $matches[4] == '' ? $this->data['name'] : $matches[4];
        $path = '/book/' . self::$link_method[$method] . '/' . $bookname;

        $dir = $matches[5] == '' ? $this->cur_dir : $matches[5];
        if ($dir != '') {
            $path .= '/' . $dir;
        }

        $page = $matches[6];

        return $path . '/' . $page;
    }

    public static function parse_codebock($class, $code) {
        $cfg = FW\Config::get('code', $class, []);
        $ret = '';
        if (is_array($cfg) && isset($cfg['cmd']) &&
                isset($cfg['path']) && $cfg['path'] != '') {

            $cmd = str_replace('%p', $cfg['path'], $cfg['cmd']);
            $handle = proc_open($cmd, [
                0 => ["pipe", "r"],
                1 => ["pipe", "w"],
                    ], $pipes, null, null);

            if (is_resource($handle)) {
                fwrite($pipes[0], $code);
                fclose($pipes[0]);
                $ret = stream_get_contents($pipes[1]);
                fclose($pipes[1]);
                proc_close($handle);
                if (isset($cfg['callback']) && is_callable($cfg['callback'])) {
                    $ret = call_user_func($cfg['callback'], $ret);
                }
            }
        }
        return $ret;
    }

    /**
     * 找到目录中的第一个页面，如果不存在页面则返回第一个目录，如果目录也不存在，则返回空字符串
     *
     * @param string $path 查找的目录
     * @return string
     */
    protected function get_first_page($path = 'data/') {
        $path = $this->path . $path;
        $list = FW\File::ls($path, '.md', false, $this->fsencoding);
        usort($list, __NAMESPACE__ . '\Book::comp_pagefirst');
        reset($list);
        $v = current($list);
        if ($v['dir']) {
            return $v['name'];
        } else {
            return substr($v['name'], 0, -3);
        }
    }

}

Book::$always_compile = FW\Config::get('book', 'always_compile', 0);
