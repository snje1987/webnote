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

namespace Org\Snje\Webnote\Model;

use Org\Snje\Minifw as FW;
use Org\Snje\Webnote as Site;

/**
 * Description of BookUtils
 *
 * @author Yang Ming <yangming0116@163.com>
 */
class BookUtils {

    public static function addpage($post) {
        if (!isset($post['dir'])) {
            throw new FW\Exception('非法操作');
        }
        if (!isset($post['msg']) || $post['msg'] == '') {
            throw new FW\Exception('修改说明不能为空');
        }
        if (!isset($post['name']) || $post['name'] == '') {
            throw new FW\Exception('页面名称不能为空');
        }
        $page_obj = self::get_page_from_url($post['dir']);
        if ($page_obj == null || !$page_obj->is_dir()) {
            throw new FW\Exception('非法操作');
        }

        try {
            $new_page = new BookPage(
                    $page_obj->get_book()
                    , $page_obj->get_path() . '/' . strval($post['name']));
            return $new_page->addpage($post['content'], $post['msg']);
        } catch (FW\Exception $ex) {
            throw $ex;
        } catch (\RuntimeException $ex) {
            throw new FW\Exception('操作失败');
        }
    }

    public static function adddir($post) {
        if (!isset($post['dir'])) {
            throw new FW\Exception('非法操作');
        }
        if (!isset($post['name']) || $post['name'] == '') {
            throw new FW\Exception('目录名称不能为空');
        }
        $page_obj = self::get_page_from_url($post['dir']);
        if ($page_obj == null || !$page_obj->is_dir()) {
            throw new FW\Exception('非法操作');
        }

        try {
            $new_page = new BookPage(
                    $page_obj->get_book()
                    , $page_obj->get_path() . '/' . strval($post['name']));
            return $new_page->adddir();
        } catch (FW\Exception $ex) {
            throw $ex;
        } catch (\RuntimeException $ex) {
            throw new FW\Exception('操作失败');
        }
    }

    public static function editpage($post) {
        if (!isset($post['page'])) {
            throw new FW\Exception('非法操作');
        }
        if (!isset($post['msg']) || $post['msg'] == '') {
            throw new FW\Exception('修改说明不能为空');
        }
        $page_obj = self::get_page_from_url($post['page']);
        if ($page_obj == null || !$page_obj->is_file()) {
            throw new FW\Exception('非法操作');
        }

        try {
            return $page_obj->edit(strval($post['content']), strval($post['msg']));
        } catch (FW\Exception $ex) {
            throw $ex;
        } catch (\RuntimeException $ex) {
            throw new FW\Exception('操作失败');
        }
    }

    public static function delpage($post) {
        if (!isset($post['page'])) {
            throw new FW\Exception('非法操作');
        }
        if (!isset($post['msg']) || $post['msg'] == '') {
            throw new FW\Exception('修改说明不能为空');
        }
        $page_obj = self::get_page_from_url($post['page']);
        if ($page_obj == null || !$page_obj->is_file()) {
            throw new FW\Exception('非法操作');
        }
        try {
            return $page_obj->delete($post['msg']);
        } catch (FW\Exception $ex) {
            throw $ex;
        } catch (\RuntimeException $ex) {
            throw new FW\Exception('操作失败');
        }
    }

    public static function deldir($post) {
        if (!isset($post['dir'])) {
            throw new FW\Exception('非法操作');
        }
        $page_obj = self::get_page_from_url($post['dir']);
        if ($page_obj == null || !$page_obj->is_dir()) {
            throw new FW\Exception('非法操作');
        }
        try {
            return $page_obj->delete();
        } catch (FW\Exception $ex) {
            throw $ex;
        } catch (\RuntimeException $ex) {
            throw new FW\Exception('操作失败');
        }
    }

    public static function push($path) {
        $book_obj = self::get_book_from_url($path);
        if ($book_obj == null) {
            throw new FW\Exception('非法操作');
        }
        try {
            return $book_obj->git_cmd('push');
        } catch (FW\Exception $ex) {
            throw $ex;
        } catch (\RuntimeException $ex) {
            throw new FW\Exception('操作失败');
        }
    }

    public static function pull($path) {
        $book_obj = self::get_book_from_url($path);
        if ($book_obj == null) {
            throw new FW\Exception('非法操作');
        }
        try {
            return $book_obj->git_cmd('pull');
        } catch (FW\Exception $ex) {
            throw $ex;
        } catch (\RuntimeException $ex) {
            throw new FW\Exception('操作失败');
        }
    }

    /**
     * @return \Org\Snje\Webnote\Model\Book
     */
    public static function get_book_from_url($url) {
        $book_url = Site\Model\BookUrl::create(strval($url));
        if ($book_url == null) {
            return null;
        }
        $book_obj = Site\Model\Book::create($book_url);
        if ($book_obj == null) {
            return null;
        }
        return $book_obj;
    }

    /**
     * @return \Org\Snje\Webnote\Model\BookPage
     */
    public static function get_page_from_url($url, $allow_suggest = false, $allow_null = false) {
        $book_url = Site\Model\BookUrl::create(strval($url));
        if ($book_url == null) {
            return null;
        }
        $book_obj = Site\Model\Book::create($book_url);
        if ($book_obj == null) {
            return null;
        }
        if ($allow_suggest) {
            return $book_obj->get_page($book_url, $allow_suggest);
        }
        elseif ($allow_null) {
            return new BookPage($book_obj, $book_url->get_page());
        }
        return $book_obj->get_page($book_url);
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

    public static function comp_pagefirst($a, $b) {
        if ($a['dir'] == $b['dir']) {
            return strcmp($a['name'], $b['name']);
        } elseif ($a['dir'] === true) {
            return 1;
        } else {
            return -1;
        }
    }

    public static function comp_dirfirst($a, $b) {
        if ($a['dir'] == $b['dir']) {
            return strcmp($a['name'], $b['name']);
        } elseif ($a['dir'] === true) {
            return -1;
        } else {
            return 1;
        }
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

}
