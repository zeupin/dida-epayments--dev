<?php
require 'D:\Projects\github\phpcodeclean\src\Zeupin\PhpCodeClean.php';

// 初始化
$codeclean = new \Zeupin\PhpCodeClean();
$codeclean
    ->ignorePath(__FILE__)
    ->ignoreFile('.git');

// 设置target目录
if (mb_substr(__DIR__, -5) === '--dev') {
    $target = mb_substr(__DIR__, 0, -5);
    $codeclean->clean($target);
}
