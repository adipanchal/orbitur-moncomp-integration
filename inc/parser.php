<?php
if (!defined('ABSPATH'))
    exit;

/**
 * Functions to parse booking XML / split upcoming/past
 */

if (!function_exists('orbitur_parse_booking_xml_string')) {
    function orbitur_parse_booking_xml_string($xmlString)
    {
        if (empty($xmlString))
            return new WP_Error('empty', 'Empty');
        // try to extract any XML portion if $xmlString might be json-encoded (SoapClient returns object converted to json earlier)
        if (strpos($xmlString, '{') === 0 || strpos($xmlString, '[') === 0) {
            // likely json representation; try decode then re-encode to xml? fallback: return as array
            $arr = json_decode($xmlString, true);
            if (is_array($arr))
                return $arr;
            return new WP_Error('parse_failed', 'JSON decode failed');
        }

        // sanitize namespace prefixes
        $clean = preg_replace('/(<\/?)([a-z0-9\-\_]+):/i', '$1', $xmlString);
        libxml_use_internal_errors(true);
        $s = simplexml_load_string($clean);
        if ($s === false) {
            $errs = libxml_get_errors();
            libxml_clear_errors();
            return new WP_Error('parse_failed', 'XML parse failed: ' . json_encode($errs));
        }
        $json = json_encode($s);
        $arr = json_decode($json, true);
        return $arr;
    }
}

if (!function_exists('orbitur_split_bookings_list')) {
    function orbitur_split_bookings_list($parsed)
    {
        $itemsFound = [];

        // recursive finder
        $finder = function ($arr) use (&$finder, &$itemsFound) {
            if (!is_array($arr))
                return;
            // items may be under 'item' keys or direct arrays with 'idOrder'
            foreach ($arr as $k => $v) {
                if ($k === 'item' && is_array($v)) {
                    // item could be single or array of items
                    if (array_keys($v) !== range(0, count($v) - 1)) {
                        // associative -> single item
                        $itemsFound[] = $v;
                    } else {
                        // numeric indexed
                        foreach ($v as $one) {
                            $itemsFound[] = $one;
                        }
                    }
                } elseif (is_array($v)) {
                    $finder($v);
                } else {
                    // skip
                }
            }
        };

        $finder($parsed);

        // fallback: find nodes with 'idOrder'
        if (empty($itemsFound)) {
            $finder2 = function ($arr) use (&$finder2, &$itemsFound) {
                if (!is_array($arr))
                    return;
                if (isset($arr['idOrder'])) {
                    $itemsFound[] = $arr;
                    return;
                }
                foreach ($arr as $v)
                    $finder2($v);
            };
            $finder2($parsed);
        }

        $up = [];
        $past = [];
        $today = strtotime('today');
        foreach ($itemsFound as $it) {
            $begin = $it['begin'] ?? ($it['dateBegin'] ?? null);
            if ($begin) {
                // remove timezone extra if present
                $b = strtotime($begin);
            } else {
                $b = 0;
            }
            if ($b && $b >= $today)
                $up[] = $it;
            else
                $past[] = $it;
        }

        return ['upcoming' => $up, 'past' => $past];
    }
}
