<?php
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 1);

require __DIR__ . '/../../vendor/autoload.php';

$client = new Google_Client();
$client->setAuthConfig(__DIR__ . '/credentials.json');
$client->setRedirectUri('http://localhost/reservenu/admin/php/oauth_callback.php');
$client->addScope(Google_Service_Calendar::CALENDAR);
$client->setAccessType('offline');
$client->setPrompt('select_account consent');

$tokenPath = __DIR__ . '/token.json';

// ✅ Step 1: Ensure we have an authorization code
if (!isset($_GET['code'])) {
    exit("❌ No authorization code returned from Google. Did you start from google_auth.php?");
}

try {
    // ✅ Step 2: Exchange the code for an access token
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    if (isset($token['error'])) {
        throw new Exception("Google API error: " . $token['error_description']);
    }

    // ✅ Step 3: Save token to token.json
    if (!file_exists(dirname($tokenPath))) {
        mkdir(dirname($tokenPath), 0700, true);
    }
    file_put_contents($tokenPath, json_encode($token));

    // ✅ Step 4: Success message
    echo "✅ Token successfully saved at: <code>$tokenPath</code><br>";
    echo "<a href='admin_requests.php'>Go back to Admin Requests</a>";

} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage();
}
