<?php

return [
    'privateKeyFile' => $_ENV['PRIVATE_KEY_PATH'] ?? '/opt/keys/jwt_rsa',
    'publicKeyFile' => $_ENV['PUBLIC_KEY_PATH']  ?? '/opt/keys/jwt_rsa.pem',
];
