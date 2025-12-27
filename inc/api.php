<?php
if (!defined('ABSPATH'))
    exit;

require_once ORBITUR_PLUGIN_DIR . 'inc/logger.php';

/* ------------------------------------------------------------
 * Logger fallback
 * ------------------------------------------------------------ */
if (!function_exists('orbitur_log')) {
    function orbitur_log($msg)
    {
        if (defined('WP_DEBUG') && WP_DEBUG && defined('ORBITUR_LOG')) {
            @file_put_contents(
                ORBITUR_LOG,
                '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL,
                FILE_APPEND | LOCK_EX
            );
        }
    }
}

/* ============================================================
 * LOGIN (SOURCE OF idSession)
 * ============================================================ */
function orbitur_moncomp_login($email, $password)
{
    $endpoint = get_option('orbitur_moncomp_endpoint');
    if (!$endpoint) {
        return new WP_Error('no_endpoint', 'Endpoint not configured');
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" '
        . 'xmlns:web="http://webservices.multicamp.fr">'
        . '<soapenv:Body>'
        . '<web:login>'
        . '<RqLogin>'
        . '<id>' . htmlspecialchars($email, ENT_XML1) . '</id>'
        . '<pw>' . htmlspecialchars($password, ENT_XML1) . '</pw>'
        . '<app>siteMarchand</app>'
        . '</RqLogin>'
        . '</web:login>'
        . '</soapenv:Body>'
        . '</soapenv:Envelope>';

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $xml,
        CURLOPT_HTTPHEADER => [
            'Content-Type: text/xml; charset=utf-8',
            'SOAPAction: "http://webservices.multicamp.fr/login"', // ← REQUIRED
        ],
        CURLOPT_TIMEOUT => 25,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    $response = curl_exec($ch);
    $curlErr = curl_error($ch);
    curl_close($ch);

    if (!$response) {
        return new WP_Error('curl_error', $curlErr ?: 'Empty response');
    }

    // orbitur_log("LOGIN RAW RESPONSE:\n" . $response);

    // Handle MonCompte error codes
    if (preg_match('/<error>(\d+)<\/error>/', $response, $m) && (int) $m[1] !== 0) {
        preg_match('/<messError[^>]*>(.*?)<\/messError>/is', $response, $e);
        return new WP_Error('login_failed', trim($e[1] ?? 'Login failed'));
    }

    // Extract idSession
    if (preg_match('/<idSession>([^<]+)<\/idSession>/', $response, $m)) {
        return ['idSession' => trim($m[1])];
    }

    return new WP_Error('no_session', 'No idSession returned');
}

/* ============================================================
 * RESET PASSWORD (SEND EMAIL)
 * ============================================================ */
if (!function_exists('orbitur_moncomp_reset_password')) {
    function orbitur_moncomp_reset_password($email)
    {
        $endpoint = get_option('orbitur_moncomp_endpoint');
        if (!$endpoint) {
            return new WP_Error('no_endpoint', 'Endpoint not configured');
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" '
            . 'xmlns:web="http://webservices.multicamp.fr">'
            . '<soapenv:Body>'
            . '<web:resetPassword>'
            . '<RqResetPassword>'
            . '<id><![CDATA[' . $email . ']]></id>'
            . '</RqResetPassword>'
            . '</web:resetPassword>'
            . '</soapenv:Body>'
            . '</soapenv:Envelope>';

        $ch = curl_init($endpoint);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $xml,
            CURLOPT_HTTPHEADER => [
                'Content-Type: text/xml; charset=utf-8',
                'SOAPAction: "http://webservices.multicamp.fr/resetPassword"',
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_TIMEOUT => 30,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            return new WP_Error('empty_response', 'Empty resetPassword response');
        }

        // Remove namespaces
        $clean = preg_replace('/(<\/?)[a-zA-Z0-9\-_]+:/', '$1', $response);
        $xmlObj = simplexml_load_string($clean);

        if (!$xmlObj) {
            return new WP_Error('parse_failed', 'Invalid resetPassword XML');
        }

        $body = $xmlObj->Body ?? null;

        if (isset($body->Fault)) {
            return new WP_Error(
                'soap_fault',
                (string) ($body->Fault->faultstring ?? 'SOAP Fault')
            );
        }

        $result = $body->resetPasswordResponse->result ?? null;

        if (!$result) {
            return new WP_Error('invalid_response', 'Invalid resetPassword response');
        }

        if ((int) $result->error !== 0) {
            return new WP_Error(
                'reset_failed',
                (string) ($result->messError ?? 'Reset password failed')
            );
        }

        return true;
    }
}
function orbitur_moncomp_reset_password_with_token($token, $newPw)
{
    $endpoint = get_option('orbitur_moncomp_endpoint');

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" '
        . 'xmlns:web="http://webservices.multicamp.fr">'
        . '<soapenv:Body>'
        . '<web:resetPassword>'
        . '<RqResetPassword>'
        . '<token><![CDATA[' . $token . ']]></token>'
        . '<newPw><![CDATA[' . $newPw . ']]></newPw>'
        . '</RqResetPassword>'
        . '</web:resetPassword>'
        . '</soapenv:Body>'
        . '</soapenv:Envelope>';

    $res = wp_remote_post($endpoint, [
        'headers' => [
            'Content-Type' => 'text/xml; charset=utf-8',
            'SOAPAction' => 'resetPassword',
        ],
        'body' => $xml,
        'timeout' => 30,
    ]);

    if (is_wp_error($res)) {
        return $res;
    }

    $body = wp_remote_retrieve_body($res);

    // Remove namespaces
    $clean = preg_replace('/(<\/?)[a-zA-Z0-9\-_]+:/', '$1', $body);
    $xmlObj = simplexml_load_string($clean);

    if (!$xmlObj) {
        return new WP_Error('invalid_xml', 'Resposta inválida');
    }

    $result = $xmlObj->Body->resetPasswordResponse->result ?? null;

    if (!$result || (int) $result->error !== 0) {
        return new WP_Error(
            'reset_failed',
            (string) ($result->messError ?? 'Erro ao redefinir palavra-passe')
        );
    }

    return true;
}

// Change PASSWORD (WSDL SAFE)
function orbitur_moncomp_update_password($idSession, $oldPw, $newPw)
{
    $endpoint = get_option('orbitur_moncomp_endpoint');
    if (!$endpoint) {
        return new WP_Error('no_endpoint', 'Endpoint not configured');
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" '
        . 'xmlns:web="http://webservices.multicamp.fr">'
        . '<soapenv:Body>'
        . '<web:updatePw>'
        . '<RqUpdatePw>'
        . '<idSession><![CDATA[' . $idSession . ']]></idSession>'
        . '<oldPw><![CDATA[' . $oldPw . ']]></oldPw>'
        . '<newPw><![CDATA[' . $newPw . ']]></newPw>'
        . '</RqUpdatePw>'
        . '</web:updatePw>'
        . '</soapenv:Body>'
        . '</soapenv:Envelope>';

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $xml,
        CURLOPT_HTTPHEADER => [
            'Content-Type: text/xml; charset=utf-8',
            'SOAPAction: "http://webservices.multicamp.fr/updatePw"',
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 30,
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        return new WP_Error('empty_response', 'Empty updatePw response');
    }

    $clean = preg_replace('/(<\/?)[a-zA-Z0-9\-_]+:/', '$1', $response);
    $xmlObj = simplexml_load_string($clean);

    if (!$xmlObj) {
        return new WP_Error('parse_failed', 'Invalid updatePw XML');
    }

    $result = $xmlObj->Body->updatePwResponse->result ?? null;

    if (!$result) {
        return new WP_Error('invalid_response', 'Invalid updatePw response');
    }

    if ((int) $result->error !== 0) {
        return new WP_Error(
            'update_failed',
            (string) ($result->messError ?? 'Password update failed')
        );
    }

    return true;
}

/* ============================================================
 * GET BOOKINGS
 * ============================================================ */
if (!function_exists('orbitur_getBookingList_raw')) {
    function orbitur_getBookingList_raw($idSession)
    {

        $endpoint = get_option('orbitur_moncomp_endpoint', '');
        if (!$endpoint) {
            return new WP_Error('no_endpoint', 'Endpoint not configured');
        }

        $xml =
            '<?xml version="1.0" encoding="UTF-8"?>' .
            '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://webservices.multicamp.fr">' .
            '<SOAP-ENV:Body>' .
            '<ns1:getBookingList>' .
            '<RqGetBookingList>' .
            '<idSession>' . esc_html($idSession) . '</idSession>' .
            '<lg>pt</lg>' .
            '<chosenList>3</chosenList>' .
            '<maxResults>500</maxResults>' .
            '</RqGetBookingList>' .
            '</ns1:getBookingList>' .
            '</SOAP-ENV:Body>' .
            '</SOAP-ENV:Envelope>';

        $res = wp_remote_post($endpoint, [
            'headers' => [
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => 'http://webservices.multicamp.fr/getBookingList'
            ],
            'body' => $xml,
            'timeout' => 30,
        ]);

        if (is_wp_error($res))
            return $res;

        return wp_remote_retrieve_body($res);
    }
}

/* ============================================================
 * PARSERS
 * ============================================================ */
if (!function_exists('orbitur_parse_booking_xml_string')) {
    function orbitur_parse_booking_xml_string($xml)
    {

        if (!$xml)
            return new WP_Error('empty', 'Empty XML');

        $clean = preg_replace('/(<\/?)[a-z0-9\-_]+:/i', '$1', $xml);
        libxml_use_internal_errors(true);
        $sx = simplexml_load_string($clean);
        if (!$sx)
            return new WP_Error('parse_failed', 'XML parse failed');

        return json_decode(json_encode($sx), true);
    }
}

if (!function_exists('orbitur_split_bookings_list')) {
    function orbitur_split_bookings_list($parsed)
    {

        $items = [];
        $iter = new RecursiveIteratorIterator(new RecursiveArrayIterator((array) $parsed));
        foreach ($iter as $k => $v) {
            if ($k === 'idOrder') {
                $items[] = $iter->getSubIterator()->getArrayCopy();
            }
        }

        $up = [];
        $past = [];

        foreach ($items as $it) {
            $begin = $it['begin'] ?? null;
            $t = $begin ? strtotime(substr($begin, 0, 10)) : 0;
            if ($t >= strtotime('today'))
                $up[] = $it;
            else
                $past[] = $it;
        }

        return ['upcoming' => $up, 'past' => $past];
    }
}

/* ============================================================
 * GET PERSON / OCC STATUS
 * ============================================================ */
function orbitur_moncomp_get_person($idSession)
{
    $endpoint = get_option('orbitur_moncomp_endpoint');
    if (!$endpoint) {
        return new WP_Error('no_endpoint', 'Endpoint not configured');
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" '
        . 'xmlns:web="http://webservices.multicamp.fr">'
        . '<soapenv:Body>'
        . '<web:getPerson>'
        . '<RqGetPerson>'
        . '<idSession><![CDATA[' . $idSession . ']]></idSession>'
        . '</RqGetPerson>'
        . '</web:getPerson>'
        . '</soapenv:Body>'
        . '</soapenv:Envelope>';

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $xml,
        CURLOPT_HTTPHEADER => [
            'Content-Type: text/xml; charset=utf-8',
            'SOAPAction: "http://webservices.multicamp.fr/getPerson"',
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_TIMEOUT => 25,
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        return new WP_Error('empty_response', 'Empty getPerson response');
    }

    // Remove namespaces safely
    $clean = preg_replace('/(<\/?)[a-zA-Z0-9\-_]+:/', '$1', $response);
    $xmlObj = simplexml_load_string($clean);

    if (!$xmlObj) {
        return new WP_Error('parse_failed', 'Invalid XML');
    }

    $result = $xmlObj->Body->getPersonResponse->result ?? null;
    if (!$result) {
        return new WP_Error('no_result', 'No result node');
    }

    $person = $result->person ?? null;
    if (!$person) {
        return new WP_Error('no_person', 'No person node');
    }

    /* ============================
     * PHONE (xsi:nil SAFE)
     * ============================ */
    $phone = '';
    $mobile = '';

    if (isset($person->mobilePhone) && trim((string) $person->mobilePhone) !== '') {
        $mobile = (string) $person->mobilePhone;
    }

    if (isset($person->phone) && trim((string) $person->phone) !== '') {
        $phone = (string) $person->phone;
    }

    $finalPhone = $mobile ?: $phone;

    /* ============================
     * OCC / FIDELITY (IMPORTANT)
     * ============================ */
    $expiry_raw = !empty($person->fidelityDate) ? (string) $person->fidelityDate : '';
    return [
        'first' => (string) $person->firstName,
        'last' => (string) $person->lastName,
        'email' => (string) $person->email,
        'address' => (string) $person->address1,
        'zipcode' => (string) $person->postCode,
        'city' => (string) $person->city,
        'country' => (string) $person->country,
        'phone' => $finalPhone,

        // OCC / Membership
        'occ_status' => ((string) $person->fidelity === 'true') ? 'active' : '',
        'occ_id' => !empty($result->idFid) ? (string) $result->idFid : '',
        'occ_valid' => $expiry_raw,
    ];
}
function orbitur_get_occ_status_from_moncomp($idSession)
{
    $person = orbitur_moncomp_get_person($idSession);
    if (is_wp_error($person)) {
        return $person;
    }

    // Get fidelity stream (start date)
    $fid = orbitur_moncomp_get_fid_stream($idSession);
    if (is_wp_error($fid)) {
        return [
            'has_membership' => false,
            'status' => 'inactive',
        ];
    }

    $start = substr($fid['begin'], 0, 10);
    $expiry = orbitur_compute_occ_expiry($fid['begin']);

    $active = false;
    if ($expiry) {
        $active = strtotime($expiry) >= strtotime(date('Y-m-d'));
    }

    if (!empty($person['occ_id'])) {
        return [
            'has_membership' => true,
            'member_number' => $person['occ_id'],
            'email' => $person['email'],
            'status' => $active ? 'active' : 'inactive',
            'start_date' => $start,
            'valid_until' => $expiry,
        ];
    }

    return [
        'has_membership' => false,
        'status' => 'inactive',
    ];
}

/**
 * Get Fidelity card details including start date
 */
function orbitur_moncomp_get_fid_stream($idSession)
{
    $endpoint = get_option('orbitur_moncomp_endpoint');
    if (!$endpoint) {
        return new WP_Error('no_endpoint', 'Endpoint not configured');
    }

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:web="http://webservices.multicamp.fr">'
        . '<soapenv:Body>'
        . '<web:getFidStream>'
        . '<RqGetFidStream>'
        . '<idSession><![CDATA[' . $idSession . ']]></idSession>'
        . '</RqGetFidStream>'
        . '</web:getFidStream>'
        . '</soapenv:Body>'
        . '</soapenv:Envelope>';

    $res = wp_remote_post($endpoint, [
        'headers' => [
            'Content-Type' => 'text/xml; charset=utf-8',
            'SOAPAction' => 'http://webservices.multicamp.fr/getFidStream',
        ],
        'body' => $xml,
        'timeout' => 30,
    ]);

    if (is_wp_error($res)) {
        return $res;
    }

    $body = wp_remote_retrieve_body($res);

    // Remove namespaces
    $clean = preg_replace('/(<\/?)[a-zA-Z0-9\-_]+:/', '$1', $body);
    $xmlObj = simplexml_load_string($clean);

    if (!$xmlObj) {
        return new WP_Error('parse_failed', 'Invalid getFidStream XML');
    }

    $result = $xmlObj->Body->getFidStreamResponse->result ?? null;
    if (!$result || empty($result->begin)) {
        return new WP_Error('no_fid', 'No FID stream found');
    }

    return [
        'begin' => (string) $result->begin,
    ];
}
function orbitur_compute_occ_expiry($begin)
{
    try {
        $start = new DateTime(substr($begin, 0, 10));
        $expiry = clone $start;
        $expiry->modify('+12 months');
        return $expiry->format('Y-m-d');
    } catch (Exception $e) {
        return null;
    }
}
/* ============================================================
 * CREATE ACCOUNT (REGISTER) — WSDL SAFE
 * ============================================================ */
function orbitur_moncomp_create_account(array $args)
{
    $endpoint = 'https://orbitur.multicamp.thelis.fr/MultiCampMonCompte/MLC_MonCompteServices';

    // Mandatory fields validation
    $required = ['email', 'password', 'first_name', 'last_name', 'civility', 'birthDate'];
    foreach ($required as $f) {
        if (empty($args[$f])) {
            return new WP_Error('missing_field', 'Missing field: ' . $f);
        }
    }

    // Password rules: MonCompte allows special chars, only length matters
    $password = trim($args['password']);
    if (strlen($password) < 6 || strlen($password) > 20) {
        return new WP_Error('invalid_password', 'Password must be 6–20 characters');
    }

    $birthDate = $args['birthDate'];

    $xml = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" '
        . 'xmlns:web="http://webservices.multicamp.fr">'
        . '<soapenv:Body>'
        . '<web:createAccount>'
        . '<RqCreateAccount_1>'
        . '<person>'
        . '<civility>' . esc_xml($args['civility']) . '</civility>'
        . '<firstName><![CDATA[' . $args['first_name'] . ']]></firstName>'
        . '<lastName><![CDATA[' . $args['last_name'] . ']]></lastName>'
        . '<address1><![CDATA[' . ($args['address'] ?? '') . ']]></address1>'
        . '<postCode><![CDATA[' . ($args['postcode'] ?? '') . ']]></postCode>'
        . '<city><![CDATA[' . ($args['city'] ?? '') . ']]></city>'
        . '<country><![CDATA[' . ($args['country'] ?? 'PT') . ']]></country>'
        . '<phone><![CDATA[' . ($args['phone'] ?? '') . ']]></phone>'
        . '<mobilePhone><![CDATA[' . ($args['mobile'] ?? '') . ']]></mobilePhone>'
        . '<identityNumber><![CDATA[' . ($args['id_number'] ?? '') . ']]></identityNumber>'
        . '<taxNumber><![CDATA[' . ($args['tax_number'] ?? '') . ']]></taxNumber>'
        . '<birthDate>' . esc_xml($birthDate) . '</birthDate>'
        . '<email><![CDATA[' . $args['email'] . ']]></email>'
        . '<idNationalityGrp><![CDATA[' . ($args['nationality'] ?? '') . ']]></idNationalityGrp>'
        . '<language>PT</language>'
        . '<newsLetter>' . (!empty($args['newsletter']) ? 'true' : 'false') . '</newsLetter>'
        . '</person>'
        . '<pw><![CDATA[' . $password . ']]></pw>'
        . '<fidelity>false</fidelity>'
        . '</RqCreateAccount_1>'
        . '</web:createAccount>'
        . '</soapenv:Body>'
        . '</soapenv:Envelope>';

    $res = wp_remote_post($endpoint, [
        'headers' => [
            'Content-Type' => 'text/xml; charset=utf-8',
            'SOAPAction' => 'createAccount',
        ],
        'body' => $xml,
        'timeout' => 30,
    ]);

    if (is_wp_error($res)) {
        return $res;
    }

    $body = wp_remote_retrieve_body($res);

    // SOAP Fault
    if (preg_match('/<faultstring[^>]*>([^<]+)<\/faultstring>/', $body, $m)) {
        return new WP_Error('soap_fault', trim($m[1]));
    }

    // Business error
    if (preg_match('/<error>(\d+)<\/error>/', $body, $m)) {
        if ((int) $m[1] !== 0) {
            preg_match('/<messError[^>]*>([^<]*)<\/messError>/', $body, $e);
            return new WP_Error('create_failed', $e[1] ?? 'Create account failed');
        }

        preg_match('/<idCustomer>(\d+)<\/idCustomer>/', $body, $id);
        return [
            'success' => true,
            'idCustomer' => $id[1] ?? null
        ];
    }

    return new WP_Error('invalid_response', 'Unrecognized createAccount response');
}
function orbitur_moncomp_update_person($idSession, $updates = [])
{
    $endpoint = get_option('orbitur_moncomp_endpoint');
    if (!$endpoint) {
        return new WP_Error('no_endpoint', 'Endpoint not configured');
    }

    $current = orbitur_moncomp_get_person($idSession);
    if (is_wp_error($current)) {
        return $current;
    }

    // Prepare data using correct MonCompte field names
    $data = [
        'firstName' => $current['first'],
        'lastName' => $current['last'],
        'email' => $updates['email'] ?? $current['email'],
        'address1' => $updates['address'] ?? $current['address'],
        'postCode' => $updates['zipcode'] ?? $current['zipcode'],
        'city' => $updates['city'] ?? $current['city'],
        'country' => $updates['country'] ?? $current['country'],
        'phone' => $updates['phone'] ?? $current['phone'],
    ];

    // RAW XML — Using RqUpdatePerson_1 and adding mandatory fidelity tag [cite: 583, 588]
    $xml = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" '
        . 'xmlns:web="http://webservices.multicamp.fr">'
        . '<soapenv:Header/>'
        . '<soapenv:Body>'
        . '<web:updatePerson>'
        . '<RqUpdatePerson_1>'
        . '<idSession>' . esc_xml($idSession) . '</idSession>'
        . '<person>'
        . '<firstName>' . esc_xml($data['firstName']) . '</firstName>'
        . '<lastName>' . esc_xml($data['lastName']) . '</lastName>'
        . '<address1>' . esc_xml($data['address1']) . '</address1>'
        . '<postCode>' . esc_xml($data['postCode']) . '</postCode>'
        . '<city>' . esc_xml($data['city']) . '</city>'
        . '<country>' . esc_xml($data['country']) . '</country>'
        . '<phone>' . esc_xml($data['phone']) . '</phone>'
        . '<email>' . esc_xml($data['email']) . '</email>'
        . '</person>'
        . '<fidelity>0</fidelity>' // Mandatory field 
        . '</RqUpdatePerson_1>'
        . '</web:updatePerson>'
        . '</soapenv:Body>'
        . '</soapenv:Envelope>';

    $res = wp_remote_post($endpoint, [
        'headers' => [
            'Content-Type' => 'text/xml; charset=utf-8',
            'SOAPAction' => 'updatePerson',
        ],
        'body' => $xml,
        'timeout' => 25,
    ]);

    if (is_wp_error($res)) {
        return $res;
    }

    $body = wp_remote_retrieve_body($res);

    // Check for success code 0 [cite: 576, 596]
    if (preg_match('/<error>0<\/error>/', $body)) {
        return true;
    }

    // Extract error message if it failed [cite: 641]
    preg_match('/<messError[^>]*>([^<]*)<\/messError>/', $body, $err);
    return new WP_Error('update_failed', $err[1] ?? 'Update failed at MonCompte');
}