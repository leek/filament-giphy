<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API key
    |--------------------------------------------------------------------------
    |
    | GIPHY requires search to run in the browser, so this key is visible
    | on the page. Treat it as a public key.
    |
    */

    'api_key' => env('GIPHY_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Content rating
    |--------------------------------------------------------------------------
    |
    | One of: g, pg, pg-13, r. Any other value resolves to pg-13.
    |
    */

    'rating' => 'pg-13',

    /*
    |--------------------------------------------------------------------------
    | Renditions
    |--------------------------------------------------------------------------
    |
    | Names of rendition objects on the GIPHY GIF payload. The package copies
    | the rendition "url" field and does not build a URL itself.
    |
    */

    'grid_rendition' => 'fixed_height_small',

    'inserted_rendition' => 'original',

    /*
    |--------------------------------------------------------------------------
    | Language
    |--------------------------------------------------------------------------
    |
    | A two-letter code, or null to use the application locale. Locales such
    | as en_US send "en". Any other shape is omitted from the request.
    |
    */

    'language' => null,

];
