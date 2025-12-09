<?php
if (!defined('ABSPATH'))
    exit;
require_once ORBITUR_PLUGIN_DIR . 'inc/logger.php';

/**
 * orbitur_moncomp_login - try login using SoapClient (preferred) or wp_remote_post fallback
 * Returns array('idSession'=>'...','customer'=>[...]) or WP_Error.
 */
if (!function_exists('orbitur_moncomp_login')) {
    function orbitur_moncomp_login($email, $pw)
    {
        $endpoint = get_option('orbitur_moncomp_endpoint', '');
        if (empty($endpoint)) {
            orbitur_log('moncomp_login: endpoint not configured');
            return new WP_Error('no_endpoint', 'MonCompte endpoint not configured');
        }

        // Accept either WSDL URL or endpoint without ?wsdl
        $wsdl = $endpoint;
        try {
            if (defined('ORBITUR_FORCE_NO_SOAP') && ORBITUR_FORCE_NO_SOAP) {
                throw new Exception('FORCE_NO_SOAP');
            }
            if (class_exists('SoapClient')) {
                $opts = ['exceptions' => true, 'trace' => 1, 'cache_wsdl' => WSDL_CACHE_NONE];
                $client = new SoapClient($wsdl, $opts);
                $rq = ['id' => $email, 'pw' => $pw, 'app' => 'siteMarchand'];
                $res = $client->__soapCall('login', ['RqLogin' => $rq]);
                $r = json_decode(json_encode($res), true);
                // defensive
                $idSession = $r['result']['idSession'] ?? ($r['idSession'] ?? '');
                if (!empty($idSession)) {
                    orbitur_log("moncomp_login: success for {$email}");
                    return ['idSession' => $idSession, 'customer' => $r['result'] ?? $r];
                }
                orbitur_log('moncomp_login: no idSession in soap response: ' . print_r($r, true));
                return new WP_Error('mc_login_failed', 'Login failed', $r);
            }
        } catch (Throwable $e) {
            orbitur_log('moncomp_login exception (soap): ' . $e->getMessage());
            // fallback to HTTP SOAP envelope below
        }

        // fallback via wp_remote_post
        try {
            $soap_action = 'http://webservices.multicamp.fr/login';
            $xml = '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://webservices.multicamp.fr"><SOAP-ENV:Body><ns1:login><RqLogin><id>' . esc_html($email) . '</id><pw>' . esc_html($pw) . '</pw><app>siteMarchand</app></RqLogin></ns1:login></SOAP-ENV:Body></SOAP-ENV:Envelope>';
            $args = [
                'headers' => ['Content-Type' => 'text/xml; charset=utf-8', 'SOAPAction' => $soap_action],
                'body' => $xml,
                'timeout' => 30,
            ];
            $res = wp_remote_post($endpoint, $args);
            if (is_wp_error($res)) {
                orbitur_log('moncomp_login http error: ' . $res->get_error_message());
                return $res;
            }
            $body = wp_remote_retrieve_body($res);
            if (preg_match('/<idSession[^>]*>([^<]+)<\/idSession>/', $body, $m)) {
                $idSession = $m[1];
                orbitur_log("moncomp_login (http): success idSession={$idSession}");
                return ['idSession' => $idSession, 'raw' => $body];
            }
            orbitur_log('moncomp_login http: no idSession in body, snippet: ' . substr($body, 0, 400));
            return new WP_Error('mc_login_failed', 'Login failed (no idSession)', $body);
        } catch (Throwable $e) {
            orbitur_log('moncomp_login http exception: ' . $e->getMessage());
            return new WP_Error('mc_exc', $e->getMessage());
        }
    }
}

/**
 * orbitur_moncomp_create_account - placeholder (should be implemented against MonCompte SOAP)
 * Returns WP_Error or array with customerId.
 */
if (!function_exists('orbitur_moncomp_create_account')) {
    function orbitur_moncomp_create_account($uid, $data = [])
    {
        orbitur_log("moncomp_create_account placeholder for user {$uid}");
        return ['customerId' => 'TEMP-' . $uid];
    }
}

/**
 * orbitur_getBookingList_raw - fetch raw XML result for a given idSession
 */
if (!function_exists('orbitur_getBookingList_raw')) {
    function orbitur_getBookingList_raw($idSession)
    {
        $endpoint = get_option('orbitur_moncomp_endpoint', '');
        if (empty($endpoint))
            return new WP_Error('no_endpoint', 'No endpoint');

        // Try SoapClient first
        try {
            if (defined('ORBITUR_FORCE_NO_SOAP') && ORBITUR_FORCE_NO_SOAP) {
                throw new Exception('FORCE_NO_SOAP');
            }
            if (class_exists('SoapClient')) {
                $client = new SoapClient($endpoint, ['trace' => 1, 'exceptions' => 1]);
                $rq = ['idSession' => $idSession, 'lg' => 'pt', 'chosenList' => 3, 'maxResults' => 200];
                $res = $client->__soapCall('getBookingList', ['RqGetBookingList' => $rq]);
                // convert to xml string (safe)
                $raw = json_encode($res);
                return $raw;
            }
        } catch (Throwable $e) {
            orbitur_log('getBookingList soap exception: ' . $e->getMessage());
            // fallback
        }

        // fallback: http soap envelope
        $soap_action = 'http://webservices.multicamp.fr/getBookingList';
        $xml = '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://webservices.multicamp.fr"><SOAP-ENV:Body><ns1:getBookingList><RqGetBookingList><idSession>' . esc_html($idSession) . '</idSession><lg>pt</lg><chosenList>3</chosenList><maxResults>200</maxResults></RqGetBookingList></ns1:getBookingList></SOAP-ENV:Body></SOAP-ENV:Envelope>';
        $res = wp_remote_post($endpoint, [
            'headers' => ['Content-Type' => 'text/xml; charset=utf-8', 'SOAPAction' => $soap_action],
            'body' => $xml,
            'timeout' => 30,
        ]);
        if (is_wp_error($res)) {
            orbitur_log('getBookingList http error: ' . $res->get_error_message());
            return $res;
        }
        return wp_remote_retrieve_body($res);
    }
}
