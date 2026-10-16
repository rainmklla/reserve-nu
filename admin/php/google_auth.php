<?php
require __DIR__ . '/../../vendor/autoload.php';

$client = new Google_Client();
$client->setAuthConfig(__DIR__ . '/credentials.json');

// IMPORTANT: must match exactly what you set in Google Cloud Console
$client->setRedirectUri('http://localhost/reservenu/admin/php/oauth_callback.php');

$client->addScope(Google_Service_Calendar::CALENDAR);
$client->setAccessType('offline');
$client->setPrompt('select_account consent');

// Redirect to Google’s OAuth 2.0 server
$authUrl = $client->createAuthUrl();
header("Location: $authUrl");
exit;
