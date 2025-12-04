<?php
if (!defined('ABSPATH'))
    exit;

function orbitur_log($msg)
{
    $time = date('[d-M-Y H:i:s]');
    $line = $time . ' ' . (is_string($msg) ? $msg : print_r($msg, true)) . PHP_EOL;
    @file_put_contents(ORBITUR_LOG, $line, FILE_APPEND | LOCK_EX);
}