<?php

return [
    'test' => env('NEOPAY_TEST', true),
    'affilliation' => env('NEOPAY_AFFILLIATION'),
    'terminal' => env('NEOPAY_TERMINAL'),
    'user' => env('NEOPAY_USER'),
    'password' => env('NEOPAY_PASSWORD'),
    'redirect' => env('NEOPAY_REDIRECT'),
    'url'  => 'https://epayserver.neonet.com.gt/',
    'url_test'  => 'https://epaytestvisanet.com.gt:4433/V3/',
    'paymentgw_ip' => '181.114.3.133',
    'paymentgw_ip_test' => '190.111.1.198',
];
