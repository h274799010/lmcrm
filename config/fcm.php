<?php

return [
    'driver' => env('FCM_PROTOCOL', 'http'),
    'log_enabled' => true,

    'http' => [
        'server_key' => env('FCM_SERVER_KEY', 'AAAAVTxhNEE:APA91bHqopKvEUTc_q6QDqQzI79OibRxvOOlVfIDntMZp8A1OaMWo7ZszbddXHTd7u9dYjHQM702reZMszEV4GPOPo2ocoVME7bIWlGPLUMcfzc2DRKn-bD6Q_K84VJLXMPOmCXiV7Zi'),
        'sender_id' => env('FCM_SENDER_ID', '366085223489'),
        'server_send_url' => 'https://fcm.googleapis.com/fcm/send',
        'server_group_url' => 'https://android.googleapis.com/gcm/notification',
        'timeout' => 30.0, // in second
    ],
];
