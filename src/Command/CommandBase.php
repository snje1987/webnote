<?php

namespace App\Command;

use Org\Snje\Minifw as FW;
use Org\Snje\Minifw\Exception;

class CommandBase {

    public static function print_table($cols, $body, $footer) {
        $max_len = [];
        $header = [];

        foreach ($cols as $k => $v) {
            $max_len[$k] = strlen($cols[$k]['name']);
            $header[$k] = $v['name'];
        }

        foreach ($footer as $k => $v) {
            if ($max_len[$k] < strlen($footer[$k])) {
                $max_len[$k] = strlen($footer[$k]);
            }
        }

        foreach ($body as $line) {
            foreach ($line as $k => $v) {
                if ($max_len[$k] < strlen($line[$k])) {
                    $max_len[$k] = strlen($line[$k]);
                }
            }
        }

        $line_count = 1;
        foreach ($max_len as $v) {
            $line_count += $v + 3;
        }

        $line_sep = str_repeat('-', $line_count) . "\n";

        self::print_line($cols, $header, $max_len, 'header');

        echo $line_sep;

        foreach ($body as $line) {
            self::print_line($cols, $line, $max_len, 'body');
        }

        echo $line_sep;

        self::print_line($cols, $footer, $max_len, 'footer');
    }

    public static function show_size($size) {
        $unit = ['', 'K', 'M', 'G'];
        $cur_unit = 0;
        while ($size > 1024 && $cur_unit < count($unit)) {
            $cur_unit ++;
            $size = bcdiv($size, 1024, 2);
        }
        return $size . $unit[$cur_unit];
    }

    public function dispatch($args) {
        $function = isset($args[0]) ? strval($args[0]) : '';
        if (empty($function)) {
            echo "未指定操作\n";
            return;
        }

        $function = 'cmd_' . $function;
        array_shift($args);

        if (is_callable([$this, $function])) {
            try {
                call_user_func([$this, $function], $args);
            }
            catch (\Exception $ex) {
                echo '[' . $ex->getCode() . '] ' . $ex->getFile() . '[' . $ex->getLine() . ']: ' . $ex->getMessage() . "\n";
            }
        }
        else {
            echo "操作不存在\n";
        }
        return;
    }

    //////////////////////

    protected static function print_line($cols, $line, $max_len, $type) {
        $first = true;
        $str = '';

        foreach ($cols as $name => $col) {
            if (!$first) {
                $str .= ' ';
            }
            $first = false;

            $align = isset($col['align_' . $type]) ? strval($col['align_' . $type]) : $col['align'];

            if ($align == 'left') {
                $pad = STR_PAD_RIGHT;
            }
            elseif ($align == 'right') {
                $pad = STR_PAD_LEFT;
            }
            else {
                $pad = STR_PAD_BOTH;
            }

            $str .= '| ' . str_pad($line[$name], $max_len[$name], ' ', $pad);
        }
        echo $str . " |\n";
    }

}
