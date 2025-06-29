<?php

return [
    'paths' => ['api/*'],
    
    'allowed_methods' => ['*'],
    
    'allowed_origins' => ['*'],
    
    'allowed_origins_patterns' => [],
    
    'allowed_headers' => ['*'],
    
    'exposed_headers' => [
        'Content-Disposition',
        'Content-Type',
        'Content-Length',
    ],
    
    'max_age' => 0,
    
    'supports_credentials' => false,
]; 