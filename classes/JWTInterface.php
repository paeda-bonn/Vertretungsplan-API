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
        $this->privateKey = file_get_contents(__DIR__ . '/../config/privkey.pem');
        $this->publicKey = file_get_contents(__DIR__ . '/../config/pubkey.pem');
    }

    /**
     * @param $username - username of the user for that the token is issued
     * @param $type - type of the user
     * @return string - new Token
     */
    public function issueToken($username, $type)
    {
        $token = array(
            "iss" => "vplan.moodle-paeda.de, Witt",
            "aud" => "vplan.moodle-paeda.de",
            "username" => $username,
            "usertype" => $type,
            "expires" => (new DateTime)->getTimestamp() + (24 * 60 * 60)
        );
        return JWT::encode($token, $this->privateKey, 'RS256');
    }

    /**
     * Validates if a token is in a valid format and has a matching signature
     * @param $token
     * @return object
     * @throws InvalidTokenException
     */
    public function verifyToken($token)
    {
        try {
            $data = JWT::decode($token, $this->publicKey, array('RS256'));
            if ($data->expires < (new DateTime)->getTimestamp()) {
                throw new ExpiredException();
            } else {
                return $data;
            }

            //return true;
        } catch (Exception $e) {
            //echo $e;
            if ($e instanceof ExpiredException) {
                throw new ExpiredException();
            } else {
                //Validation failed
                throw new InvalidTokenException();
            }
        }
    }
}