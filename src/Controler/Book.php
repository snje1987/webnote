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

namespace App\Controler;

use Org\Snje\Minifw as FW;
use App;

class Book extends Base {

    protected function c_file($args) {
        try {
            $file_obj = App\Model\BookUtils::get_file_from_url($args);
            if ($file_obj != null && $file_obj->is_file()) {
                $file_obj->readfile();
            }
        } catch (FW\Exception $ex) {

        }
    }

    private function c_fileview($args) {
        $book_obj = App\Model\BookUtils::get_book_from_url($args);
        if ($book_obj == null) {
            $this->show_last_page();
            return;
        }
        try {
            $file_obj = App\Model\BookUtils::get_file_from_url($args, true);
            if ($file_obj == null) {
                $this->show_last_page();
                return;
            }
            if ($file_obj->is_dir()) {
                FW\Tpl::assign('list', $file_obj->get_sub_nodes('', 'App\\Model\\BookUtils::comp_dirfirst'));
            }

            FW\Tpl::prepend('title', $file_obj->get_url() . '-');
            FW\Tpl::assign('breadcrumb', $file_obj->get_breadcrumb());
            FW\Tpl::display('/book/fileview', $file_obj, $this->theme);
        } catch (FW\Exception $ex) {
            $this->redirect('/book/fileview/' . $book_obj->get_book_name() . '/' . $ex->getMessage());
        }
    }

    private function c_filelist($args) {
        $file_obj = App\Model\BookUtils::get_file_from_url($args);
        if ($file_obj == null) {
            die();
        }
        if ($file_obj->is_null()) {
            die();
        }

        if ($file_obj->is_root()) {
            $system_obj = App\Model\System::get();
            $books = $system_obj->get_booklist();
            FW\Tpl::assign('is_book', true);
            FW\Tpl::assign('books', $books);
        } else {
            FW\Tpl::assign('is_book', false);
            $siblings = $file_obj->get_siblings_nodes('', 'App\\Model\\BookUtils::comp_dirfirst');
            FW\Tpl::assign('list', $siblings);
        }
        FW\Tpl::display('/book/filelist', $file_obj, $this->theme);
    }

    private function c_view($args) {
        $book_obj = App\Model\BookUtils::get_book_from_url($args);
        if ($book_obj == null) {
            $this->show_last_page();
            return;
        }
        try {
            $page_obj = App\Model\BookUtils::get_page_from_url($args, true);
            if ($page_obj == null) {
                $this->show_last_page($book_obj->get_book_name());
                return;
            }
            FW\Tpl::prepend('title', $page_obj->get_url() . '-');
            FW\Tpl::assign('breadcrumb', $page_obj->get_breadcrumb());
            FW\Tpl::display('/book/view', $page_obj, $this->theme);
        } catch (FW\Exception $ex) {
            $this->redirect('/book/view/' . $book_obj->get_book_name() . '/' . $ex->getMessage());
        }
    }

    private function c_edit($args) {
        if ($_POST) {
            $this->json_call($_POST, 'App\Model\BookUtils::editpage');
        }
        try {
            $page_obj = App\Model\BookUtils::get_page_from_url($args);
            if ($page_obj == null) {
                $this->show_last_page();
                return;
            }
            FW\Tpl::prepend('title', '[编辑页面]-' . $page_obj->get_url() . '-');
            FW\Tpl::assign('breadcrumb', $page_obj->get_breadcrumb());
            FW\Tpl::display('/book/edit', $page_obj, $this->theme);
        } catch (FW\Exception $ex) {
            $this->show_last_page();
            return;
        }
    }

    private function c_list($args) {
        $page_obj = App\Model\BookUtils::get_page_from_url($args);
        if ($page_obj == null) {
            die();
        }
        if ($page_obj->is_null()) {
            die();
        }

        if ($page_obj->is_root()) {
            $system_obj = App\Model\System::get();
            $books = $system_obj->get_booklist();
            FW\Tpl::assign('is_book', true);
            FW\Tpl::assign('books', $books);
        } else {
            FW\Tpl::assign('is_book', false);
            $siblings = $page_obj->get_siblings_nodes('.md', 'App\\Model\\BookUtils::comp_dirfirst');
            FW\Tpl::assign('list', $siblings);
        }
        FW\Tpl::display('/book/list', $page_obj, $this->theme);
    }

    private function c_push($args) {
        $this->json_call($args, 'App\Model\BookUtils::push');
    }

    private function c_pull($args) {
        $this->json_call($args, 'App\Model\BookUtils::pull');
    }

    private function c_history($args) {
        $matches = [];
        if (!preg_match('/^\/(\d+)\/(.+)?$/', $args, $matches)) {
            $this->show_last_page();
            return;
        }
        $hist_page = intval($matches[1]);
        $url = strval($matches[2]);

        try {
            $page_obj = App\Model\BookUtils::get_page_from_url($url);
            if ($page_obj == null) {
                $this->show_last_page();
                return;
            }
            $data = $page_obj->get_history($hist_page);
            FW\Tpl::assign('data', $data);
            FW\Tpl::prepend('title', '[历史记录]' . $page_obj->get_url() . '-');
            FW\Tpl::display('/book/history', $page_obj, $this->theme);
        } catch (FW\Exception $ex) {
            $this->show_last_page();
            return;
        }
    }

    private function c_diff($args) {
        $matches = [];
        if (!preg_match('/^\/([0-9a-f]+)\/(.*)?$/', $args, $matches)) {
            $this->show_last_page();
            return;
        }
        $commit_hash = strval($matches[1]);
        $url = strval($matches[2]);
        try {
            $page_obj = App\Model\BookUtils::get_page_from_url($url);
            if ($page_obj == null) {
                $this->show_last_page();
                return;
            }
            $commit_obj = $page_obj->get_diff($commit_hash);
            FW\Tpl::assign('commit_obj', $commit_obj);
            $from = isset($_GET['from']) ? intval($_GET['from']) : 1;
            FW\Tpl::assign('from_page', $from);
            FW\Tpl::prepend('title', '[查询修改]' . $page_obj->get_url() . '-');
            FW\Tpl::display('/book/diff', $page_obj, $this->theme);
        } catch (FW\Exception $ex) {
            $this->show_last_page();
            return;
        }
    }

    private function c_movepage($args) {
        $this->show_last_page();
        return;
        if ($_POST) {
            $this->json_call($_POST, 'App\Model\BookUtils::movepage');
        }
        try {
            $page_obj = App\Model\BookUtils::get_page_from_url($args);
            if ($page_obj == null || !$page_obj->is_file()) {
                $this->show_last_page();
                return;
            }
            FW\Tpl::prepend('title', '[移动页面]-' . $page_obj->get_url() . '-');
            FW\Tpl::assign('breadcrumb', $page_obj->get_breadcrumb());
            FW\Tpl::display('/book/movepage', $page_obj, $this->theme);
        } catch (FW\Exception $ex) {
            $this->show_last_page();
            return;
        }
    }

    private function c_movedir($args) {
        $this->show_last_page();
        return;
    }

    private function c_addpage($args) {
        if ($_POST) {
            $this->json_call($_POST, 'App\Model\BookUtils::addpage');
        }
        try {
            $page_obj = App\Model\BookUtils::get_page_from_url($args, false, true);
            if ($page_obj == null) {
                $this->show_last_page();
                return;
            }
            $parent = $page_obj->get_parent();
            if ($parent == null) {
                $this->show_last_page();
                return;
            }
            FW\Tpl::prepend('title', '[新增页面]-' . $parent->get_url() . '-');
            FW\Tpl::assign('breadcrumb', $parent->get_breadcrumb());
            FW\Tpl::assign('parent', $parent);
            FW\Tpl::display('/book/addpage', $page_obj, $this->theme);
        } catch (FW\Exception $ex) {
            $this->show_last_page();
            return;
        }
    }

    private function c_addfile($args) {
        if ($_POST) {
            $this->json_call($_POST, 'App\Model\BookUtils::addfile');
        }
        try {
            $file_obj = App\Model\BookUtils::get_file_from_url($args);
            if ($file_obj == null) {
                $this->show_last_page();
                return;
            }
            FW\Tpl::prepend('title', '[新增页面]-' . $file_obj->get_url() . '-');
            FW\Tpl::assign('breadcrumb', $file_obj->get_breadcrumb());
            FW\Tpl::display('/book/addfile', $file_obj, $this->theme);
        } catch (FW\Exception $ex) {
            $this->show_last_page();
            return;
        }
    }

    private function c_adddir($args) {
        if ($_POST) {
            $this->json_call($_POST, 'App\Model\BookUtils::adddir');
        }
        try {
            $page_obj = App\Model\BookUtils::get_page_from_url($args, false, true);
            if ($page_obj == null) {
                $this->show_last_page();
                return;
            }
            $parent = $page_obj->get_parent();
            if ($parent == null) {
                $this->show_last_page();
                return;
            }
            FW\Tpl::prepend('title', '[新增目录]-' . $parent->get_url() . '-');
            FW\Tpl::assign('breadcrumb', $parent->get_breadcrumb());
            FW\Tpl::assign('parent', $parent);
            FW\Tpl::display('/book/adddir', $page_obj, $this->theme);
        } catch (FW\Exception $ex) {
            $this->show_last_page();
            return;
        }
    }

    private function c_addfiledir($args) {
        if ($_POST) {
            $this->json_call($_POST, 'App\Model\BookUtils::addfiledir');
        }
        try {
            $file_obj = App\Model\BookUtils::get_file_from_url($args);
            if ($file_obj == null) {
                $this->show_last_page();
                return;
            }
            FW\Tpl::prepend('title', '[新增目录]-' . $file_obj->get_url() . '-');
            FW\Tpl::assign('breadcrumb', $file_obj->get_breadcrumb());
            FW\Tpl::display('/book/addfiledir', $file_obj, $this->theme);
        } catch (FW\Exception $ex) {
            $this->show_last_page();
            return;
        }
    }

    private function c_delpage($args) {
        if ($_POST) {
            $this->json_call($_POST, 'App\Model\BookUtils::delpage');
        }
        try {
            $page_obj = App\Model\BookUtils::get_page_from_url($args);
            if ($page_obj == null || !$page_obj->is_file()) {
                $this->show_last_page();
                return;
            }
            FW\Tpl::prepend('title', '[删除页面]-' . $page_obj->get_url() . '-');
            FW\Tpl::assign('breadcrumb', $page_obj->get_breadcrumb());
            FW\Tpl::display('/book/delpage', $page_obj, $this->theme);
        } catch (FW\Exception $ex) {
            $this->show_last_page();
            return;
        }
    }

    private function c_delfile($args) {
        if ($_POST) {
            $this->json_call($_POST, 'App\Model\BookUtils::delfile');
        }
        try {
            $file_obj = App\Model\BookUtils::get_file_from_url($args);
            if ($file_obj == null || !$file_obj->is_file()) {
                $this->show_last_page();
                return;
            }
            FW\Tpl::prepend('title', '[删除文件]-' . $file_obj->get_url() . '-');
            FW\Tpl::assign('breadcrumb', $file_obj->get_breadcrumb());
            FW\Tpl::display('/book/delfile', $file_obj, $this->theme);
        } catch (FW\Exception $ex) {
            $this->show_last_page();
            return;
        }
    }

    private function c_deldir($args) {
        if ($_POST) {
            $this->json_call($_POST, 'App\Model\BookUtils::deldir');
        }
        try {
            $page_obj = App\Model\BookUtils::get_page_from_url($args);
            if ($page_obj == null || !$page_obj->is_dir()) {
                $this->show_last_page();
                return;
            }
            FW\Tpl::prepend('title', '[删除目录]-' . $page_obj->get_url() . '-');
            FW\Tpl::assign('breadcrumb', $page_obj->get_breadcrumb());
            FW\Tpl::display('/book/deldir', $page_obj, $this->theme);
        } catch (FW\Exception $ex) {
            $this->show_last_page();
            return;
        }
    }

    private function c_delfiledir($args) {
        if ($_POST) {
            $this->json_call($_POST, 'App\Model\BookUtils::delfiledir');
        }
        try {
            $file_obj = App\Model\BookUtils::get_file_from_url($args);
            if ($file_obj == null || !$file_obj->is_dir()) {
                $this->show_last_page();
                return;
            }
            FW\Tpl::prepend('title', '[删除目录]-' . $file_obj->get_url() . '-');
            FW\Tpl::assign('breadcrumb', $file_obj->get_breadcrumb());
            FW\Tpl::display('/book/delfiledir', $file_obj, $this->theme);
        } catch (FW\Exception $ex) {
            $this->show_last_page();
            return;
        }
    }

    private function show_last_page($bookname = '') {
        $system_obj = App\Model\System::get();
        $books = $system_obj->get_booklist(false);
        if ($bookname !== '' && isset($books[$bookname])) {
            $path = $books[$bookname]['last_page'];
            $system_obj->set_last_page('', $bookname);
            $this->redirect('/book/view/' . $bookname . '/' . $path);
            return;
        } else {
            $path = $system_obj->last_page;
            if ($path != '') {
                $system_obj->set_last_page('');
                $this->redirect('/book/view/' . $path);
                return;
            } else {
                if (count($books) > 0) {
                    $this->redirect('/book/view/' . key($books));
                } else {
                    $this->redirect('/book/open/');
                }
            }
        }
    }

}
