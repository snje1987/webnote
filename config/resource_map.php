<?php
$value = [];
$value[] = [
    'method' => 'copy',
    'type' => 'file',
    'from' => [
        '/vendor/components/jquery/jquery.js',
        '/vendor/components/jquery/jquery.min.js',
        '/vendor/components/jquery/jquery.min.map',
        '/vendor/jquery-form/form/dist/jquery.form.min.js',
        '/vendor/components/highlightjs/highlight.pack.min.js',
        '/vendor/components/highlightjs/styles/github.css',
    ],
    'to' => [
        '/www/lib/jquery.js',
        '/www/lib/jquery.min.js',
        '/www/lib/jquery.min.map',
        '/www/lib/jquery.form.min.js',
        '/www/lib/highlight.pack.min.js',
        '/www/lib/github.css',
    ],
];
$value[] = [
    'method' => 'copy',
    'type' => 'dir',
    'from' => [
        '/vendor/twbs/bootstrap/dist',
        '/vendor/bootstrap-select/bootstrap-select/dist',
        '/vendor/fortawesome/font-awesome/css',
        '/vendor/fortawesome/font-awesome/fonts',
    ],
    'to' => [
        '/www/lib/bootstrap',
        '/www/lib/bootstrap-select',
        '/www/lib/font-awesome/css',
        '/www/lib/font-awesome/fonts',
    ],
];
$value[] = [
    'method' => 'cssmin',
    'type' => 'dir',
    'from' => '/theme/def/style',
    'to' => '/www/theme/def/style',
];
$value[] = [
    'method' => 'uglify',
    'type' => 'dir',
    'from' => '/theme/def/script',
    'to' => '/www/theme/def/script',
];
$value[] = [
    'method' => 'cssmin',
    'type' => 'dir',
    'from' => '/theme/def/style',
    'to' => '/www/theme/def/style',
];
return $value;
