<?php
if (!defined('ABSPATH')) exit;

require_once ORBITUR_PLUGIN_DIR . 'inc/logger.php';

/**
 * Try MonCompte login using SOAP or HTTP. Returns array on success:
 *  ['idSession' => '...', 'customer' => [...]]
 */
function orbitur_moncomp_login($email, $pw) {
    $endpoint = get_option('orbitur_moncomp_endpoint', '');
    if (empty($endpoint)) {
        orbitur_log('moncomp_login: endpoint not configured');
        return new WP_Error('no_endpoint', 'MonCompte endpoint not configured');
    }

    // prefer SoapClient if present and the endpoint looks like WSDL
    try {
        if (class_exists('SoapClient')) {
            $wsdl = $endpoint;
            $opts = ['exceptions' => true, 'trace' => 1, 'cache_wsdl' => WSDL_CACHE_NONE];
            $client = new SoapClient($wsdl, $opts);
            $rq = ['id' => $email, 'pw' => $pw, 'app' => 'siteMarchand'];
            $res = $client->__soapCall('login', ['RqLogin' => $rq]);
            // parse response - be defensive
            $r = json_decode(json_encode($res), true);
            if (!empty($r['result']['idSession'])) {
                return ['idSession' => $r['result']['idSession'], 'raw' => $r];
            }
            return new WP_Error('mc_login_failed', 'Login failed', $r);
        } else {
            // fallback: craft SOAP envelope with wp_remote_post
            $soap_action = 'http://webservices.multicamp.fr/login';
            $xml = '<?xml version="1.0"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://webservices.multicamp.fr"><SOAP-ENV:Body><ns1:login><RqLogin><id>' . esc_html($email) . '</id><pw>' . esc_html($pw) . '</pw><app>siteMarchand</app></RqLogin></ns1:login></SOAP-ENV:Body></SOAP-ENV:Envelope>';
            $res = wp_remote_post($endpoint, [
                'headers' => ['Content-Type' => 'text/xml; charset=utf-8', 'SOAPAction' => $soap_action],
                'body' => $xml,
                'timeout' => 20,
            ]);
            if (is_wp_error($res)) return $res;
            $body = wp_remote_retrieve_body($res);
            if (preg_match('/<idSession>([^<]+)<\/idSession>/', $body, $m)) {
                return ['idSession' => $m[1], 'raw' => $body];
            }
            return new WP_Error('mc_login_failed', 'Login failed (no idSession)', $body);
        }
    } catch (Throwable $e) {
        orbitur_log('moncomp_login exception: ' . $e->getMessage());
        return new WP_Error('mc_exc', $e->getMessage());
    }
}

/**
 * Try to create account on MonCompte (non-blocking). Returns array or WP_Error.
 * Minimal example: depends on MonCompte WS.
 */
function orbitur_moncomp_create_account($uid, $data = []) {
    $endpoint = get_option('orbitur_moncomp_endpoint', '');
    if (empty($endpoint)) {
        return new WP_Error('no_endpoint', 'MonCompte endpoint not configured');
    }

    // For now we only log and return fake success — implement real SOAP create by reading your WSDL.
    orbitur_log("moncomp_create_account placeholder for user {$uid}");
    // return fake success (so UI proceeds). Replace with real call when ready.
    return ['customerId' => 'TEMP-' . $uid];
}

/**
 * Fetch raw booking XML (string) using idSession.
 */
function orbitur_getBookingList_raw($idSession) {
    $endpoint = get_option('orbitur_moncomp_endpoint', '');
    if (empty($endpoint)) return new WP_Error('no_endpoint', 'No endpoint');
    try {
        if (class_exists('SoapClient')) {
            $client = new SoapClient($endpoint, ['trace' => 1, 'exceptions' => 1]);
            $rq = ['idSession' => $idSession, 'lg' => 'pt', 'chosenList' => 3, 'maxResults' => 200];
            $res = $client->__soapCall('getBookingList', ['RqGetBookingList' => $rq]);
            $raw = json_encode($res);
            return $raw;
        } else {
            // fallback - craft SOAP body (this may need tuning)
            $soap_action = 'http://webservices.multicamp.fr/getBookingList';
            $xml = '<?xml version="1.0"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://webservices.multicamp.fr"><SOAP-ENV:Body><ns1:getBookingList><RqGetBookingList><idSession>' . esc_html($idSession) . '</idSession><lg>pt</lg><chosenList>3</chosenList><maxResults>200</maxResults></RqGetBookingList></ns1:getBookingList></SOAP-ENV:Body></SOAP-ENV:Envelope>';
            $res = wp_remote_post($endpoint, ['headers' => ['Content-Type' => 'text/xml; charset=utf-8', 'SOAPAction' => $soap_action], 'body' => $xml, 'timeout' => 20]);
            if (is_wp_error($res)) return $res;
            return wp_remote_retrieve_body($res);
        }
    } catch (Throwable $e) {
        return new WP_Error('soap_exc', $e->getMessage());
    }
}

/**
 * Parse booking XML string into PHP array — minimal / tolerant parser.
 */
function orbitur_parse_booking_xml_string($xmlString) {
    if (empty($xmlString)) return new WP_Error('empty', 'Empty');
    // convert to SimpleXML (suppress warnings)
    try {
        $clean = preg_replace('/(soapenv|SOAP-ENV):/','', $xmlString);
        $s = simplexml_load_string($clean);
        if ($s === false) return new WP_Error('parse_failed', 'XML parse failed');
        // convert to array (rough)
        $json = json_encode($s);
        $arr = json_decode($json, true);
        return $arr;
    } catch (Throwable $e) {
        return new WP_Error('parse_exc', $e->getMessage());
    }
}

/**
 * Split parsed booking array into 'upcoming' and 'past' (very naive).
 */
function orbitur_split_bookings_list($parsed) {
    // look for result > list > item
    $items = [];
    // try multiple paths depending on returned structure
    $maybe = $parsed;
    // dig for 'list'
    $result = null;
    foreach ($maybe as $k => $v) {
        if ($k === 'Body' || $k === 'soapenv:Body') {
            $result = $v;
            break;
        }
        if ($k === 'result' || $k === 'getBookingListResponse') {
            $result = $v;
            break;
        }
    }
    // fallback: flatten
    $flat = $parsed;
    // naive extraction: search recursively for 'item' arrays
    $itemsFound = [];
    $it = new RecursiveIteratorIterator(new RecursiveArrayIterator($parsed));
    foreach ($it as $key => $val) {
        if ($key === 'item' && is_array($val)) {
            $itemsFound[] = $val;
        }
    }
    // if not found, try deeper scan
    if (empty($itemsFound)) {
        // try to find any node that has 'idOrder' child
        $finder = function($arr) use (&$finder, &$itemsFound) {
            if (!is_array($arr)) return;
            if (isset($arr['idOrder'])) {
                $itemsFound[] = $arr;
                return;
            }
            foreach ($arr as $v) $finder($v);
        };
        $finder($parsed);
    }

    // normalize to upcoming/past based on begin date
    $up = []; $past = [];
    foreach ($itemsFound as $it) {
        $begin = $it['begin'] ?? null;
        $b = strtotime($begin ?: '');
        if ($b && $b >= strtotime('today')) $up[] = $it;
        else $past[] = $it;
    }
    return ['upcoming' => $up, 'past' => $past];
}