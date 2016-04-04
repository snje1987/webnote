<?php

namespace Org\Snje\Webnote;

use Org\Snje\Minifw as FW;
use Org\Snje\Webnote as WN;

require_once '../vendor/autoload.php';

$app = new FW\System([
    '/src/default.php',
    '/config.php'
        ]);
/*
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
 */

$app->reg_call('/^(.*)$/', [new Router(), 'dispatch']);

$app->run();
