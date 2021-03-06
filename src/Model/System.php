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
 * Description of System
 *
 * @author Yang Ming <yangming0116@163.com>
 */
class System {

    /**
     * @var static the instance
     */
    protected static $_instance = null;

    /**
     * @return System
     */
    public static function get() {
        if (self::$_instance === null) {
            self::$_instance = new static();
        }
        return self::$_instance;
    }

    private $last_page;
    private $password;
    private $books;
    private $data_path;
    private $title;

    const SESSION_AUTH_KEY = 'auth';

    public function setpwd($post) {
        $pwd1 = $post['pwd1'];
        $pwd2 = $post['pwd2'];
        if ($pwd1 == '') {
            throw new FW\Exception('密码不能为空');
        }
        if ($pwd1 != $pwd2) {
            throw new FW\Exception('密码与确认密码不符');
        }
        $this->password = password_hash($pwd1, PASSWORD_DEFAULT);
        return $this->save();
    }

    public function chpwd($post) {
        $pwd = $post['pwd'];
        if ($pwd == '') {
            throw new FW\Exception('原密码不能为空');
        }
        if (!password_verify($pwd, $this->password)) {
            throw new FW\Exception('原密码不正确');
        }
        $pwd1 = $post['pwd1'];
        $pwd2 = $post['pwd2'];
        if ($pwd1 == '') {
            throw new FW\Exception('新密码不能为空');
        }
        if ($pwd1 != $pwd2) {
            throw new FW\Exception('密码与确认密码不符');
        }
        $this->password = password_hash($pwd1, PASSWORD_DEFAULT);
        return $this->save();
    }

    public function login($post) {
        $pwd = $post['pwd'];
        if ($pwd == '') {
            throw new FW\Exception('密码不能为空');
        }
        if (password_verify($pwd, $this->password)) {
            $_SESSION[self::SESSION_AUTH_KEY] = true;
            return true;
        }
        throw new FW\Exception('密码不正确');
    }

    public function logout() {
        $auth = FW\Config::get()->get_config('main', 'auth', true);
        if (!$auth) {
            return true;
        }
        $_SESSION[self::SESSION_AUTH_KEY] = false;
        return true;
    }

    public function clearcache() {
        FW\File::clear_dir(FW\Config::get()->get_config('path', 'compiled'));
        $fsencoding = FW\Config::get()->get_config('main', 'fsencoding', 'utf-8');
        foreach ($this->books as $name => $data) {
            FW\File::clear_dir($data['path'] . '/html', true, $fsencoding);
        }
        return true;
    }

    public function set_last_page($page, $book = null) {
        if ($book === null || !isset($this->books[$book])) {
            $this->last_page = $page;
        }
        else {
            $this->books[$book]['last_page'] = $page;
            $this->last_page = $book . '/' . $page;
        }
        $this->save();
    }

    public function add_book($name, $info) {
        if (isset($this->books[$name])) {
            throw new FW\Exception('笔记已存在');
        }
        if ($info['path'] == '') {
            throw new FW\Exception('未指定路径');
        }
        $this->books[$name] = [
            'path' => $info['path'],
        ];
        $this->save();
    }

    public function get_booklist($all = true) {
        $books = $this->books;
        if ($all == false) {
            $ret = [];
            foreach ($this->books as $k => $v) {
                if (!isset($v['disable']) || $v['disable'] != true) {
                    $ret[$k] = $v;
                }
            }
            $books = $ret;
        }
        return $books;
    }

    public function disable_book($name) {
        if (isset($this->books[$name])) {
            $this->books[$name]['disable'] = true;
        }
        $this->save();
        return true;
    }

    public function enable_book($name) {
        if (isset($this->books[$name])) {
            $this->books[$name]['disable'] = false;
        }
        $this->save();
        return true;
    }

    public function save() {
        if ($this->data_path == '') {
            return false;
        }
        $data = [];
        $data['books'] = $this->books;
        $data['last_page'] = $this->last_page;
        $data['password'] = $this->password;
        $data['title'] = $this->title;
        $str = \json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        FW\File::mkdir(dirname($this->data_path));
        return file_put_contents($this->data_path, $str);
    }

    public function __get($name) {
        if (!isset($this->$name)) {
            return '';
        }
        return $this->$name;
    }

    private function __construct() {
        $dir = FW\Config::get()->get_config('save', 'data');
        if ($dir == '') {
            return;
        }
        $this->data_path = WEB_ROOT . $dir . '/info.json';
        if (!file_exists($this->data_path)) {
            $this->save();
            return;
        }
        $str = file_get_contents($this->data_path);
        $data = \json_decode($str, true);
        $this->init($data);
    }

    private function init($data) {
        if (isset($data['last_page'])) {
            $this->last_page = $data['last_page'];
        }
        if (isset($data['password'])) {
            $this->password = $data['password'];
        }
        if (isset($data['title'])) {
            $this->title = $data['title'];
        } else {
            $this->title = '我的笔记';
        }
        $this->books = [];
        if (isset($data['books']) && is_array([$data['books']])) {
            foreach ($data['books'] as $k => $v) {
                if (!isset($v['path']) || $v['path'] == '') {
                    continue;
                }
                $this->books[$k] = [
                    'path' => $v['path'],
                    'disable' => isset($v['disable']) ? $v['disable'] : false,
                    'last_page' => isset($v['last_page']) ? $v['last_page'] : '',
                ];
            }
        }
    }

}
