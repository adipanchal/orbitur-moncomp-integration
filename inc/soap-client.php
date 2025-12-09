<?php
if (!defined('ABSPATH'))
    exit;

/**
 * Low-level SOAP HTTP POST via cURL.
 * Returns raw response string or WP_Error.
 */
function orbitur_make_soap_request($endpoint, $soap_action, $xml_body, &$info_out = null)
{
    // Basic validation
    if (empty($endpoint))
        return new WP_Error('no_endpoint', 'No endpoint configured');

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: text/xml; charset=utf-8',
        'Content-Length: ' . strlen($xml_body),
        'SOAPAction: "http://webservices.multicamp.fr/' . $soap_action . '"'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_body);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    // optional: verify SSL (true in prod)
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    $info = curl_getinfo($ch);
    curl_close($ch);
    $info_out = $info + ['curl_error' => $err];
    if ($res === false)
        return new WP_Error('curl_error', $err, $info_out);
    return $res;
}