<?php

/**
 * @filename Info.php
 * @encoding UTF-8
 * @author Yang Ming <yangming0116@163.com>
 * @datetime 2016-2-28  14:26:03
 * @Description
 */

namespace Org\Snje\Webnote;
use Org\Snje\Minifw as FW;

class Info{

    protected $path = '';
    protected $data = [];
    protected static $_instance = null;

    public static function get($key, $default = ''){
        if(self::$_instance == null){
            self::$_instance = new Info();
        }
        if(isset(self::$_instance->data[$key])){
            return self::$_instance->data[$key];
        }
        return $default;
    }

    public static function set($key, $val){
        if(self::$_instance == null){
            self::$_instance = new Info();
        }
        self::$_instance->data[$key] = $val;
        return true;
    }

    public static function del($key){
        if(self::$_instance == null){
            self::$_instance = new Info();
        }
        if(isset(self::$_instance->data[$key])){
            unset(self::$_instance->data[$key]);
        }
        return true;
    }

    public static function save(){
        if(self::$_instance == null){
            return true;
        }
        return self::$_instance->save_data();
    }

    protected function save_data(){
        if($this->path == ''){
            return false;
        }
        $str = \Zend\Json\Json::encode($this->data);
        if(DEBUG){
            $str = \Zend\Json\Json::prettyPrint($str, ["indent" => "    "]);
        }
        FW\File::mkdir(dirname($this->path));
        return file_put_contents($this->path, $str);
    }


    private function __construct(){
        $dir = FW\Config::get('save','data');
        if($dir == ''){
            return;
        }
        $this->path = WEB_ROOT . $dir . '/info.json';
        if(!file_exists($this->path)){
            return;
        }
        $str = file_get_contents($this->path);
        $this->data = \Zend\Json\Json::decode($str, \Zend\Json\Json::TYPE_ARRAY);
    }
}
