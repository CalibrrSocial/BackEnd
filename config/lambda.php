<?php

return [
    'region' => env('LAMBDA_REGION', env('AWS_DEFAULT_REGION', 'us-east-1')),
    'function' => env('LAMBDA_PROFILE_LIKED_FUNCTION', 'emailNotificationFinal'),
    'debug' => env('LAMBDA_DEBUG', false),
    'aws_key' => env('AWS_ACCESS_KEY_ID'),
    'aws_secret' => env('AWS_SECRET_ACCESS_KEY'),
    'aws_token' => env('AWS_SESSION_TOKEN'),
];


