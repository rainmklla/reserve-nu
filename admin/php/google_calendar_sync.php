<?php
require_once __DIR__ . '/google_auth.php'; // or autoload.php, depending on how you load Google Client

function getGoogleClient() {
    $client = new Google_Client();
    $client->setApplicationName('ReserveNU');
    $client->setScopes(Google_Service_Calendar::CALENDAR_READONLY);
    $client->setAuthConfig(__DIR__ . '/credentials.json');
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    $tokenPath = __DIR__ . '/token.json';
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);

        // Refresh token if expired
        if ($client->isAccessTokenExpired()) {
            if ($client->getRefreshToken()) {
                $newAccessToken = $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
                
                if (!isset($newAccessToken['error'])) {
                    file_put_contents($tokenPath, json_encode($client->getAccessToken()));
                } else {
                    unlink($tokenPath); // Force re-authentication if refresh fails
                    header('Location: google_auth.php');
                    exit;
                }
            } else {
                unlink($tokenPath); // No refresh token, force re-auth
                header('Location: google_auth.php');
                exit;
            }
        }
    } else {
        header('Location: google_auth.php');
        exit;
    }

    return $client;
}


function getUpcomingEvents($maxResults = 50, $timeMin = null, $timeMax = null) {
    $client = getGoogleClient();
    $service = new Google_Service_Calendar($client);

    $calendarId = 'primary';

    // If no start date is provided, default to today
    if (!$timeMin) {
        $timeMin = date('c');
    }

    $optParams = [
        'orderBy' => 'startTime',
        'singleEvents' => true,
        'timeMin' => $timeMin
    ];

    // If a timeMax is provided, include it in the filter
    if ($timeMax) {
        $optParams['timeMax'] = $timeMax;
    } else {
        $optParams['maxResults'] = $maxResults; // fallback if no end range is given
    }

    $results = $service->events->listEvents($calendarId, $optParams);
    return $results->getItems();
}

