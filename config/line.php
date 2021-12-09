<?php
return [
    'client_id'     => env('LINE_CHANNEL_ID'),
    'client_secret' => env('LINE_CHANNEL_SECRET'),
    'callback_url'  => env('APP_URL','https://localhost:8000') . env('LINE_CALLBACK_URL','/auth/line/callback'),
];
