<?php
if (!defined('ABSPATH')) exit;

require_once ORBITUR_PLUGIN_DIR . 'inc/logger.php';

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
 * SOAP call wrapper with logging and optional stub mode
 */
function orbitur_call_soap($action, $xml_body, $timeout = 30) {
    $cfg = orbitur_get_config();

    // STUB MODE: useful for local development
    if ( defined('ORBITUR_MONCOMP_STUB') && ORBITUR_MONCOMP_STUB ) {
        orbitur_log("STUB SOAP action={$action}");
        if ($action === 'login') {
            return '<?xml version="1.0"?><SOAP-ENV:Envelope><SOAP-ENV:Body><ns1:loginResponse xmlns:ns1="http://webservices.multicamp.fr"><result><error>0</error><idSession>stub-session-123</idSession></result></ns1:loginResponse></SOAP-ENV:Body></SOAP-ENV:Envelope>';
        }
        if ($action === 'getBookingList') {
            return '<?xml version="1.0"?><SOAP-ENV:Envelope><SOAP-ENV:Body><ns1:getBookingListResponse xmlns:ns1="http://webservices.multicamp.fr"><result><error>0</error><list><item><idOrder>U-STUB-1</idOrder><site>STUB_SITE</site><begin>2025-12-01T00:00:00+00:00</begin><end>2025-12-02T00:00:00+00:00</end><url>https://example.com/booking</url></item></list></result></ns1:getBookingListResponse></SOAP-ENV:Body></SOAP-ENV:Envelope>';
        }
        return '<?xml version="1.0"?><SOAP-ENV:Envelope><SOAP-ENV:Body><result><error>0</error></result></SOAP-ENV:Body></SOAP-ENV:Envelope>';
    }

    if (empty($cfg['endpoint'])) {
        orbitur_log('No endpoint configured.');
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

    orbitur_log("SOAP action={$action} http_code=" . ($info['http_code'] ?? 'n/a') . " curl_err=" . ($err ? $err : 'none'));
    $snippet = $res ? substr($res,0,3000) : 'NO_RESPONSE';
    orbitur_log("SOAP response snippet: " . $snippet);

    if ($res === false) {
        return new WP_Error('curl_error', $err, $info);
    }
    if (!empty($info['http_code']) && $info['http_code'] >= 400) {
        return new WP_Error('http_error','HTTP '.$info['http_code'], ['response'=>substr($res,0,2000),'info'=>$info]);
    }

    return $res;
}

/**
 * Login helper
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