<?php


class Userbase
{
    private $sqlConn;
    private $passwordhash = "";
    private $type = "";

    /**
     * Userbase constructor.
     * @param $sqlConn
     */
    public function __construct($sqlConn)
    {
        $this->sqlConn = $sqlConn;
    }

    /**
     * tries loading the user information from the database
     * @param $username
     * @return bool
     */
    public function loadUser($username)
    {
        $stmt = $this->sqlConn->prepare("SELECT * FROM users WHERE `username` LIKE :username");
        $stmt->bindParam(':username', $username);

        $stmt->execute();

        $res = $stmt->fetchAll(PDO::FETCH_CLASS);
        if (sizeof($res) == 1) {
            $this->passwordhash = $res[0]->password;
            $this->type = $res[0]->usertype;
        }
        return sizeof($res) == 1;
    }

    /**
     * Validates the param password with the hash from the database
     * @param $password
     * @return bool - true-> valid, false-> invalid
     */
    public function verifyPassword($password)
    {
        return password_verify($password, $this->passwordhash);
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

}