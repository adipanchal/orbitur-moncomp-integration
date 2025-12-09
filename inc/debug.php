<?php
// Debug bookings: add to plugin (inc/debug.php) or drop into main plugin file for testing.
if (!function_exists('orbitur_debug_bookings_shortcode')) {
    add_shortcode('orbitur_debug_bookings', 'orbitur_debug_bookings_shortcode');

    function orbitur_debug_bookings_shortcode($atts = [])
    {
        if (!is_user_logged_in())
            return '<p>Log in first.</p>';
        $uid = get_current_user_id();
        $out = [];

        $out[] = "<h3>Orbitur debug (user {$uid})</h3>";

        // show stored idSession
        $idSession = get_user_meta($uid, 'moncomp_idSession', true);
        $out[] = "<p><strong>Stored moncomp_idSession:</strong> " . ($idSession ? esc_html($idSession) : '<em>(none)</em>') . "
</p>";

        // show endpoint
        $endpoint = get_option('orbitur_moncomp_endpoint', '');
        $out[] = "<p><strong>Configured endpoint:</strong> " . ($endpoint ? esc_html($endpoint) : '<em>(not set)</em>') . "</p>
";

        // option: clear transient link
        $tkey = 'orbitur_bookings_' . $uid;
        $clear_link = add_query_arg('orbitur_debug_clear', '1');
        $out[] = '<p><a href="' . esc_url($clear_link) . '">Clear booking transient</a></p>';
        if (isset($_GET['orbitur_debug_clear'])) {
            delete_transient($tkey);
            $out[] = '<p><strong>Transient deleted.</strong></p>';
        }

        // option: quick set idSession (for manual test) - caution: visible only to logged in user
        if (isset($_GET['orbitur_debug_set'])) {
            update_user_meta($uid, 'moncomp_idSession', sanitize_text_field(wp_unslash($_GET['orbitur_debug_set'])));
            $idSession = get_user_meta($uid, 'moncomp_idSession', true);
            $out[] = '<p><strong>idSession set to:</strong> ' . esc_html($idSession) . '</p>';
        }

        // fetch raw
        if (empty($idSession)) {
            $out[] = '<p><em>No idSession present — you must login via MonCompte or set idSession manually for debug.</em></p>';
            return implode("\n", $out);
        }

        $raw = orbitur_getBookingList_raw($idSession);
        if (is_wp_error($raw)) {
            $out[] = '<p style="color:darkred"><strong>orbitur_getBookingList_raw returned WP_Error:</strong> ' .
                esc_html($raw->get_error_message()) . '</p>';
            $meta = $raw->get_error_data();
            if ($meta)
                $out[] = '
<pre style="white-space:pre-wrap;max-width:100%;">' . esc_html(print_r($meta, true)) . '</pre>';
            // show last orbitur.log tail if available
            $logfile = defined('ORBITUR_LOG') ? ORBITUR_LOG : WP_CONTENT_DIR . '/uploads/orbitur.log';
            if (file_exists($logfile)) {
                $tail = implode("\n", array_slice(explode("\n", file_get_contents($logfile)), -60));
                $out[] = '<h4>orbitur.log tail</h4>
<pre style="max-height:400px;overflow:auto;">' . esc_html($tail) . '</pre>';
            }
            return implode("\n", $out);
        }

        // show raw snippet
        $out[] = '<h4>Raw SOAP response (snippet)</h4>';
        $out[] = '
<pre
    style="white-space:pre-wrap;max-width:100%;">' . esc_html(mb_substr($raw, 0, 6000)) . (mb_strlen($raw) > 6000 ? "\n\n...(truncated)" : '') . '</pre>
';

        // parse
        $parsed = orbitur_parse_booking_xml_string($raw);
        if (is_wp_error($parsed)) {
            $out[] = '<p style="color:darkred"><strong>Parsing error:</strong> ' . esc_html($parsed->get_error_message()) . '</p>';
            return implode("\n", $out);
        }

        // show top-level keys of parsed output
        $out[] = '<h4>Parsed structure (top keys)</h4>';
        if (is_array($parsed)) {
            $keys = array_keys($parsed);
            $out[] = '
<pre>' . esc_html(implode(", ", $keys)) . '</pre>';
        } else {
            $out[] = '
<pre>' . esc_html(print_r($parsed, true)) . '</pre>';
        }

        // split into upcoming/past
        $lists = orbitur_split_bookings_list($parsed);
        if (is_wp_error($lists)) {
            $out[] = '<p style="color:darkred"><strong>Split error:</strong> ' . esc_html($lists->get_error_message()) . '</p>';
            return implode("\n", $out);
        }

        $upcount = count($lists['upcoming'] ?? []);
        $pastcount = count($lists['past'] ?? []);
        $out[] = "<p><strong>Upcoming:</strong> {$upcount} items — <strong>Past:</strong> {$pastcount} items</p>";

        // sample first items
        if ($upcount) {
            $out[] = '<h4>First upcoming item (sample)</h4>
<pre>' . esc_html(print_r($lists['upcoming'][0], true)) . '</pre>';
        } else {
            $out[] = '<p>No upcoming items found.</p>';
        }
        if ($pastcount) {
            $out[] = '<h4>First past item (sample)</h4>
<pre>' . esc_html(print_r($lists['past'][0], true)) . '</pre>';
        } else {
            $out[] = '<p>No past items found.</p>';
        }

        return implode("\n", $out);
    }
}