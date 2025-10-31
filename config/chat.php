<?php

return [
    'enabled' => env('CHAT_ENABLED', true),
    'provider' => env('CHAT_PROVIDER', 'crisp'),
    'crisp_website_id' => env('CRISP_WEBSITE_ID'),
];
