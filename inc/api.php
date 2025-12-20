<?php
if (!defined('ABSPATH'))
    exit;

require_once ORBITUR_PLUGIN_DIR . 'inc/logger.php';
function orbitur_get_moncomp_client()
{
    $endpoint = rtrim(get_option('orbitur_moncomp_endpoint'), '/');

    if (empty($endpoint)) {
        throw new Exception('MonCompte endpoint not configured');
    }

    return new SoapClient(
        null,
        [
            'location' => $endpoint,
            'uri' => 'http://webservices.multicamp.fr',
            'trace' => true,
            'exceptions' => true,
            'encoding' => 'UTF-8',
            'stream_context' => stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                ],
            ]),
        ]
    );
}
/* ============================================================
 * CONSTANTS / SAFETY
 * ============================================================ */
if (!defined('ORBITUR_MONCOMP_WSDL')) {
    $wsdl = get_option('orbitur_moncomp_endpoint', '');
    if ($wsdl) {
        define('ORBITUR_MONCOMP_WSDL', $wsdl);
    }
}

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

    orbitur_log("LOGIN RAW RESPONSE:\n" . $response);

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

        $endpoint = get_option('orbitur_moncomp_endpoint', '');
        if (!$endpoint) {
            return new WP_Error('no_endpoint', 'Endpoint not configured');
        }

        $xml =
            '<?xml version="1.0" encoding="UTF-8"?>' .
            '<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://webservices.multicamp.fr">' .
            '<SOAP-ENV:Body>' .
            '<ns1:resetPassword>' .
            '<Email>' . esc_html($email) . '</Email>' .
            '</ns1:resetPassword>' .
            '</SOAP-ENV:Body>' .
            '</SOAP-ENV:Envelope>';

        $res = wp_remote_post($endpoint, [
            'headers' => [
                'Content-Type' => 'text/xml; charset=utf-8',
                'SOAPAction' => 'http://webservices.multicamp.fr/resetPassword',
            ],
            'body' => $xml,
            'timeout' => 20,
        ]);

        if (is_wp_error($res))
            return $res;

        $body = wp_remote_retrieve_body($res);

        if (preg_match('/<messError[^>]*>([^<]+)<\/messError>/i', $body, $m)) {
            return new WP_Error('reset_failed', trim($m[1]));
        }

        return ['success' => true];
    }
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

    // Remove namespaces
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

    return [
        'first' => (string) $person->firstName,
        'last' => (string) $person->lastName,
        'email' => (string) $person->email,
        'address' => (string) $person->address1,
        'zipcode' => (string) $person->postCode,
        'city' => (string) $person->city,
        'country' => (string) $person->country,
        'phone' => (string) ($person->mobilePhone ?: $person->phone),

        // ✅ OCC — CORRECT
        'occ_status' => ((string) $person->fidelity === 'true') ? 'active' : '',
        'occ_id' => !empty($result->idFid) ? (string) $result->idFid : '',
        'occ_valid' => !empty($person->fidelityDate) ? (string) $person->fidelityDate : '',
    ];
}
/* ============================================================
 * CREATE ACCOUNT (REGISTER) — WSDL SAFE
 * ============================================================ */
function orbitur_moncomp_create_account(array $args)
{
    $endpoint = get_option('orbitur_moncomp_endpoint');
    if (!$endpoint) {
        return new WP_Error('no_endpoint', 'Endpoint not configured');
    }

    // DO NOT escape inside CDATA
    $xml = '<?xml version="1.0" encoding="UTF-8"?>'
        . '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" '
        . 'xmlns:web="http://webservices.multicamp.fr">'
        . '<soapenv:Body>'
        . '<web:createAccount>'
        . '<RqCreateAccount_1>'
        . '<person>'
        . '<civility>M</civility>'
        . '<firstName><![CDATA[' . $args['first_name'] . ']]></firstName>'
        . '<lastName><![CDATA[' . $args['last_name'] . ']]></lastName>'
        . '<address1><![CDATA[' . $args['address'] . ']]></address1>'
        . '<postCode>' . $args['postcode'] . '</postCode>'
        . '<city><![CDATA[' . $args['city'] . ']]></city>'
        . '<country>PT</country>'
        . '<phone>' . $args['phone'] . '</phone>'
        . '<email><![CDATA[' . $args['email'] . ']]></email>'
        . '<newsLetter>0</newsLetter>'
        . '</person>'
        . '<pw><![CDATA[' . $args['password'] . ']]></pw>'
        . '<fidelity>0</fidelity>'
        . '<app>siteMarchand</app>'
        . '</RqCreateAccount_1>'
        . '</web:createAccount>'
        . '</soapenv:Body>'
        . '</soapenv:Envelope>';

    $res = wp_remote_post($endpoint, [
        'headers' => [
            'Content-Type' => 'text/xml; charset=utf-8',
        ],
        'body' => $xml,
        'timeout' => 30,
    ]);

    if (is_wp_error($res)) {
        return $res;
    }

    $body = wp_remote_retrieve_body($res);

    if (preg_match('/<faultstring[^>]*>([^<]+)<\/faultstring>/', $body, $m)) {
        return new WP_Error('soap_fault', trim($m[1]));
    }

    if (preg_match('/<error>(\d+)<\/error>/', $body, $m) && (int) $m[1] !== 0) {
        preg_match('/<messError[^>]*>([^<]*)<\/messError>/', $body, $e);
        return new WP_Error('create_failed', $e[1] ?? 'Create failed');
    }

    return ['success' => true];
}
function orbitur_moncomp_update_person($idSession, array $data)
{
    $endpoint = get_option('orbitur_moncomp_endpoint');
    if (!$endpoint) {
        return new WP_Error('no_endpoint', 'Endpoint not configured');
    }

    // Only send fields that exist (VERY IMPORTANT)
    $personXml = '';

    if (!empty($data['address'])) {
        $personXml .= '<address1><![CDATA[' . $data['address'] . ']]></address1>';
    }
    if (!empty($data['zipcode'])) {
        $personXml .= '<postCode>' . $data['zipcode'] . '</postCode>';
    }
    if (!empty($data['city'])) {
        $personXml .= '<city><![CDATA[' . $data['city'] . ']]></city>';
    }
    if (!empty($data['country'])) {
        $personXml .= '<country>' . $data['country'] . '</country>';
    }
    if (!empty($data['phone'])) {
        $personXml .= '<phone>' . $data['phone'] . '</phone>';
    }

    if ($personXml === '') {
        return ['success' => true]; // Nothing to update
    }

    $xml =
        '<?xml version="1.0" encoding="UTF-8"?>' .
        '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" ' .
        'xmlns:web="http://webservices.multicamp.fr">' .
        '<soapenv:Body>' .
        '<web:updatePerson>' .
        '<RqUpdatePerson>' .
        '<idSession>' . htmlspecialchars($idSession, ENT_XML1) . '</idSession>' .
        '<person>' . $personXml . '</person>' .
        '<app>siteMarchand</app>' .
        '</RqUpdatePerson>' .
        '</web:updatePerson>' .
        '</soapenv:Body>' .
        '</soapenv:Envelope>';

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $xml,
        CURLOPT_HTTPHEADER => [
            'Content-Type: text/xml; charset=utf-8',
            'SOAPAction: "http://webservices.multicamp.fr/updatePerson"',
        ],
        CURLOPT_TIMEOUT => 25,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) {
        return new WP_Error('empty_response', 'Empty updatePerson response');
    }

    // Parse response safely
    $clean = preg_replace('/(<\/?)[a-zA-Z0-9\-_]+:/', '$1', $response);
    $xmlObj = simplexml_load_string($clean);

    if (!$xmlObj) {
        return new WP_Error('parse_failed', 'Invalid XML response');
    }

    $result = $xmlObj->Body->updatePersonResponse->result ?? null;
    if (!$result) {
        return new WP_Error('no_result', 'No result node');
    }

    if ((string) $result->error !== '0') {
        return new WP_Error(
            'moncomp_error',
            (string) ($result->messError ?? 'Update failed')
        );
    }

    return ['success' => true];
}