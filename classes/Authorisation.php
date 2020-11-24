<?php
require_once('Userbase.php');
require_once('JWTInterface.php');

class Authorisation
{
    private $userbase;
    private $usertype = "";
    private $jwtInterface;

    /**
     * Authorisation constructor.
     * @param Userbase $userbase
     */
    public function __construct(Userbase $userbase)
    {
        $this->userbase = $userbase;
        $this->jwtInterface = new JWTInterface();
    }

    /**
     * Verifys the users credentials and returns a token
     * @return string
     */
    public function login()
    {
        $username = $_GET["username"];
        $password = $_GET["password"];

        if ($this->userbase->loadUser($username)) {
            if ($this->userbase->verifyPassword($password)) {

                return $this->jwtInterface->issueToken($username, $this->userbase->getType());
            } else {
                return "PW Error";
            }
        } else {
            return "User: N/A";
        }
    }

    /**
     * Validation wrapper for the JWToken
     * @param $key
     */
    public function verifyKey($key)
    {
        if (!$this->jwtInterface->verifyToken($key)) {
            http_response_code(401);
            die("401");
        }
    }

    /**
     * Validation wrapper for the JWToken
     * @param $key
     */
    public function verifyKeyType($key,$type)
    {
        if (!$this->jwtInterface->verifyToken($key)) {
            http_response_code(401);
            die("401");
        }
    }

    /**
     * @return string
     */
    public function getUsertype(): string
    {
        return $this->usertype;
    }
}