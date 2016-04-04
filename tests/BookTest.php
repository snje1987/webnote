<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of BookTest
 *
 * @author Yang Ming <yangming0116@163.com>
 */
class BookTest extends PHPUnit_Framework_TestCase {

    public function test_parse_link() {
        $book = new Org\Snje\Webnote\Book(dirname(__dir__) . '/tests_data/test');
        $book->show("/测试文件");
        $hash = [
            '[[F:1.jpg]]' => '/file/测试/1.jpg',
            '[[F:工作//1.jpg]]' => '/file/工作/1.jpg',
            '[[V:1.jpg]]' => '/view/测试/1.jpg',
            '[[V:工作//1.jpg]]' => '/view/工作/1.jpg',
        ];
        foreach($hash as $k => $v){
            $ret = $book->parse_link($k);
            $this->assertEquals($ret, $v);
        }
    }

}
