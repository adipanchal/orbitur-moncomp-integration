<?php
if (!defined('ABSPATH')) exit;

/**
 * Get plugin config (prefer constants, fallback to options)
 */
function orbitur_get_config() {
    $endpoint = defined('ORBITUR_MONCOMP_ENDPOINT') ? ORBITUR_MONCOMP_ENDPOINT : get_option('orbitur_moncomp_endpoint', '');
    return [
        'endpoint' => untrailingslashit(trim($endpoint)),
    ];
}

/**
 * Generic SOAP call via cURL
 * @param string $action SOAP action name (getBookingList, login, etc)
 * @param string $xml_body XML content that goes inside the <SOAP-ENV:Body>
 * @return string|WP_Error raw response or error
 */
function orbitur_call_soap($action, $xml_body, $timeout = 30) {
    $cfg = orbitur_get_config();
    if (empty($cfg['endpoint'])) return new WP_Error('no_endpoint', 'MonCompte endpoint not configured.');

    $endpoint = $cfg['endpoint'];

    $envelope = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://webservices.multicamp.fr">'
        . '<SOAP-ENV:Body>'
        . $xml_body
        . '</SOAP-ENV:Body>'
        . '</SOAP-ENV:Envelope>';

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: text/xml; charset=utf-8',
        'Content-Length: '.strlen($envelope),
        'SOAPAction: "http://webservices.multicamp.fr/'.$action.'"'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $envelope);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $res = curl_exec($ch);
    $err = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    if ($res === false) {
        return new WP_Error('curl_error', $err, $info);
    }

    if (isset($info['http_code']) && $info['http_code'] >= 400) {
        return new WP_Error('http_error', 'HTTP '.$info['http_code'], ['response'=>substr($res,0,4000),'info'=>$info]);
    }

    return $res;
}

/**
 * Fetch raw booking list XML string for a given idSession
 * @param string $idSession
 * @return string|WP_Error
 */
function orbitur_getBookingList_raw($idSession) {
    if (empty($idSession)) return new WP_Error('no_session', 'No MonCompte session provided.');
    $xml_body = '<ns1:getBookingList>'
              . '<RqGetBookingList>'
              . '<idSession>'.esc_html($idSession).'</idSession>'
              . '<lg>pt</lg>'
              . '<chosenList>3</chosenList>'
              . '<maxResults>200</maxResults>'
              . '</RqGetBookingList>'
              . '</ns1:getBookingList>';
    return orbitur_call_soap('getBookingList', $xml_body);
}

/**
 * Login to MonCompte (returns idSession or WP_Error)
 * @param string $email
 * @param string $pw
 * @param string $app (optional)
 * @return string|WP_Error
 */
function orbitur_login_moncomp($email, $pw, $app = 'siteMarchand') {
    if (empty($email) || empty($pw)) return new WP_Error('missing_credentials', 'Missing credentials');
    $xml_body = '<ns1:login>'
              . '<RqLogin>'
              . '<id>'.esc_html($email).'</id>'
              . '<pw>'.esc_html($pw).'</pw>'
              . '<app>'.esc_html($app).'</app>'
              . '</RqLogin>'
              . '</ns1:login>';
    $res = orbitur_call_soap('login', $xml_body);
    if (is_wp_error($res)) return $res;

    // try to extract idSession
    if (preg_match('/<idSession>([^<]+)<\\/idSession>/', $res, $m)) {
        return trim($m[1]);
    }

    // Attempt to find error message
    if (preg_match('/<error>(\d+)<\\/error>/', $res, $e)) {
        $errcode = $e[1];
        if (preg_match('/<messError([^>]*)>(.*?)<\\/messError>/s', $res, $me)) {
            $msg = strip_tags($me[2]);
            return new WP_Error('moncomp_login_error', $msg, ['raw' => $res, 'code'=>$errcode]);
        }
    }

    return new WP_Error('no_session_returned', 'No idSession returned', ['raw'=>$res]);
}