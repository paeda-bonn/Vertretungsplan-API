<?php
require_once('SQLConfig.php');

class Config
{
    private $sql;

    /**
     * Config constructor.
     */
    public function __construct()
    {
        $this->sql = new SQLConfig();
    }

    /**
     * @return SQLConfig
     */
    public function getSql(): SQLConfig
    {
        return $this->sql;
    }

}