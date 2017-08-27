<?php
$value = [];
$value['/www/theme/def/script'] = [
    'method' => 'uglify',
    'type' => 'dir',
    'dep' => '/theme/def/script',
];
$value['/www/theme/def/style'] = [
    'method' => 'cssmin',
    'type' => 'dir',
    'dep' => '/theme/def/style',
];
return $value;
