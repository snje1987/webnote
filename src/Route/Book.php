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
        $book_obj = Site\Model\BookUtils::get_book_from_url($args);
        if ($book_obj == null) {
            $this->show_last_page();
        }
        try {
            $page_obj = Site\Model\BookUtils::get_page_from_url($args, true);
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
        try {
            $page_obj = Site\Model\BookUtils::get_page_from_url($args);
            if ($page_obj == null) {
                $this->show_last_page();
            }
            FW\Tpl::prepend('title', '[编辑页面]-' . $page_obj->get_url() . '-');
            FW\Tpl::assign('breadcrumb', $page_obj->get_breadcrumb());
            FW\Tpl::display('/book/edit', $page_obj);
        } catch (FW\Exception $ex) {
            $this->show_last_page();
        }
    }

    /**
     * @route(prev=true)
     */
    private function c_list($args) {
        $page_obj = Site\Model\BookUtils::get_page_from_url($args);
        if ($page_obj == null) {
            die();
        }
        if ($page_obj->is_null()) {
            die();
        }

        if ($page_obj->is_root()) {
            $system_obj = Site\Model\System::get();
            $books = $system_obj->get_booklist();
            FW\Tpl::assign('is_book', true);
            FW\Tpl::assign('books', $books);
        } else {
            FW\Tpl::assign('is_book', false);
            $siblings = $page_obj->get_siblings();
            FW\Tpl::assign('list', $siblings);
        }
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
        if (!preg_match('/^(\d+)\/(.+)?$/', $args, $matches)) {
            $this->show_last_page();
        }
        $hist_page = intval($matches[1]);
        $url = strval($matches[2]);

        try {
            $page_obj = Site\Model\BookUtils::get_page_from_url($url);
            if ($page_obj == null) {
                $this->show_last_page();
            }
            $data = $page_obj->get_history($hist_page);
            FW\Tpl::assign('data', $data);
            FW\Tpl::prepend('title', '[历史记录]' . $page_obj->get_url() . '-');
            FW\Tpl::display('/book/history', $page_obj);
        } catch (FW\Exception $ex) {
            $this->show_last_page();
        }
    }

    /**
     * @route(prev=true)
     */
    private function c_diff($args) {
        $matches = [];
        if (!preg_match('/^([0-9a-f]+)\/(.*)?$/', $args, $matches)) {
            $this->show_last_page();
        }
        $commit_hash = strval($matches[1]);
        $url = strval($matches[2]);
        try {
             $page_obj = Site\Model\BookUtils::get_page_from_url($url);
            if ($page_obj == null) {
                $this->show_last_page();
            }
            $commit_obj = $page_obj->get_diff($commit_hash);
            FW\Tpl::assign('commit_obj', $commit_obj);
            $from = isset($_GET['from']) ? intval($_GET['from']) : 1;
            FW\Tpl::assign('from_page', $from);
            FW\Tpl::prepend('title', '[查询修改]' . $page_obj->get_url() . '-');
            FW\Tpl::display('/book/diff', $page_obj);
        } catch (FW\Exception $ex) {
            $this->show_last_page();
        }
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
