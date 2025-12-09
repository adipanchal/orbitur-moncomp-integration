<?php
if (!defined('ABSPATH'))
    exit;
require_once ORBITUR_PLUGIN_DIR . 'inc/logger.php';
/* Logging helper if you have one; no-op fallback */
if (!function_exists('orbitur_log')) {
    function orbitur_log($msg)
    {
        if (defined('WP_DEBUG') && WP_DEBUG) {
            // write to plugin log if possible
            $f = defined('ORBITUR_LOG') ? ORBITUR_LOG : false;
            if ($f) {
                @file_put_contents($f, '[' . date('c') . '] ' . $msg . PHP_EOL, FILE_APPEND | LOCK_EX);
            }
        }
    }
}

/**
 * orbitur_moncomp_login: wrapper that does SOAP login
 * Use your existing working implementation. Return ['idSession'=>..., 'customer'=>...] or WP_Error.
 * If you already have an implementation, keep it; this is a safe stub.
 */
if (!function_exists('orbitur_moncomp_login')) {
    function orbitur_moncomp_login($email, $pw)
    {
        // delegate to curl fallback like your working test script
        $endpoint = get_option('orbitur_moncomp_endpoint', '');
        if (empty($endpoint))
            return new WP_Error('no_endpoint', 'Endpoint not set');

        // Accept either ?wsdl or service URL; most calls succeed with POSTing XML as done in tests.
        $xml = '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://webservices.multicamp.fr"><SOAP-ENV:Body><ns1:login><RqLogin><id>' . esc_html($email) . '</id><pw>' . esc_html($pw) . '</pw><app>siteMarchand</app></RqLogin></ns1:login></SOAP-ENV:Body></SOAP-ENV:Envelope>';

        $res = wp_remote_post($endpoint, [
            'headers' => [
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => 'http://webservices.multicamp.fr/login'
            ],
            'body' => $xml,
            'timeout' => 20
        ]);
        if (is_wp_error($res))
            return $res;
        $body = wp_remote_retrieve_body($res);

        // parse idSession if present
        if (preg_match('/<idSession(?:\s+xsi:nil="1")?>([^<]*)<\/idSession>/', $body, $m)) {
            $idSession = trim($m[1]);
            if ($idSession === '') {
                // maybe nil attribute => login failed, surface error
                if (preg_match('/<messError>([^<]+)<\/messError>/', $body, $m2)) {
                    return new WP_Error('login_failed', trim($m2[1]));
                }
                return new WP_Error('login_failed', 'No idSession returned');
            }
            return ['idSession' => $idSession, 'raw' => $body];
        }
        // fallback erb: return WP_Error with raw response
        return new WP_Error('soap_no_session', 'No idSession', $body);
    }
}

/**
 * Get BookingList raw response string using idSession
 */
if (!function_exists('orbitur_getBookingList_raw')) {
    function orbitur_getBookingList_raw($idSession)
    {
        $endpoint = get_option('orbitur_moncomp_endpoint', '');
        if (empty($endpoint))
            return new WP_Error('no_endpoint', 'Endpoint not set');

        $xml = '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://webservices.multicamp.fr"><SOAP-ENV:Body><ns1:getBookingList><RqGetBookingList><idSession>' . esc_html($idSession) . '</idSession><lg>pt</lg><chosenList>3</chosenList><maxResults>500</maxResults></RqGetBookingList></ns1:getBookingList></SOAP-ENV:Body></SOAP-ENV:Envelope>';

        $res = wp_remote_post($endpoint, [
            'headers' => [
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => 'http://webservices.multicamp.fr/getBookingList'
            ],
            'body' => $xml,
            'timeout' => 25
        ]);
        if (is_wp_error($res))
            return $res;
        return wp_remote_retrieve_body($res);
    }
}

/* Parsing helpers (if you already have them, keep them — guard redeclare) */
if (!function_exists('orbitur_parse_booking_xml_string')) {
    function orbitur_parse_booking_xml_string($xmlString)
    {
        if (empty($xmlString))
            return new WP_Error('empty', 'empty');
        // remove soap namespace prefixes to simplify
        $clean = preg_replace('/(<\/?)[a-z0-9\-_]+:/i', '$1', $xmlString);
        libxml_use_internal_errors(true);
        $s = simplexml_load_string($clean);
        if ($s === false) {
            $err = libxml_get_errors();
            libxml_clear_errors();
            return new WP_Error('parse_failed', 'XML parse failed');
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
        $it = new RecursiveIteratorIterator(new RecursiveArrayIterator((array) $parsed));
        foreach ($it as $key => $val) {
            if ($key === 'item' && is_array($val)) {
                // If item is associative vs numeric collection, normalize
                $itemsFound[] = $val;
            }
        }
        // Sometimes list->item is directly in nested arrays — attempt deeper scanning
        if (empty($itemsFound)) {
            $finder = function ($arr) use (&$finder, &$itemsFound) {
                if (!is_array($arr))
                    return;
                if (isset($arr['idOrder'])) {
                    $itemsFound[] = $arr;
                    return;
                }
                foreach ($arr as $v)
                    $finder($v);
            };
            $finder($parsed);
        }
        $up = [];
        $past = [];
        foreach ($itemsFound as $it) {
            $begin = $it['begin'] ?? ($it['start'] ?? null);
            $btime = $begin ? strtotime(substr($begin, 0, 10)) : 0;
            if ($btime && $btime >= strtotime('today'))
                $up[] = $it;
            else
                $past[] = $it;
        }
        return ['upcoming' => $up, 'past' => $past];
    }
}