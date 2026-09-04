<?php

return [
    'mode' => env('TENANCY_MODE', 'shared'),
    'base_domain' => env('TENANCY_BASE_DOMAIN', parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
    'reserved_subdomains' => ['www', 'app', 'api', 'admin'],
    'schema_prefix' => env('TENANCY_SCHEMA_PREFIX', 'school_'),
];
