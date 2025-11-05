<?php
if (!defined('ABSPATH')) exit;

/**
 * orbitur_log - logs messages to WP error_log and plugin local log file
 * usage: orbitur_log('some message', $contextArrayOptional);
 */
function orbitur_log($msg, $context = null) {
    // WP error log
    if (is_array($context) || is_object($context)) {
        error_log('[Orbitur] ' . $msg . ' ' . print_r($context, true));
    } else {
        error_log('[Orbitur] ' . $msg . (is_string($context) ? ' ' . $context : ''));
    }

    // plugin local log (append)
    $dir = ORBITUR_PLUGIN_DIR;
    $file = $dir . 'orbitur.log';
    $txt = '[' . date('Y-m-d H:i:s') . '] ' . $msg;
    if (!empty($context)) $txt .= ' ' . print_r($context, true);
    $txt .= PHP_EOL;
    @file_put_contents($file, $txt, FILE_APPEND | LOCK_EX);
}