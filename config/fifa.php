<?php

return [

    /*
    |--------------------------------------------------------------------------
    | FIFA API
    |--------------------------------------------------------------------------
    |
    | Configuration for the FIFA calendar API used to sync World Cup matches.
    |
    */

    'base_url' => env('FIFA_API_BASE_URL', 'https://api.fifa.com/api/v3'),

    'season_id' => env('FIFA_SEASON_ID', '285023'),

    'language' => env('FIFA_API_LANGUAGE', 'en'),

    'match_count' => (int) env('FIFA_API_MATCH_COUNT', 500),

    'timeout' => (int) env('FIFA_API_TIMEOUT', 15),

    'connect_timeout' => (int) env('FIFA_API_CONNECT_TIMEOUT', 5),

    'sync_interval_hours' => (int) env('FIFA_SYNC_INTERVAL_HOURS', 6),

    'results_sync_minutes' => (int) env('FIFA_RESULTS_SYNC_MINUTES', 15),

    /*
    |--------------------------------------------------------------------------
    | FIFA country codes to ISO 3166-1 alpha-2
    |--------------------------------------------------------------------------
    |
    | Used to render flag emojis from FIFA three-letter abbreviations.
    |
    */

    'country_alpha2' => [
        'ALB' => 'AL',
        'ALG' => 'DZ',
        'ARG' => 'AR',
        'AUS' => 'AU',
        'AUT' => 'AT',
        'BEL' => 'BE',
        'BIH' => 'BA',
        'BRA' => 'BR',
        'CAN' => 'CA',
        'CHI' => 'CL',
        'COL' => 'CO',
        'CRC' => 'CR',
        'CRO' => 'HR',
        'CZE' => 'CZ',
        'DEN' => 'DK',
        'ECU' => 'EC',
        'EGY' => 'EG',
        'ENG' => 'GB',
        'ESP' => 'ES',
        'FRA' => 'FR',
        'GER' => 'DE',
        'GHA' => 'GH',
        'GRE' => 'GR',
        'HUN' => 'HU',
        'IRN' => 'IR',
        'ISL' => 'IS',
        'ITA' => 'IT',
        'JAM' => 'JM',
        'JPN' => 'JP',
        'KOR' => 'KR',
        'MAR' => 'MA',
        'MEX' => 'MX',
        'NED' => 'NL',
        'NGA' => 'NG',
        'NOR' => 'NO',
        'NZL' => 'NZ',
        'PAN' => 'PA',
        'PAR' => 'PY',
        'PER' => 'PE',
        'POL' => 'PL',
        'POR' => 'PT',
        'QAT' => 'QA',
        'RSA' => 'ZA',
        'RUS' => 'RU',
        'SEN' => 'SN',
        'SRB' => 'RS',
        'SUI' => 'CH',
        'SVK' => 'SK',
        'SVN' => 'SI',
        'SWE' => 'SE',
        'TUN' => 'TN',
        'TUR' => 'TR',
        'UKR' => 'UA',
        'URU' => 'UY',
        'USA' => 'US',
        'WAL' => 'GB',
    ],

];
