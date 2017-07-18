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
        $book_url = Site\Model\BookUrl::create($args);
        if ($book_url == null) {
            $this->show_last_page();
        }
        $book_obj = Site\Model\Book::create($book_url);
        if ($book_obj == null) {
            $this->show_last_page();
        }
        try {
            $page_obj = $book_obj->get_page($book_url, true);
            if ($page_obj == null) {
                $this->show_last_page();
            }
            FW\Tpl::prepend('title', $page_obj->get_url() . '-');
            FW\Tpl::assign('breadcrumb', $page_obj->get_breadcrumb());
            FW\Tpl::display('/book/view', $page_obj);
        } catch (FW\Exception $ex) {
            FW\Server::redirect('/book/view/' . $book_obj->get_book_name() . '/' . $ex->getMessage());
        }
    }

    /**
     * @route(prev=true)
     */
    private function c_edit($args) {

        if ($_POST) {
            FW\Common::json_call($_POST, 'Org\Snje\Webnote\Model\BookUtils::edit_page');
        }
        $system_obj = Site\Model\System::get();
        $info = $this->path_info($args);
        $books = $system_obj->get_booklist();
        try {
            if (!isset($books[$info[0]])) {
                FW\Server::redirect('/');
            }
            $book_obj = new Site\Model\Book($books[$info[0]]['path']);
            $book_obj->step_into($info[1]);
            FW\Tpl::prepend('title', $info[0] . '/' . $info[1] . '-');
            FW\Tpl::assign('breadcrumb', $book_obj->get_breadcrumb());
            $system_obj->enable_book($info[0]);
            FW\Tpl::display('/book/edit', $book_obj);
        } catch (FW\Exception $ex) {
            FW\Server::redirect('/');
        }
    }

    /**
     * @route(prev=true)
     */
    private function c_list($args) {
        $book_url = Site\Model\BookUrl::create($args);
        if ($book_url == null) {
            FW\Server::redirect('/');
        }
        $book_obj = Site\Model\Book::create($book_url);
        if ($book_obj == null) {
            FW\Server::redirect('/');
        }

        $page_obj = $book_obj->get_page($book_url);
        if ($page_obj == null) {
            FW\Server::redirect('/');
        }
        if ($page_obj->is_null()) {
            FW\Server::redirect('/');
        }
        $books = [];
        $dirs = [];
        $pages = [];

        if ($page_obj->is_root()) {
            $system_obj = Site\Model\System::get();
            $books = $system_obj->get_booklist();
        } else {
            $siblings = $page_obj->get_siblings();
            $dirs = $siblings['dirs'];
            $pages = $siblings['pages'];
        }

        FW\Tpl::assign('books', $books);
        FW\Tpl::assign('dirs', $dirs);
        FW\Tpl::assign('pages', $pages);
        FW\Tpl::display('/book/list', $page_obj);
    }

    /**
     * @route(prev=true)
     */
    private function c_push($args) {
        FW\Common::json_call($args, 'Org\Snje\Webnote\Model\BookUtils::push');
    }

    /**
     * @route(prev=true)
     */
    private function c_pull($args) {
        FW\Common::json_call($args, 'Org\Snje\Webnote\Model\BookUtils::pull');
    }

    /**
     * @route(prev=true)
     */
    private function c_history($args) {
        $matches = [];
        if (!preg_match('/^(\d+)\/([^\/]+)(\/(.*))?$/', $args, $matches)) {
            $this->show_last_page();
        }
        $hist_page = intval($matches[1]);
        $book_name = strval($matches[2]);
        $page_path = '';
        if (isset($matches[4])) {
            $page_path = strval($matches[4]);
        }
        $system_obj = Site\Model\System::get();
        $books = $system_obj->get_booklist();
        try {
            if (!isset($books[$book_name])) {
                FW\Server::redirect('/');
            }
            $book_obj = new Site\Model\Book($books[$book_name]['path']);
            if ($page_path != '') {
                $book_obj->step_into($page_path);
            }
            $book_obj->get_history(intval($hist_page), $page_path);
            FW\Tpl::assign('book_name', $book_name);
            FW\Tpl::assign('page_path', $page_path);
            FW\Tpl::prepend('title', '[历史记录]' . $book_name . '/' . $page_path . '-');
            $system_obj->enable_book($book_name);
            FW\Tpl::display('/book/history', $book_obj);
        } catch (FW\Exception $ex) {//只有笔记本不存在的时候才会抛出异常
            $system_obj->disable_book($book_name);
            FW\Server::redirect('/');
        }
    }

    /**
     * @route(prev=true)
     */
    private function c_diff($args) {
        $matches = [];
        if (!preg_match('/^([0-9a-f]+)\/([^\/]+)(\/(.*))?$/', $args, $matches)) {
            $this->show_last_page();
        }
        $commit_hash = strval($matches[1]);
        $book_name = strval($matches[2]);
        $page_path = '';
        if (isset($matches[4])) {
            $page_path = strval($matches[4]);
        }
        $system_obj = Site\Model\System::get();
        $books = $system_obj->get_booklist();
        try {
            if (!isset($books[$book_name])) {
                FW\Server::redirect('/');
            }
            $book_obj = new Site\Model\Book($books[$book_name]['path']);
            if ($page_path != '') {
                $book_obj->step_into($page_path);
            }
            $book_obj->get_diff($commit_hash, $page_path);
            FW\Tpl::assign('book_name', $book_name);
            FW\Tpl::assign('page_path', $page_path);
            $from = isset($_GET['from']) ? intval($_GET['from']) : 1;
            FW\Tpl::assign('from_page', $from);
            FW\Tpl::prepend('title', '[查询修改]' . $book_name . '/' . $page_path . '-');
            $system_obj->enable_book($book_name);
            FW\Tpl::display('/book/diff', $book_obj);
        } catch (FW\Exception $ex) {//只有笔记本不存在的时候才会抛出异常
            $system_obj->disable_book($book_name);
            FW\Server::redirect('/');
        }
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
