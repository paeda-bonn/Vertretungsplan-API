<?php
$options = [
    'cost' => 12,
];
echo password_hash("otto14", PASSWORD_BCRYPT, $options);