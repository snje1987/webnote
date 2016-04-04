<?php

/**
 * @filename Router.php
 * @encoding UTF-8
 * @author Yang Ming <yangming0116@163.com>
 * @datetime 2016-2-28  13:36:42
 * @Description
 */

namespace Org\Snje\Webnote;

use Org\Snje\Minifw as FW;

class Router {

    protected static $route = [
        'open', 'file', 'view', 'ajax'
    ];

    public function __construct() {

    }

    public function dispatch($path) {
        $path = urldecode($path);

        $matches = [];
        if (!preg_match('/^\/([a-z]*)\/(.*)?$/', $path, $matches)) {
            $this->show_last_page();
        }

        $method = strval($matches[1]);
        $args = strval($matches[2]);

        if (!in_array($method, self::$route)) {
            $this->show_last_page();
        }

        $this->$method($args);
    }

    protected function ajax($path) {
        $pinfo = FW\System::path_info('/' . $path);

        $class = __NAMESPACE__ . ucwords(str_replace('/', '\\', $pinfo[0]), '\\');
        $func = 'ajax_' . strval($pinfo[1]);
        $args = strval($pinfo[3]);
        if (class_exists($class) && method_exists($class, $func)) {
            $class::$func($args);
        }
    }

    protected function file($path) {
        $matches = [];
        if (!preg_match('/^([^\/]*)(\/(.*))?$/', $path, $matches)) {
            $this->show_last_page();
        }
        $book = strval($matches[1]);
        $file = '';
        if (isset($matches[3])) {
            $file = strval($matches[3]);
        }
        $books = Book::get_booklist();
        try {
            $book = urldecode(strval($book));
            $file = urldecode($file);
            if (!isset($books[$book])) {
                die();
            }
            $book_obj = new Book($books[$book]['path']);
            $book_obj->read_file($file);
        } catch (FW\Exception $ex) {//只有笔记本不存在的时候才会抛出异常
            Book::disable_book($book);
            die();
        }
    }

    protected function view($path) {
        $matches = [];
        if (!preg_match('/^([^\/]*)(\/(.*))?$/', $path, $matches)) {
            $this->show_last_page();
        }
        $book = strval($matches[1]);
        $page = '';
        if (isset($matches[3])) {
            $page = strval($matches[3]);
        }
        $books = Book::get_booklist();
        try {
            if (!isset($books[$book])) {
                FW\Server::redirect('/');
            }
            $book_obj = new Book($books[$book]['path']);
            $book_obj->set_path($page);
            FW\Env::set('title', $book . '/' . $page);
            if (FW\Tpl::display('/view/page', $book_obj)) {
                Book::enable_book($book);
            }
        } catch (FW\Exception $ex) {//只有笔记本不存在的时候才会抛出异常
            Book::disable_book($book);
            FW\Server::redirect('/');
        }
    }

    protected function open($args) {
        FW\Env::set('title', "打开笔记本");
        FW\Tpl::display('/open', []);
    }

    protected function show_last_page() {
        $path = Info::get('last_page', '');
        if ($path != '') {
            Info::del('last_page');
            Info::save();
            FW\Server::redirect('/view/' . $path);
        } else {
            $books = Book::get_booklist(Book::Type_Enable);
            if (count($books) > 0) {
                FW\Server::redirect('/view/' . key($books));
            } else {
                FW\Server::redirect('/open/');
            }
        }
    }

}
