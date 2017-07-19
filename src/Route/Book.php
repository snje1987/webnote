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
        try {
            $file_obj = Site\Model\BookUtils::get_file_from_url($args);
            if ($file_obj != null) {
                die($file_obj->get_file_content());
            }
        } catch (FW\Exception $ex) {

        }
        die();
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
            FW\Common::json_call($_POST, 'Org\Snje\Webnote\Model\BookUtils::editpage');
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
            $siblings = $page_obj->get_siblings_nodes('.md', 'Org\\Snje\\Webnote\\Model\\Buulutils::cmp_dirfirst');
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

    /**
     * @route(prev=true)
     */
    private function c_movepage($args) {
        $this->show_last_page();
        if ($_POST) {
            FW\Common::json_call($_POST, 'Org\Snje\Webnote\Model\BookUtils::movepage');
        }
        try {
            $page_obj = Site\Model\BookUtils::get_page_from_url($args);
            if ($page_obj == null || !$page_obj->is_file()) {
                $this->show_last_page();
            }
            FW\Tpl::prepend('title', '[移动页面]-' . $page_obj->get_url() . '-');
            FW\Tpl::assign('breadcrumb', $page_obj->get_breadcrumb());
            FW\Tpl::display('/book/movepage', $page_obj);
        } catch (FW\Exception $ex) {
            $this->show_last_page();
        }
    }

    /**
     * @route(prev=true)
     */
    private function c_movedir($args) {
        $this->show_last_page();
    }

    /**
     * @route(prev=true)
     */
    private function c_addpage($args) {
        if ($_POST) {
            FW\Common::json_call($_POST, 'Org\Snje\Webnote\Model\BookUtils::addpage');
        }
        try {
            $page_obj = Site\Model\BookUtils::get_page_from_url($args, false, true);
            if ($page_obj == null) {
                $this->show_last_page();
            }
            $parent = $page_obj->get_parent();
            if ($parent == null) {
                $this->show_last_page();
            }
            FW\Tpl::prepend('title', '[新增页面]-' . $parent->get_url() . '-');
            FW\Tpl::assign('breadcrumb', $parent->get_breadcrumb());
            FW\Tpl::assign('parent', $parent);
            FW\Tpl::display('/book/addpage', $page_obj);
        } catch (FW\Exception $ex) {
            $this->show_last_page();
        }
    }

    /**
     * @route(prev=true)
     */
    private function c_adddir($args) {
        if ($_POST) {
            FW\Common::json_call($_POST, 'Org\Snje\Webnote\Model\BookUtils::adddir');
        }
        try {
            $page_obj = Site\Model\BookUtils::get_page_from_url($args, false, true);
            if ($page_obj == null) {
                $this->show_last_page();
            }
            $parent = $page_obj->get_parent();
            if ($parent == null) {
                $this->show_last_page();
            }
            FW\Tpl::prepend('title', '[新增目录]-' . $parent->get_url() . '-');
            FW\Tpl::assign('breadcrumb', $parent->get_breadcrumb());
            FW\Tpl::assign('parent', $parent);
            FW\Tpl::display('/book/adddir', $page_obj);
        } catch (FW\Exception $ex) {
            $this->show_last_page();
        }
    }

    /**
     * @route(prev=true)
     */
    private function c_delpage($args) {
        if ($_POST) {
            FW\Common::json_call($_POST, 'Org\Snje\Webnote\Model\BookUtils::delpage');
        }
        try {
            $page_obj = Site\Model\BookUtils::get_page_from_url($args);
            if ($page_obj == null || !$page_obj->is_file()) {
                $this->show_last_page();
            }
            FW\Tpl::prepend('title', '[删除页面]-' . $page_obj->get_url() . '-');
            FW\Tpl::assign('breadcrumb', $page_obj->get_breadcrumb());
            FW\Tpl::display('/book/delpage', $page_obj);
        } catch (FW\Exception $ex) {
            $this->show_last_page();
        }
    }

    /**
     * @route(prev=true)
     */
    private function c_deldir($args) {
        if ($_POST) {
            FW\Common::json_call($_POST, 'Org\Snje\Webnote\Model\BookUtils::deldir');
        }
        try {
            $page_obj = Site\Model\BookUtils::get_page_from_url($args);
            if ($page_obj == null || !$page_obj->is_dir()) {
                $this->show_last_page();
            }
            FW\Tpl::prepend('title', '[删除目录]-' . $page_obj->get_url() . '-');
            FW\Tpl::assign('breadcrumb', $page_obj->get_breadcrumb());
            FW\Tpl::display('/book/deldir', $page_obj);
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
