<?php

return [
    'enabled' => env('CHAT_ENABLED', true),
    // Default to native chat so live chat shows even if CRISP_WEBSITE_ID isn't set
    'provider' => env('CHAT_PROVIDER', 'native'),
    'crisp_website_id' => env('CRISP_WEBSITE_ID'),
];
