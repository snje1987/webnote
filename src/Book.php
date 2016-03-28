<?php

/**
 * @filename Book.php
 * @encoding UTF-8
 * @author Yang Ming <yangming0116@163.com>
 * @datetime 2016-2-28  16:19:59
 * @Description
 */

namespace Org\Snje\Webnote;
use Org\Snje\Minifw as FW;

class Book{

    protected $path = '';
    protected $data = [];
    protected $cur_page = '';
    protected $cur_dir = '';
    protected $compiled = false;

    protected static $link_method = [
        'F' => 'file',
        'V' => 'view',
    ];

    const Type_All = 0;
    const Type_Enable = 1;

    public function __construct($path){
        $path = str_replace('\\', '/', $path);
        $this->path = strval($path);
        if(substr($this->path, -1) !== '/'){
            $this->path .= '/';
        }
        $info_file = $this->path . 'book.json';
        if(!file_exists($info_file)){
            throw new FW\Exception('笔记本不存在');
        }
        $str = file_get_contents($info_file);
        if($str === false){
            throw new FW\Exception('读取信息失败');
        }
        $this->data = \Zend\Json\Json::decode($str, \Zend\Json\Json::TYPE_ARRAY);
        $this->cur_page = '';
    }

    public function save(){
        $info_file = $this->path . 'book.json';
        $str = \Zend\Json\Json::encode($this->data);
        if(DEBUG){
            $str = \Zend\Json\Json::prettyPrint($str, ["indent" => "    "]);
        }
        return file_put_contents($info_file, $str);
    }

    public function show($page){
        $page = self::format_page($page);
        if($page != ''){//如果不为空，尝试显示页面
            $this->show_page($page);
        }
        else{
            //尝试显示笔记本中的第一个页面
            $page = $this->get_first_page();
            if($page != ''){
                FW\Server::redirect('/view/' . $this->data['name'] . '/' . $page);
            }
            //没有页面则显示空
            $this->cur_dir = $page;
            FW\Tpl::display('/view/empty', $this);
        }
    }

    public function open($post){
        $books = Info::get('books', []);
        if(isset($books[$this->data['name']])){
            throw new FW\Exception('笔记本已存在');
        }
        $books[$this->data['name']] = ['path' => $this->path];
        Info::set('books', $books);
        Info::save();
        return ['returl' => '/view/'.$this->data['name']];
    }

    public function get_html(){
        if(!$this->compiled){
            return '';
        }
        $dest = $this->path . 'html/' . $this->cur_page . '.html';
        if(file_exists($dest)){
            return file_get_contents($dest);
        }
        return '';
    }

    public function get_path(){
        $dir = $this->cur_dir;
        $path = [];
        while($dir != '' && $dir != '.'){
            $path[] = [
                'name' => self::basename($dir),
                'path' => $this->data['name'] . '/' . $dir
            ];
            $dir = dirname($dir);
        }
        $path[] = [
            'name' => $this->data['name'],
            'path' => $this->data['name']
        ];
        $path = array_reverse($path);
        return $path;
    }

    public function get_list(){
        $dir = $this->cur_dir;
        if($dir != ''){
            $dir .= '/';
        }
        $list = FW\File::ls($this->path . 'data/' . $dir);
        usort($list, __NAMESPACE__ . '\Book::comp_pagefirst');
        $pages = [];
        $dirs = [];
        foreach($list as $v){
            if($v['dir'] === true){
                $dirs[] = [
                    'name' => $v['name'],
                    'path' => $this->data['name'] . '/' . $dir . $v['name'],
                ];
            }
            else{
                if(strlen($v['name']) > 3 && substr($v['name'], -3) === '.md'){
                    $page = substr($v['name'], 0, strlen($v['name']) - 3);
                    $pages[] = [
                        'name' => $page,
                        'path' => $this->data['name'] . '/' . $dir . $page,
                    ];
                }

            }
        }
        return [
            'pages' => $pages,
            'dirs' => $dirs,
        ];
    }

    public function get_siblings($page){
        $page = self::format_page($page);
        $json = [];
        if($page !== ''){
            $parent = self::dirname($page);
            if($parent != ''){
                $parent .= '/';
            }
            $list = FW\File::ls($this->path . 'data/' . $parent);
            usort($list, __NAMESPACE__ . '\Book::comp_pagefirst');
            $dirs = [];
            foreach($list as $v){
                if($v['dir'] === true){
                    $dirs[] = [
                        'name' => $v['name'],
                        'path' => $this->data['name'] . '/' . $parent . $v['name'],
                    ];
                }
            }
            $json = $dirs;
        }
        echo \Zend\Json\Json::encode($json);
    }

    public function read_file($file){
        $file = self::format_page($file);
        if($file === ''){
            return true;
        }
        $path = $this->path . 'file/' . $file;
        if(file_exists($path)){
            $fi = new \finfo(FILEINFO_MIME_TYPE);
            $mime_type = $fi->file($path);
            header('Content-Type: '. $mime_type);
            readfile($path);
        }
        return true;
    }

    public static function comp_pagefirst($a, $b){
        if($a['dir'] == $b['dir']){
            return strcmp($a['name'], $b['name']);
        }
        elseif($a['dir'] === true){
            return -1;
        }
        else{
            return 1;
        }
    }

    public static function get_booklist($type = self::Type_All){
        $books = Info::get('books', []);
        if($type == self::Type_Enable){
            $ret = [];
            foreach($books as $k => $v){
                if(!isset($v['disable']) || $v['disable'] != true){
                    $ret[$k] = $v;
                }
            }
            $books = $ret;
        }
        return $books;
    }

    public static function disable_book($name){
        $books = Info::get('books', []);
        if(isset($books[$name])){
            $books[$name]['disable'] = true;
        }
        Info::set('books', $books);
        Info::save();
        return true;
    }

    public static function enable_book($name){
        $books = Info::get('books', []);
        if(isset($books[$name])){
            if(isset($books[$name]['disable'])){
                unset($books[$name]['disable']);
            }
        }
        Info::set('books', $books);
        Info::save();
        return true;
    }

    public static function format_page($page){
        if(strlen($page) > 0 && $page[0] == '/'){
            $page = substr($page, 1);
        }
        $page = str_replace('/../', '/', $page);
        return $page;
    }

    public static function dirname($page){
        $page = \dirname($page);
        if($page == '.'){
            $page = '';
        }
        return $page;
    }

    public static function basename($page){
        $pos = strrpos($page, '/');
        if($pos === false){
            return $page;
        }
        return substr($page, $pos + 1);
    }

    /*
     * 笔记本的内部引用链接，格式[[方法:笔记本名//路径]]
     */
    public function parse_link($link){
        $matches = [];
        if(!preg_match('/^\[\[(F|V):(([^\/]*)\/\/)?([^\]]*)\]\]$/i', $link, $matches)){
            return $link;
        }

        $method = $matches[1];
        if(!isset(self::$link_method[$method])){
            return $link;
        }
        $bookname = $matches[3] == '' ? $this->data['name'] : $matches[3];
        $page = $matches[4];

        return '/' . self::$link_method[$method] . '/' . $bookname . '/' . $page;
    }

    protected function show_page($page){
        if($this->show_md($page)){//先尝试显示指定名称的页面
            return true;
        }
        //如果页面不存在，则尝试显示指定名称的目录
        $file = $this->path . 'data/' . $page;
        if(is_dir($file)){
            $sub = $this->get_first_page('data/' . $page);
            if($sub != ''){//目录中存在页面就显示
                FW\Server::redirect('/view/' . $this->data['name'] . '/' . $page . '/' . $sub);
            }
            //不存在则显示空模板
            $this->cur_dir = $page;
            FW\Tpl::display('/view/empty', $this);
            return true;
        }
        //如果没有目录，则回退到上一级目录
        FW\Server::redirect('/view/' . $this->data['name'] . '/' . dirname($page));
    }

    protected function show_md($page){
        $src = $this->path . 'data/' . $page . '.md';
        if(!is_file($src)){
            return false;
        }
        $dest = $this->path . 'html/' . $page . '.html';
        $srctime = filemtime($src);
        $desttime = 0;
        if(file_exists($dest)){
            $desttime = filemtime($dest);
        }
        //只在更新后重新编译
        if($desttime == 0 || $desttime <= $srctime){
            $str = file_get_contents($src);

            $transform = new \Michelf\MarkdownExtra();
            $transform->url_filter_func = [$this, 'parse_link'];
            $str = $transform->transform($str);
            FW\File::mkdir(dirname($dest));
            file_put_contents($dest, $str);
        }
        $this->compiled = true;
        $this->cur_page = $page;
        $this->cur_dir = dirname($page);
        if($this->cur_dir == '.'){
            $this->cur_dir = '';
        }
        FW\Tpl::display('/view/page', $this);
        $this->data['last_page'] = $this->cur_page;
        $this->save();
        Info::set('last_page', $this->data['name'] . '/' . $this->cur_page);
        Info::save();
        return true;
    }

    protected function get_first_page($path = 'data/'){
        $list = FW\File::ls($this->path . $path);
        usort($list, __NAMESPACE__ . '\Book::comp_pagefirst');
        $dir = '';
        $page = '';
        foreach($list as $v){
            if($v['dir'] === true){
                $dir = ($dir === ''? $v['name'] : $dir);
            }
            else{
                if($page === '' && strlen($v['name']) > 3
                        && substr($v['name'], -3) === '.md'){
                    $page = substr($v['name'], 0, strlen($v['name']) - 3);
                    break;
                }
            }
        }
        return ($page === '' ? $dir : $page);
    }

}
