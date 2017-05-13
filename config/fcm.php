<?php

return [
    'driver' => env('FCM_PROTOCOL', 'http'),
    'log_enabled' => true,

    'http' => [
        'server_key' => env('FCM_SERVER_KEY', 'AAAAyLC1u6c:APA91bGD4JQ96NfwqSs5GcpDLxQ05HycDRpGz3nXBNoDoKIJTLBqIhYbpXdFi7Eg6GivhCR5-VGD41GuNE1G4xDE9uMtPA4qgJH4xmN7o_flGI7Im12dTJBqGGwN0aPEoMJGEAb5rKGo'),
        'sender_id' => env('FCM_SENDER_ID', '861958159271'),
        'server_send_url' => 'https://fcm.googleapis.com/fcm/send',
        'server_group_url' => 'https://android.googleapis.com/gcm/notification',
        'timeout' => 30.0, // in second
    ],
];
