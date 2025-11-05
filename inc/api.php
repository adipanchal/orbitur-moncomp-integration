<?php
if (!defined('ABSPATH')) exit;

/**
 * Read endpoint and admin test creds from options.
 * You can override by defining constants in wp-config.php:
 * ORBITUR_MONCOMP_ENDPOINT, ORBITUR_MONCOMP_ADMIN_EMAIL, ORBITUR_MONCOMP_ADMIN_PASSWORD
 */
function orbitur_get_config() {
    $endpoint = defined('ORBITUR_MONCOMP_ENDPOINT') ? ORBITUR_MONCOMP_ENDPOINT : get_option('orbitur_moncomp_endpoint', '');
    $email = defined('ORBITUR_MONCOMP_ADMIN_EMAIL') ? ORBITUR_MONCOMP_ADMIN_EMAIL : get_option('orbitur_moncomp_email','');
    $pw = defined('ORBITUR_MONCOMP_ADMIN_PW') ? ORBITUR_MONCOMP_ADMIN_PW : get_option('orbitur_moncomp_password','');
    return ['endpoint'=>$endpoint,'email'=>$email,'password'=>$pw];
}

/* ---------------- login ----------------
 * returns idSession string or WP_Error
 */
function orbitur_login($email, $pw) {
    $cfg = orbitur_get_config();
    $endpoint = $cfg['endpoint'];
    if (empty($endpoint)) return new WP_Error('no_endpoint','No MonCompte endpoint configured.');

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'
        .'<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://webservices.multicamp.fr">'
        .'<SOAP-ENV:Body>'
        .'<ns1:login>'
        .'<RqLogin>'
        .'<id>'.esc_html($email).'</id>'
        .'<pw>'.esc_html($pw).'</pw>'
        .'<app>siteMarchand</app>'
        .'</RqLogin>'
        .'</ns1:login>'
        .'</SOAP-ENV:Body>'
        .'</SOAP-ENV:Envelope>';

    $info = [];
    $res = orbitur_make_soap_request($endpoint, 'login', $xml, $info);
    if (is_wp_error($res)) return $res;

    // extract idSession
    if (preg_match('/<idSession>([^<]+)<\/idSession>/', $res, $m)) {
        return sanitize_text_field($m[1]);
    }
    // attempt to extract messError
    if (preg_match('/<messError[^>]*>(.*?)<\/messError>/', $res, $m2)) {
        return new WP_Error('login_failed', wp_strip_all_tags($m2[1]), ['raw'=>$res,'info'=>$info]);
    }
    return new WP_Error('no_idsession','No idSession returned', ['raw'=>$res,'info'=>$info]);
}

/* ---------------- findPersonWithEmail ----------------
 * returns true if person exists, false if not, or WP_Error
 */
function orbitur_findPersonWithEmail($email) {
    $cfg = orbitur_get_config();
    $endpoint = $cfg['endpoint'];
    if (empty($endpoint)) return new WP_Error('no_endpoint','No MonCompte endpoint configured.');

    // Build SOAP request for findPersonWithEmail
    $xml = '<?xml version="1.0" encoding="UTF-8"?>'
        .'<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://webservices.multicamp.fr">'
        .'<SOAP-ENV:Body>'
        .'<ns1:findPersonWithEmail>'
        .'<RqFindPersonWithEmail>'
        .'<email>'.esc_html($email).'</email>'
        .'</RqFindPersonWithEmail>'
        .'</ns1:findPersonWithEmail>'
        .'</SOAP-ENV:Body>'
        .'</SOAP-ENV:Envelope>';

    $info = []; $res = orbitur_make_soap_request($endpoint,'findPersonWithEmail',$xml,$info);
    if (is_wp_error($res)) return $res;
    // If response contains result->person or list, detect existence.
    if (stripos($res,'<result') !== false && stripos($res,'<error>0</error>') !== false) {
        // heuristics: if we find person node or id
        if (preg_match('/<personId>([^<]+)<\/personId>/i', $res)) return true;
        // sometimes returns list empty - detect "not found" via error codes if present
        // default assume false (not found)
    }
    // fallback: search for 'not found' words
    if (stripos($res,'not found') !== false || stripos($res,'aucun') !== false) return false;
    // if contains entries maybe true
    if (preg_match('/<idPerson[^>]*>/i', $res) || preg_match('/<personId>/i',$res)) return true;
    return false;
}

/* ---------------- createAccount ----------------
 * $payload associative array with fields required by createAccount
 * returns person id string or WP_Error
 */
function orbitur_createAccount(array $payload) {
    $cfg = orbitur_get_config();
    $endpoint = $cfg['endpoint'];
    if (empty($endpoint)) return new WP_Error('no_endpoint','No MonCompte endpoint configured.');

    // Map fields - adapt as required by MonCompte
    $firstname = isset($payload['firstname']) ? $payload['firstname'] : '';
    $lastname  = isset($payload['lastname']) ? $payload['lastname'] : '';
    $email     = isset($payload['email']) ? $payload['email'] : '';
    $password  = isset($payload['password']) ? $payload['password'] : '';

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'
        .'<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://webservices.multicamp.fr">'
        .'<SOAP-ENV:Body>'
        .'<ns1:createAccount>'
        .'<RqCreateAccount>'
        .'<firstname>'.esc_html($firstname).'</firstname>'
        .'<lastname>'.esc_html($lastname).'</lastname>'
        .'<email>'.esc_html($email).'</email>'
        .'<password>'.esc_html($password).'</password>'
        .'</RqCreateAccount>'
        .'</ns1:createAccount>'
        .'</SOAP-ENV:Body>'
        .'</SOAP-ENV:Envelope>';

    $info = []; $res = orbitur_make_soap_request($endpoint,'createAccount',$xml,$info);
    if (is_wp_error($res)) return $res;
    // try extract created person id (example tag name may vary)
    if (preg_match('/<personId>([^<]+)<\/personId>/', $res, $m)) return sanitize_text_field($m[1]);
    // if not, return raw success
    if (stripos($res,'<error>0</error>') !== false) return 'created';
    return new WP_Error('create_failed','CreateAccount did not return expected person id', ['raw'=>$res,'info'=>$info]);
}

/* ---------------- getPerson ----------------
 * pass idSession and returns parsed array or WP_Error
 */
function orbitur_getPerson($idSession) {
    $cfg = orbitur_get_config();
    $endpoint = $cfg['endpoint'];
    if (empty($endpoint)) return new WP_Error('no_endpoint','No MonCompte endpoint configured.');
    $xml = '<?xml version="1.0" encoding="UTF-8"?>'
        .'<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://webservices.multicamp.fr">'
        .'<SOAP-ENV:Body>'
        .'<ns1:getPerson>'
        .'<RqGetPerson>'
        .'<idSession>'.esc_html($idSession).'</idSession>'
        .'</RqGetPerson>'
        .'</ns1:getPerson>'
        .'</SOAP-ENV:Body>'
        .'</SOAP-ENV:Envelope>';

    $info=[]; $res = orbitur_make_soap_request($endpoint,'getPerson',$xml,$info);
    if (is_wp_error($res)) return $res;
    // parse into associative array (light)
    $sx = @simplexml_load_string($res);
    if (!$sx) return new WP_Error('parse_error','Failed to parse getPerson response', ['raw'=>$res]);
    $person = [];
    // try find person nodes - flexible parsing
    $nodes = $sx->xpath('//result') ?: $sx->xpath('//person') ?: [];
    if ($nodes) {
        $node = $nodes[0];
        foreach ($node as $k => $v) {
            $person[$k] = (string)$v;
        }
    }
    return $person;
}

/* ---------------- getBookingList (raw) ---------------- */
function orbitur_getBookingList_raw($idSession, $lg = 'pt', $chosenList = 3, $maxResults = 200) {
    $cfg = orbitur_get_config();
    $endpoint = $cfg['endpoint'];
    if (empty($endpoint)) return new WP_Error('no_endpoint','No MonCompte endpoint configured.');
    $xml = '<?xml version="1.0" encoding="UTF-8"?>'
        .'<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://webservices.multicamp.fr">'
        .'<SOAP-ENV:Body>'
        .'<ns1:getBookingList>'
        .'<RqGetBookingList>'
        .'<idSession>'.esc_html($idSession).'</idSession>'
        .'<lg>'.esc_html($lg).'</lg>'
        .'<chosenList>'.intval($chosenList).'</chosenList>'
        .'<maxResults>'.intval($maxResults).'</maxResults>'
        .'</RqGetBookingList>'
        .'</ns1:getBookingList>'
        .'</SOAP-ENV:Body>'
        .'</SOAP-ENV:Envelope>';
    $info=[]; $res = orbitur_make_soap_request($endpoint,'getBookingList',$xml,$info);
    if (is_wp_error($res)) return $res;
    return $res;
}