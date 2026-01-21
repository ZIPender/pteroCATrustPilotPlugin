<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Days Before Expiry
    |--------------------------------------------------------------------------
    |
    | Number of days before server expiry to show the review popup
    |
    */
    'days_before_expiry' => env('TRUSTPILOT_DAYS_BEFORE_EXPIRY', 7),

    /*
    |--------------------------------------------------------------------------
    | Trustpilot Review URL
    |--------------------------------------------------------------------------
    |
    | The URL where users will be redirected to leave a review
    |
    */
    'review_url' => env('TRUSTPILOT_REVIEW_URL', 'https://www.trustpilot.com/evaluate/your-business'),

    /*
    |--------------------------------------------------------------------------
    | Trustpilot API Key (Optional)
    |--------------------------------------------------------------------------
    |
    | Optional API key for advanced Trustpilot integration
    |
    */
    'api_key' => env('TRUSTPILOT_API_KEY', null),

    /*
    |--------------------------------------------------------------------------
    | Plugin Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable the plugin globally
    |
    */
    'enabled' => env('TRUSTPILOT_ENABLED', true),
];
