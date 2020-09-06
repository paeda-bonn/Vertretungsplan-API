<?php

class Applications
{
    private $application;
    private $secret;
    private $sqlConn;

    function __construct($conn)
    {
        $this->sqlConn = $conn;
    }

    function loadRequest()
    {
        $this->secret = htmlspecialchars($_GET["secret"]);
        $_GET["secret"] = NULL;
    }

    function validate()
    {
        $access = false;

        $stmt = $this->sqlConn->prepare("SELECT * FROM `applications` WHERE `secret`=:secret AND `active`='1'");

        $stmt->bindParam(':secret', $this->secret);

        $stmt->execute();
        foreach ($stmt->fetchAll() as $row) {
            $access = true;
        }

        if (!$access) {
            return false;
        } else {
            return true;
        }
    }
}

?>