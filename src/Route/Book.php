<?php

/**
 * @filename Book.php
 * @encoding UTF-8
 * @author Yang Ming <yangming0116@163.com>
 * @datetime 2016-2-28  13:36:42
 * @Description
 */

namespace Org\Snje\Webnote\Route;

use Org\Snje\Minifw as FW;
use Org\Snje\Webnote as Site;

class Book extends BaseRoute {

    /**
     * @route(prev=true)
     */
    private function c_ajax($path) {
        $pinfo = FW\System::path_info('/' . $path);

        $func = 'ajax_' . strval($pinfo[1]);
        $args = strval($pinfo[3]);
        if (method_exists(Site\Model\Book::class, $func)) {
            Site\Model\Book::$func($args);
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

    /**
     * @route(prev=true)
     */
    private function c_view($path) {
        $matches = [];
        if (!preg_match('/^([^\/]+)(\/(.*))?$/', $path, $matches)) {
            $this->show_last_page();
        }
        $book = strval($matches[1]);
        $page = '';
        if (isset($matches[3])) {
            $page = strval($matches[3]);
        }
        $system_obj = Site\Model\System::get();
        $books = $system_obj->books;
        FW\Tpl::assign('books', $books);
        try {
            if (!isset($books[$book])) {
                FW\Server::redirect('/');
            }
            $book_obj = new Site\Model\Book($books[$book]['path']);
            $book_obj->set_path($page);
            FW\Tpl::prepend('title', $book . '/' . $page . '-');
            FW\Tpl::assign('breadcrumb', $book_obj->get_breadcrumb());
            $system_obj->enable_book($book);
            FW\Tpl::display('/book/view', $book_obj);
        } catch (FW\Exception $ex) {//只有笔记本不存在的时候才会抛出异常
            $system_obj->disable_book($book);
            FW\Server::redirect('/');
        }
    }

    protected function open($args) {
        FW\Env::set('title', "打开笔记本");
        FW\Tpl::display('/open', []);
    }

    protected function show_last_page() {
        $system_obj = Site\Model\System::get();
        $path = $system_obj->last_page;
        if ($path != '') {
            $system_obj->set_last_page('');
            FW\Server::redirect('/book/view/' . $path);
        } else {
            $books = $system_obj->get_booklist(false);
            if (count($books) > 0) {
                FW\Server::redirect('/book/view/' . key($books));
            } else {
                FW\Server::redirect('/book/open/');
            }
        }
    }

}
