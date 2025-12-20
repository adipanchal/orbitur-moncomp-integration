<?php
if (!defined('ABSPATH'))
    exit;

/**
 * Low-level SOAP HTTP POST via cURL.
 * Returns raw response string or WP_Error.
 */
function orbitur_make_soap_request($endpoint, $soap_action, $xml_body, &$info_out = null)
{
    if (empty($endpoint)) {
        return new WP_Error('no_endpoint', 'No endpoint configured');
    }

    $headers = [
        'Content-Type: text/xml; charset=utf-8',
        'Accept: text/xml',
        'SOAPAction: http://webservices.multicamp.fr/' . $soap_action,
    ];

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $xml_body,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_ENCODING => '',
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_HEADER => true,
    ]);

    $response = curl_exec($ch);
    $err = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);

    if ($response === false) {
        $info_out = $info + ['curl_error' => $err];
        return new WP_Error('curl_error', $err, $info_out);
    }

    $header_size = $info['header_size'] ?? 0;
    $headers_raw = substr($response, 0, $header_size);
    $body = substr($response, $header_size);

    $info_out = $info + [
        'curl_error' => $err,
        'response_headers' => $headers_raw,
        'http_code' => $info['http_code'] ?? null,
    ];

    if (!empty($info['http_code']) && intval($info['http_code']) >= 400) {
        return new WP_Error('http_error', 'HTTP ' . $info['http_code'], $body);
    }

    return $body;
}