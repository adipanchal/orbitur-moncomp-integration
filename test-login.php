<?php
/**
 * Standalone SOAP login tester (NO WordPress required)
 * URL example: https://yourdomain.com/wp-content/plugins/orbitur-moncomp-integration/test-login.php
 */

header('Content-Type: text/plain; charset=utf-8');

echo "=== Orbitur SOAP Login Test ===\n\n";

/* ---- EDIT THIS ---- */
$endpoint = 'https://orbitur.multicamp.thelis.fr/MultiCampMonCompte/MLC_MonCompteServices?wsdl';
$email = 'dev@blendd.pt';
$password = 'InêsMarques01!?';
/* -------------------- */

try {

    echo "Endpoint: $endpoint\n\n";
    echo "Attempting SoapClient...\n";

    $client = new SoapClient($endpoint, [
        'exceptions' => true,
        'trace' => true,
        'cache_wsdl' => WSDL_CACHE_NONE
    ]);

    $request = [
        'RqLogin' => [
            'id' => $email,
            'pw' => $password,
            'app' => 'siteMarchand'
        ]
    ];

    $response = $client->__soapCall('login', $request);

    echo "--- Raw Response Object ---\n";
    print_r($response);

    $arr = json_decode(json_encode($response), true);
    echo "\n--- Parsed Array ---\n";
    print_r($arr);

    // Extract idSession if exists
    if (!empty($arr['result']['idSession'])) {
        echo "\nSUCCESS: idSession = " . $arr['result']['idSession'] . "\n";
    } else {
        echo "\nFAILED: No idSession returned.\n";
        if (!empty($arr['result']['messError'])) {
            echo "Remote Error: " . $arr['result']['messError'] . "\n";
        }
        if (!empty($arr['result']['error'])) {
            echo "Remote Error Code: " . $arr['result']['error'] . "\n";
        }
    }

} catch (Exception $e) {

    echo "\n=== EXCEPTION ===\n";
    echo $e->getMessage() . "\n";

    if (method_exists($client, "__getLastResponse")) {
        echo "\n--- Last SOAP Response ---\n";
        echo $client->__getLastResponse();
    }
}

echo "\n=== END ===\n";