<?php
if (!defined('ABSPATH')) {
    exit;
}

if (!function_exists('orbitur_log')) {
    function orbitur_log($msg)
    {
        $time = gmdate('Y-m-d H:i:s');
        $line = "[{$time}] {$msg}\n";
        // ensure directory exists
        $file = defined('ORBITUR_LOG') ? ORBITUR_LOG : WP_CONTENT_DIR . '/uploads/orbitur.log';
        $dir = dirname($file);
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }
        // append
        @file_put_contents($file, $line, FILE_APPEND | LOCK_EX);
    }
}
