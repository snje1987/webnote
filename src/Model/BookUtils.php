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

namespace App\Model;

use Org\Snje\Minifw as FW;
use App;

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
        $msg = null;
        if (isset($post['msg']) && $post['msg'] != '') {
            $msg = strval($post['msg']);
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
            return $new_page->addpage($post['content'], $msg);
        }
        catch (FW\Exception $ex) {
            throw $ex;
        }
        catch (\RuntimeException $ex) {
            throw new FW\Exception('操作失败');
        }
    }

    public static function addfile($post) {
        if (!isset($post['dir'])) {
            throw new FW\Exception('非法操作');
        }
        $msg = null;
        if (isset($post['msg']) && $post['msg'] != '') {
            $msg = strval($post['msg']);
        }
        $file_obj = self::get_file_from_url($post['dir']);
        if ($file_obj == null || !$file_obj->is_dir()) {
            throw new FW\Exception('非法操作');
        }
        if (!isset($_FILES) || !isset($_FILES['file'])) {
            throw new FW\Exception('非法操作');
        }
        $name = FW\File::get_name($_FILES['file'], true);
        try {
            $new_file = new BookFile(
                    $file_obj->get_book()
                    , $file_obj->get_path() . '/' . $name);
            return $new_file->upload($_FILES['file'], $msg);
        }
        catch (FW\Exception $ex) {
            throw $ex;
        }
        catch (\RuntimeException $ex) {
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
        }
        catch (FW\Exception $ex) {
            throw $ex;
        }
        catch (\RuntimeException $ex) {
            throw new FW\Exception('操作失败');
        }
    }

    public static function addfiledir($post) {
        if (!isset($post['dir'])) {
            throw new FW\Exception('非法操作');
        }
        if (!isset($post['name']) || $post['name'] == '') {
            throw new FW\Exception('目录名称不能为空');
        }
        $file_obj = self::get_file_from_url($post['dir']);
        if ($file_obj == null || !$file_obj->is_dir()) {
            throw new FW\Exception('非法操作');
        }

        try {
            $new_dir = new BookFile(
                    $file_obj->get_book()
                    , $file_obj->get_path() . '/' . strval($post['name']));
            return $new_dir->adddir();
        }
        catch (FW\Exception $ex) {
            throw $ex;
        }
        catch (\RuntimeException $ex) {
            throw new FW\Exception('操作失败');
        }
    }

    public static function editpage($post) {
        if (!isset($post['page'])) {
            throw new FW\Exception('非法操作');
        }
        $msg = null;
        if (isset($post['msg']) && $post['msg'] != '') {
            $msg = strval($post['msg']);
        }
        $page_obj = self::get_page_from_url($post['page']);
        if ($page_obj == null || !$page_obj->is_file()) {
            throw new FW\Exception('非法操作');
        }

        try {
            return $page_obj->edit(strval($post['content']), $msg);
        }
        catch (FW\Exception $ex) {
            throw $ex;
        }
        catch (\RuntimeException $ex) {
            throw new FW\Exception('操作失败');
        }
    }

    public static function delpage($post) {
        if (!isset($post['page'])) {
            throw new FW\Exception('非法操作');
        }
        $msg = null;
        if (isset($post['msg']) && $post['msg'] != '') {
            $msg = strval($post['msg']);
        }
        $page_obj = self::get_page_from_url($post['page']);
        if ($page_obj == null || !$page_obj->is_file()) {
            throw new FW\Exception('非法操作');
        }
        try {
            return $page_obj->delete($msg);
        }
        catch (FW\Exception $ex) {
            throw $ex;
        }
        catch (\RuntimeException $ex) {
            throw new FW\Exception('操作失败');
        }
    }

    public static function delfile($post) {
        if (!isset($post['file'])) {
            throw new FW\Exception('非法操作');
        }
        $msg = null;
        if (isset($post['msg']) && $post['msg'] != '') {
            $msg = strval($post['msg']);
        }
        $file_obj = self::get_file_from_url($post['file']);
        if ($file_obj == null || !$file_obj->is_file()) {
            throw new FW\Exception('非法操作');
        }
        try {
            return $file_obj->delete($msg);
        }
        catch (FW\Exception $ex) {
            throw $ex;
        }
        catch (\RuntimeException $ex) {
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
        }
        catch (FW\Exception $ex) {
            throw $ex;
        }
        catch (\RuntimeException $ex) {
            throw new FW\Exception('操作失败');
        }
    }

    public static function delfiledir($post) {
        if (!isset($post['dir'])) {
            throw new FW\Exception('非法操作');
        }
        $file_obj = self::get_file_from_url($post['dir']);
        if ($file_obj == null || !$file_obj->is_dir()) {
            throw new FW\Exception('非法操作');
        }
        try {
            return $file_obj->delete();
        }
        catch (FW\Exception $ex) {
            throw $ex;
        }
        catch (\RuntimeException $ex) {
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
        }
        catch (FW\Exception $ex) {
            throw $ex;
        }
        catch (\RuntimeException $ex) {
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
        }
        catch (FW\Exception $ex) {
            throw $ex;
        }
        catch (\RuntimeException $ex) {
            throw new FW\Exception('操作失败');
        }
    }

    /**
     * @return \App\Model\Book
     */
    public static function get_book_from_url($url) {
        $book_url = App\Model\BookUrl::create(strval($url));
        if ($book_url == null) {
            return null;
        }
        $book_obj = App\Model\Book::create($book_url);
        if ($book_obj == null) {
            return null;
        }
        return $book_obj;
    }

    /**
     * @return \App\Model\BookPage
     */
    public static function get_page_from_url($url, $allow_suggest = false, $allow_null = false) {
        $book_url = App\Model\BookUrl::create(strval($url));
        if ($book_url == null) {
            return null;
        }
        $book_obj = App\Model\Book::create($book_url);
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

    /**
     * @return \App\Model\BookFile
     */
    public static function get_file_from_url($url, $allow_suggest = false, $allow_null = false) {
        $book_url = App\Model\BookUrl::create(strval($url));
        if ($book_url == null) {
            return null;
        }
        $book_obj = App\Model\Book::create($book_url);
        if ($book_obj == null) {
            return null;
        }
        if ($allow_suggest) {
            return $book_obj->get_file($book_url, $allow_suggest);
        }
        elseif ($allow_null) {
            return new BookPage($book_obj, $book_url->get_page());
        }
        return $book_obj->get_file($book_url);
    }

    public static function dirname($page) {
        $page = \dirname($page);
        if ($page == '.' || $page == '\\') {
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
        }
        elseif ($a['dir'] === true) {
            return 1;
        }
        else {
            return -1;
        }
    }

    public static function comp_dirfirst($a, $b) {
        if ($a['dir'] == $b['dir']) {
            return strcmp($a['name'], $b['name']);
        }
        elseif ($a['dir'] === true) {
            return -1;
        }
        else {
            return 1;
        }
    }

    public static function parse_codebock($class, $code) {
        $cfg = FW\Config::get()->get_config('code', $class, []);
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
