<?php
if (!defined('ABSPATH')) exit;

/**
 * Parse booking XML string into PHP array (namespace-agnostic)
 * Returns array of items or WP_Error on parse failure
 */
function orbitur_parse_booking_xml_string($xml_string) {
    if (empty($xml_string)) return [];

    $doc = new DOMDocument();
    libxml_use_internal_errors(true);
    if (! $doc->loadXML($xml_string)) {
        $errs = libxml_get_errors();
        libxml_clear_errors();
        return new WP_Error('xml_parse_error', 'Failed to parse XML', $errs);
    }
    $xpath = new DOMXPath($doc);

    // Find all nodes named 'item' regardless of namespace:
    $nodes = $xpath->query("//*[local-name() = 'item']");

    $results = [];
    foreach ($nodes as $idx => $node) {
        $item = [];
        foreach ($node->childNodes as $child) {
            if ($child->nodeType !== XML_ELEMENT_NODE) continue;
            $key = $child->localName;
            $val = trim($child->textContent);

            // If this child contains element children (eg. supplements), extract them recursively
            if ($child->hasChildNodes()) {
                $hasElementChild = false;
                foreach ($child->childNodes as $c2) {
                    if ($c2->nodeType === XML_ELEMENT_NODE) { $hasElementChild = true; break; }
                }
                if ($hasElementChild) {
                    $sub = [];
                    foreach ($child->childNodes as $c2) {
                        if ($c2->nodeType !== XML_ELEMENT_NODE) continue;
                        if ($c2->localName === 'item') {
                            $subItem = [];
                            foreach ($c2->childNodes as $c3) {
                                if ($c3->nodeType === XML_ELEMENT_NODE) {
                                    $subItem[$c3->localName] = trim($c3->textContent);
                                }
                            }
                            $sub[] = $subItem;
                        } else {
                            $sub[$c2->localName] = trim($c2->textContent);
                        }
                    }
                    $item[$key] = $sub;
                    continue;
                }
            }

            $item[$key] = $val;
        }
        $results[] = $item;
    }

    return $results;
}

/**
 * Split parsed bookings into upcoming and past (based on begin date)
 * @param array $parsed_results
 * @return array ['upcoming'=>[], 'past'=>[]]
 */
function orbitur_split_bookings_list($parsed_results) {
    $upcoming = [];
    $past = [];
    $now = time();
    foreach ($parsed_results as $r) {
        $begin = isset($r['begin']) ? strtotime($r['begin']) : 0;
        if ($begin >= $now) $upcoming[] = $r;
        else $past[] = $r;
    }
    return ['upcoming'=>$upcoming, 'past'=>$past];
}