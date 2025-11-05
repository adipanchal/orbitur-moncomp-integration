<?php
if (!defined('ABSPATH')) exit;

function orbitur_parse_booking_xml_string($xml_string) {
    if (empty($xml_string)) return [];

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    if (! $doc->loadXML($xml_string)) {
        libxml_clear_errors();
        return new WP_Error('xml_parse_error','Failed to parse XML');
    }

    $xpath = new DOMXPath($doc);
    $nodes = $xpath->query("//*[local-name() = 'item']");

    $results = [];
    foreach ($nodes as $node) {
        $item = [];
        foreach ($node->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) continue;
            $key = $child->localName;
            $val = trim($child->textContent);
            $item[$key] = $val;
        }
        $results[] = $item;
    }
    return $results;
}

function orbitur_split_bookings_list($parsed_results) {
    $upcoming = []; $past = [];
    $now = time();
    foreach ($parsed_results as $r) {
        $begin = isset($r['begin']) ? strtotime($r['begin']) : 0;
        if ($begin >= $now) $upcoming[] = $r; else $past[] = $r;
    }
    return ['upcoming'=>$upcoming,'past'=>$past];
}