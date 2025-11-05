<?php
if (!defined('ABSPATH')) exit;

/**
 * Parse the getBookingList SOAP raw XML and return normalized array.
 */
function orbitur_parse_booking_xml_string($xmlString) {
    if (empty($xmlString)) return [];
    libxml_use_internal_errors(true);
    $sx = @simplexml_load_string($xmlString);
    if (!$sx) return [];
    $items = $sx->xpath('//result//list//item') ?: $sx->xpath('//booking') ?: [];
    $out = [];
    foreach ($items as $it) {
        $get = function($k) use ($it){ return isset($it->{$k}) ? trim((string)$it->{$k}) : ''; };
        $begin = $get('begin'); $end = $get('end');
        $begin_date = $begin ? date('Y-m-d', strtotime($begin)) : '';
        $end_date   = $end ? date('Y-m-d', strtotime($end)) : '';
        $supps = [];
        if (isset($it->supplements->item)) {
            foreach ($it->supplements->item as $s) {
                $supps[] = ['name'=> (string)$s->name, 'quantity' => (string)$s->quantity];
            }
        }
        $out[] = [
            'id' => $get('idOrder') ?: $get('orderNumber'),
            'site' => $get('site'),
            'begin' => $begin_date,
            'end' => $end_date,
            'lodging' => $get('lodging'),
            'nbPers' => $get('nbPers'),
            'price' => $get('priceCustomer') ?: $get('price'),
            'status' => $get('status'),
            'situation' => $get('situation'),
            'url' => html_entity_decode($get('url')),
            'idSite' => $get('idSite'),
            'supplements' => $supps,
            'raw' => $it
        ];
    }
    return $out;
}

function orbitur_split_bookings_list($bookings) {
    $today = new DateTime('today');
    $upcoming = $past = [];
    foreach ($bookings as $b) {
        $end = $b['end'] ? new DateTime($b['end']) : null;
        if ($end && $end < $today) $past[] = $b; else $upcoming[] = $b;
    }
    return ['upcoming'=>$upcoming,'past'=>$past];
}