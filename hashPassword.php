<?php

$options = [
    'cost' => 12,
];
echo password_hash($argv[0], PASSWORD_BCRYPT, $options);