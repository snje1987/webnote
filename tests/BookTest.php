<?php

/*
 * Copyright (C) 2016 Yang Ming <yangming0116@163.com>
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

namespace Org\Snje\WebnoteTest;

use Org\Snje\Webnote;

/**
 * Description of BookTest
 *
 * @author Yang Ming <yangming0116@163.com>
 */
class BookTest extends \PHPUnit_Framework_TestCase {

    public function test_parse_link() {
        $book = new Webnote\Book(dirname(__dir__) . '/tests_data/test');
        $book->set_path("/测试文件");
        $hash = [
            '[[F:1.jpg]]' => '/file/测试/1.jpg',
            '[[F:工作//1.jpg]]' => '/file/工作/1.jpg',
            '[[V:1.jpg]]' => '/view/测试/1.jpg',
            '[[V:工作//1.jpg]]' => '/view/工作/1.jpg',
        ];
        foreach ($hash as $k => $v) {
            $ret = $book->parse_link($k);
            $this->assertEquals($ret, $v);
        }
    }

}
