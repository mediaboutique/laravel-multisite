<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Site Model
    |--------------------------------------------------------------------------
    |
    | This option determines the model that will be used for handling
    | each site. The model should implement
    | MediaBoutique\Multisite\Contracts\MultisiteModel.
    |
    | The 'alias' option should correspond to a shortname-type field for the
    | model, like 'slug'.
    |
    */

    'model' => env('MULTISITE_MODEL'),

    'alias' => env('MULTISITE_ALIAS'),

    /*
    |--------------------------------------------------------------------------
    | Exclude Hosts
    |--------------------------------------------------------------------------
    */

    'exclude_hosts' => [],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    */

    'cache' => [

        'enabled' => env('MULTISITE_CACHE_ENABLED', false),

    ],

];
