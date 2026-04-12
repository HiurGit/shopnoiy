<?php

return [
    'gateway_url' => env('VNPAY_GATEWAY_URL', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html'),
    'tmn_code' => env('VNPAY_TMN_CODE', ''),
    'hash_secret' => env('VNPAY_HASH_SECRET', ''),
    'version' => env('VNPAY_VERSION', '2.1.0'),
    'command' => env('VNPAY_COMMAND', 'pay'),
    'currency' => env('VNPAY_CURRENCY', 'VND'),
    'locale' => env('VNPAY_LOCALE', 'vn'),
    'order_type' => env('VNPAY_ORDER_TYPE', 'other'),
    'expire_minutes' => (int) env('VNPAY_EXPIRE_MINUTES', 15),
];
