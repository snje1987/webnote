<?php

/**
 * @filename Router.php
 * @encoding UTF-8
 * @author Yang Ming <yangming0116@163.com>
 * @datetime 2016-2-28  13:36:42
 * @Description
 */

namespace Org\Snje\Webnote;
use Org\Snje\Minifw as FW;

class Router{

    public function __construct(){
    }

    public function foramt($path){
        $path = Info::get('last_page', '');
        if($path != ''){
            Info::del('last_page');
            Info::save();
            FW\Server::redirect('/view/' . $path);
        }
        else{
            $books = Book::get_booklist(Book::Type_Enable);
            if(count($books) > 0){
                FW\Server::redirect('/view/' . key($books));
            }
            else{
                FW\Server::redirect('/open/');
            }
        }
        return true;
    }
}
