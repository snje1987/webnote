<?php
namespace Org\Snje\Webnote;
use Org\Snje\Minifw as FW;
use Org\Snje\Webnote as WN;

define('WEB_ROOT', dirname(__DIR__));
define('CFG_FILE', '/config.php');

require_once WEB_ROOT . '/vendor/autoload.php';

$app = new FW\System();

$app->reg_call('/^\/view\/([^\/]*)((\/.*)?)$/', function($book, $page){
    $books = Book::get_booklist();
    try{
        $book = urldecode(strval($book));
        $page = urldecode($page);
        if(!isset($books[$book])){
            FW\Server::redirect('/');
        }
        $book_obj = new Book($books[$book]['path']);
        FW\Env::set('title', $book . $page);
        $book_obj->show($page);
        Book::enable_book($book);
    } catch (FW\Exception $ex) {//只有笔记本不存在的时候才会抛出异常
        Book::disable_book($book);
        FW\Server::redirect('/');
    }
    return true;
});

$app->reg_call('/^\/siblings\/([^\/]*)((\/.*)?)$/', function($book, $page){
    $books = Book::get_booklist();
    try{
        $book = urldecode(strval($book));
        $page = urldecode($page);
        if(!isset($books[$book])){
            die();
        }
        $book_obj = new Book($books[$book]['path']);
        $book_obj->get_siblings($page);
    } catch (FW\Exception $ex) {//只有笔记本不存在的时候才会抛出异常
        Book::disable_book($book);
        die();
    }
    return true;
});

$app->reg_call('/^\/file\/([^\/]*)((\/.*)?)$/', function($book, $file){
    $books = Book::get_booklist();
    try{
        $book = urldecode(strval($book));
        $file = urldecode($file);
        if(!isset($books[$book])){
            die();
        }
        $book_obj = new Book($books[$book]['path']);
        $book_obj->read_file($file);
    } catch (FW\Exception $ex) {//只有笔记本不存在的时候才会抛出异常
        Book::disable_book($book);
        die();
    }
    return true;
});

$app->reg_call('/^\/open\/$/', function(){
    if($_POST){
        FW\Common::json_call(['returl' => '/'], [new Book($_POST['path']), 'open']);
    }
    FW\Env::set('title', "打开笔记本");
    FW\Tpl::display('/open', []);
    return true;
});

$app->reg_call('/^(.*)$/', function($path){
    $router = new namespace\Router();
    if(!$router->foramt($path)){
        FW\Server::show_404();
    }
    return true;
});

$app->run();
