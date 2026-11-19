<?php

/*
|--------------------------------------------------------------------------
| SEO and brand entity settings
|--------------------------------------------------------------------------
|
| Used for the Organization schema (JSON-LD), footer social links and llms.txt.
| Social profile URLs are read from .env so they can be added without a code change.
|
*/

return [
    'organization' => [
        'name' => 'SOV SUMMIT',
        'legal_name' => 'Sovereign Summit GmbH',
        'slogan' => 'People. Ideas. Impact.',
        'email' => 'info@sov-summit.com',
        'telephone' => '+41 71 539 23 00',
        'founding_date' => env('SEO_FOUNDING_DATE'),
        'logo' => 'assets/img/logo.webp',
        'area_served' => 'Worldwide',
        'address' => [
            'street' => 'Bahnhofstrasse 21',
            'locality' => 'Zug',
            'postal_code' => '6300',
            'country' => 'CH',
        ],
    ],

    /*
     * Public brand profiles. Each filled URL is added to the schema "sameAs" list.
     * Social ones (linkedin, instagram, facebook, x, youtube) are also shown in the footer.
     */
    'profiles' => [
        'linkedin' => env('SOCIAL_LINKEDIN_URL'),
        'instagram' => env('SOCIAL_INSTAGRAM_URL'),
        'facebook' => env('SOCIAL_FACEBOOK_URL'),
        'x' => env('SOCIAL_X_URL'),
        'youtube' => env('SOCIAL_YOUTUBE_URL'),
        'crunchbase' => env('PROFILE_CRUNCHBASE_URL'),
        'wikidata' => env('PROFILE_WIKIDATA_URL'),
        'google_business' => env('PROFILE_GOOGLE_BUSINESS_URL'),
    ],

    'footer_social' => ['linkedin', 'instagram', 'facebook', 'x', 'youtube'],
];
