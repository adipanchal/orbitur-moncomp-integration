<?php
if (!defined('ABSPATH'))
    exit;

/**
 * Simple file logger - append-only.
 * Usage: orbitur_log('message');
 */
function orbitur_log($msg)
{
    $f = defined('ORBITUR_LOG') ? ORBITUR_LOG : WP_CONTENT_DIR . '/uploads/orbitur.log';
    $time = date('[y-m-d H:i:s]');
    // ensure uploads exists
    $dir = dirname($f);
    if (!file_exists($dir)) {
        wp_mkdir_p($dir);
    }
    // cast non-string
    if (is_array($msg) || is_object($msg)) {
        $msg = print_r($msg, true);
    }
    @file_put_contents($f, "{$time} {$msg}\n", FILE_APPEND | LOCK_EX);
}