<?php
if (!defined('ABSPATH')) exit;

/**
 * Config helper - prefer constants, fallback to options
 */
function orbitur_get_config() {
    $endpoint = defined('ORBITUR_MONCOMP_ENDPOINT') ? ORBITUR_MONCOMP_ENDPOINT : get_option('orbitur_moncomp_endpoint', '');
    $admin_email = defined('ORBITUR_MONCOMP_ADMIN_EMAIL') ? ORBITUR_MONCOMP_ADMIN_EMAIL : get_option('orbitur_moncomp_email','');
    $admin_pw = defined('ORBITUR_MONCOMP_ADMIN_PW') ? ORBITUR_MONCOMP_ADMIN_PW : get_option('orbitur_moncomp_password','');

    return [
        'endpoint' => rtrim($endpoint,'/'),
        'admin_email' => $admin_email,
        'admin_pw' => $admin_pw,
    ];
}

/**
 * Simple cURL SOAP wrapper
 */
function orbitur_call_soap($action, $xml_body, $timeout = 30) {
    $cfg = orbitur_get_config();
    if (empty($cfg['endpoint'])) {
        return new WP_Error('no_endpoint','No MonCompte endpoint configured.');
    }
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

    if (!empty($info['http_code']) && $info['http_code'] >= 400) {
        return new WP_Error('http_error','HTTP '.$info['http_code'], ['response'=>substr($res,0,2000),'info'=>$info]);
    }

    return $res;
}

/**
 * Login to MonCompte returns idSession string or WP_Error
 */
function orbitur_login_moncomp($email, $pw, $app = 'siteMarchand') {
    $xml_body = '<ns1:login>'
              . '<RqLogin>'
              . '<id>'.esc_html($email).'</id>'
              . '<pw>'.esc_html($pw).'</pw>'
              . '<app>'.esc_html($app).'</app>'
              . '</RqLogin>'
              . '</ns1:login>';

    $res = orbitur_call_soap('login', $xml_body);
    if (is_wp_error($res)) return $res;

    if (preg_match('/<idSession>([^<]+)<\\/idSession>/', $res, $m)) {
        return trim($m[1]);
    }

    // extract error message if present
    if (preg_match('/<messError[^>]*>(.*?)<\\/messError>/s', $res, $me)) {
        $msg = trim(strip_tags($me[1]));
        return new WP_Error('login_failed', $msg, ['raw'=>$res]);
    }

    return new WP_Error('no_session','No idSession returned', ['raw'=>$res]);
}

/**
 * Get bookings raw xml for idSession
 */
function orbitur_getBookingList_raw($idSession) {
    if (empty($idSession)) return new WP_Error('no_session','No idSession provided.');
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