<?php
$url = "https://example.com/api/";
$config = array();
$config["url"] = array();
$config["sql"] = array();
$config["sql"]["table"] = array();
$config["ics"] = array();
$config["lessons"] = array();

$config["url"]["site"] = "https://example.com/";
$config["url"]["api"] = "https://example.com/api/";

$config["sql"]["username"] = "user";
$config["sql"]["password"] = "pass";
$config["sql"]["charset"] = "utf8";
$config["sql"]["host"] = "127.0.0.1";
$config["sql"]["database"] = "db";
$pdo = new PDO('mysql:host=' . $config["sql"]["host"] . ';dbname=' . $config["sql"]["database"] . ';charset=' . $config["sql"]["charset"], $config["sql"]["username"], $config["sql"]["password"]);

$config["sql"]["table"]["config"] = "config";
$config["sql"]["table"]["klausuren"] = "klausuren";
$config["sql"]["table"]["aushang"] = "aushang";
$config["sql"]["table"]["token"] = "access_tokens";
$config["sql"]["table"]["stundenplan"] = "stundenplan";
$config["sql"]["table"]["vertretungen"] = "vertretungsdata";
$config["sql"]["table"]["aufsichten"] = "aufsichten";

$config["ics"]["language"] = "DE";
$config["ics"]["topic"] = "OKS ics TEST";
$config["ics"]["publisher"] = "Nils Witt";
$config["ics"]["timezone"] = "Europe/Berlin";

$config["lessons"][1] = "07:50-08:35";
$config["lessons"][2] = "08:35-09:20";
$config["lessons"][3] = "09:40-10:25";
$config["lessons"][4] = "10:30-11:15";
$config["lessons"][5] = "11:35-12:20";
$config["lessons"][6] = "12:20-13:05";
$config["lessons"][7] = "13:15-14:00";


?>