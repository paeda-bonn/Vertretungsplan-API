<?php

use Firebase\JWT\JWT;

class JWTInterface
{
    private $privateKey;
    private $publicKey;
    /**
     * JWTInterface constructor.
     */
    public function __construct()
    {
        $this->privateKey = file_get_contents(__DIR__.'/../config/privkey.pem');
        $this->publicKey = file_get_contents(__DIR__.'/../config/pubkey.pem');
    }

    /**
     * @param $username - username of the user for that the token is issued
     * @param $type - type of the user
     * @return string - new Token
     */
    public function issueToken($username, $type){
        $token = array(
            "iss" => "vplan.moodle-paeda.de",
            "aud" => "vplan.moodle-paeda.de",
            "username" => $username,
            "usertype" => $type,
        );
        return JWT::encode($token, $this->privateKey, 'RS256');
    }

    /**
     * Validates if a token is in a valid format and has a matching signature
     * @param $token
     * @return bool
     */
    public function verifyToken($token){
        try {
            $decoded = JWT::decode($token, $this->publicKey, array('RS256'));
            //Validation Succeded
            return true;
        }catch (Exception $e){
            echo $e;
            //Validation failed
            return false;
        }

    }
}