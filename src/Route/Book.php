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
    private function c_ajax($args) {
        $pinfo = FW\System::path_info('/' . $args);

        $func = 'ajax_' . strval($pinfo[1]);
        $args = strval($pinfo[3]);
        if (method_exists(Site\Model\Book::class, $func)) {
            Site\Model\Book::$func($args);
        }
    }

    /**
     * @route(prev=true)
     */
    protected function c_file($args) {
        $info = $this->path_info($args);
        $system_obj = Site\Model\System::get();
        $books = $system_obj->get_booklist();
        try {
            $book = $info[0];
            $file = $info[1];
            if (!isset($books[$book])) {
                die();
            }
            $book_obj = new Site\Model\Book($books[$book]['path']);
            $book_obj->read_file($file);
        } catch (FW\Exception $ex) {//只有笔记本不存在的时候才会抛出异常
            $system_obj->disable_book($info[0]);
            die();
        }
    }

    /**
     * @route(prev=true)
     */
    private function c_view($args) {
        $info = $this->path_info($args);
        $system_obj = Site\Model\System::get();
        $books = $system_obj->get_booklist();
        try {
            if (!isset($books[$info[0]])) {
                FW\Server::redirect('/');
            }
            $book_obj = new Site\Model\Book($books[$info[0]]['path']);
            $book_obj->set_path($info[1]);
            FW\Tpl::prepend('title', $info[0] . '/' . $info[1] . '-');
            FW\Tpl::assign('breadcrumb', $book_obj->get_breadcrumb());
            $system_obj->enable_book($info[0]);
            FW\Tpl::display('/book/view', $book_obj);
        } catch (FW\Exception $ex) {//只有笔记本不存在的时候才会抛出异常
            $system_obj->disable_book($info[0]);
            FW\Server::redirect('/');
        }
    }

    /**
     * @route(prev=true)
     */
    private function c_list($args) {
        $info = $this->path_info($args);
        $system_obj = Site\Model\System::get();
        $book_list = $system_obj->get_booklist();
        $books = [];
        $dirs = [];
        $pages = [];
        $book_obj = null;
        try {
            if (!isset($book_list[$info[0]])) {
                throw new fw\Exception();
            }
            $book_obj = new Site\Model\Book($book_list[$info[0]]['path']);
            if ($info[1] == '') {
                $books = $book_list;
            } else {
                $siblings = $book_obj->get_siblings($info[1]);
                $dirs = $siblings['dirs'];
                $pages = $siblings['pages'];
            }
        } catch (FW\Exception $ex) {
            $books = [];
            $dirs = [];
            $pages = [];
        }
        $cur_path = $info[0];
        if ($info[1] != '') {
            $cur_path .= '/' . $info[1];
        }
        FW\Tpl::assign('cur_path', $cur_path);
        FW\Tpl::assign('books', $books);
        FW\Tpl::assign('dirs', $dirs);
        FW\Tpl::assign('pages', $pages);
        FW\Tpl::display('/book/list', []);
    }

    private function path_info($path) {
        $matches = [];
        if (!preg_match('/^([^\/]+)(\/(.*))?$/', $path, $matches)) {
            $this->show_last_page();
        }
        $book = strval($matches[1]);
        $page = '';
        if (isset($matches[3])) {
            $page = strval($matches[3]);
        }
        return [$book, $page];
    }

    private function show_last_page() {
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
