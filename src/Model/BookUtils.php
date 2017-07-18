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

    public static function edit_page($post) {
        if (!isset($post['page'])) {
            throw new FW\Exception('非法操作');
        }
        if (!isset($post['msg'])) {
            throw new FW\Exception('修改说明不能为空');
        }
        $path = strval($post['page']);
        $matches = [];
        if (!preg_match('/^([^\/]+)(\/(.+))?$/', $path, $matches)) {
            throw new FW\Exception('非法操作');
        }
        $book = strval($matches[1]);
        if (!isset($matches[3])) {
            throw new FW\Exception('非法操作');
        }
        $page = strval($matches[3]);
        $system_obj = Site\Model\System::get();
        $books = $system_obj->get_booklist();
        if (!isset($books[$book])) {
            throw new FW\Exception('笔记不存在');
        }
        try {
            $book_obj = new Book($books[$book]['path']);
            $book_obj->step_into($page);
            return $book_obj->edit_page(strval($post['content']), strval($post['msg']));
        } catch (FW\Exception $ex) {
            throw $ex;
        } catch (\RuntimeException $ex) {
            throw new FW\Exception('操作失败');
        }
    }

    public static function push($path) {
        $matches = [];
        if (!preg_match('/^([^\/]+)?$/', $path, $matches)) {
            throw new FW\Exception('非法操作');
        }
        $book = strval($matches[1]);
        $system_obj = Site\Model\System::get();
        $books = $system_obj->get_booklist();
        if (!isset($books[$book])) {
            throw new FW\Exception('笔记不存在');
        }
        try {
            $book_obj = new Book($books[$book]['path']);
            return $book_obj->git_cmd('push');
        } catch (FW\Exception $ex) {
            throw $ex;
        } catch (\RuntimeException $ex) {
            throw new FW\Exception('操作失败');
        }
    }

    public static function pull($path) {
        $matches = [];
        if (!preg_match('/^([^\/]+)?$/', $path, $matches)) {
            throw new FW\Exception('非法操作');
        }
        $book = strval($matches[1]);
        $system_obj = Site\Model\System::get();
        $books = $system_obj->get_booklist();
        if (!isset($books[$book])) {
            throw new FW\Exception('笔记不存在');
        }
        try {
            $book_obj = new Book($books[$book]['path']);
            return $book_obj->git_cmd('pull');
        } catch (FW\Exception $ex) {
            throw $ex;
        } catch (\RuntimeException $ex) {
            throw new FW\Exception('操作失败');
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
