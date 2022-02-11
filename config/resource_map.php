<?php

$value = [];
$value[] = [
    'method' => 'copy',
    'type' => 'file',
    'map' => [
        '/www/lib/jquery.js' => '/vendor/components/jquery/jquery.js',
        '/www/lib/jquery.min.js' => '/vendor/components/jquery/jquery.min.js',
        '/www/lib/jquery.min.map' => '/vendor/components/jquery/jquery.min.map',
        '/www/lib/jquery.form.min.js' => '/vendor/jquery-form/form/dist/jquery.form.min.js',
        '/www/lib/highlight.pack.min.js' => '/vendor/components/highlightjs/highlight.pack.min.js',
        '/www/lib/github.css' => '/vendor/components/highlightjs/styles/github.css',
    ],
];
$value[] = [
    'method' => 'copy',
    'type' => 'dir',
    'map' => [
        '/www/lib/bootstrap/' => '/vendor/twbs/bootstrap/dist/',
        '/www/lib/bootstrap-select/' => '/vendor/snapappointments/bootstrap-select/dist/',
        '/www/lib/font-awesome/css/' => '/vendor/fortawesome/font-awesome/css/',
        '/www/lib/font-awesome/fonts/' => '/vendor/fortawesome/font-awesome/fonts/',
    ],
];
return $value;
