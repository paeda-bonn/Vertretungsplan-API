<?php
require_once 'vendor/autoload.php';
require_once('config/Config.php');
require_once('classes/Authorisation.php');
require_once('classes/Vertretungsplan.php');
require_once('classes/Klausuren.php');


header('Content-Type: application/json');

$method = $_SERVER["REQUEST_METHOD"];
$path = explode("/", substr($_SERVER["PATH_INFO"], 1));
$authKey = substr($_SERVER["HTTP_AUTHORIZATION"],7);

$config = new Config();
$pdo = $config->getSql()->getPDO();
$authFramework = new Authorisation(new Userbase($pdo));

$vertretungsplan = new Vertretungsplan($pdo);
$klausuren = new Klausuren($pdo);

/*
 * Router
 */
//GET
if ($method == "GET") {
    if($path[0] == "login"){
        echo json_encode($authFramework->login());
    }
    if ($path[0] == "vertretungsplan") {
        $authFramework->verifyKeyType($authKey,"user");

        if ($path[1] == "vertretungen") {
            if ($path[2] == "date") {
                echo json_encode($vertretungsplan->getVertretungenByDate($path[3]));
            }
        } elseif ($path[1] == "activedays") {
            echo json_encode($vertretungsplan->getActiveDates());
        }
    } elseif ($path[0] == "klausuren") {
        $authFramework->verifyKeyType($authKey,"user");
        $authFramework->verifyKey($authKey);
        if ($path[1] == "date") {
            echo json_encode($klausuren->getByDate($path[2]));
        } elseif ($path[1] == "active") {
            echo json_encode($klausuren->getActive());
        } elseif ($path[1] == "upcoming") {
            echo json_encode($klausuren->getUpcoming());
        } else {
            echo json_encode($klausuren->getAll());
        }
    }

//POST
} elseif ($method == "POST") {
    $authFramework->verifyKeyType($authKey,"admin");
    if ($path[0] == "vertretungsplan") {
        if ($path[1] == "vertretungen") {
            $vertretungsplan->insertVertretungen($vertretungsplan->loadPayloadToArray());
        } elseif ($path[1] == "activedates") {
            print_r($vertretungsplan->loadPayloadToArray());
            $vertretungsplan->setActiveDates($vertretungsplan->loadPayloadToArray());
        }
    } elseif ($path[0] == "klausuren") {
        $res = $klausuren->insertArray($klausuren->loadPayloadToArray());
    }

//DELETE
} elseif ($method == "DELETE") {
    $authFramework->verifyKeyType($authKey,"admin");
    if ($path[0] == "vertretungsplan") {
        if ($path[1] == "vertretungen") {
            if ($path[2] == "id") {
                $vertretungsplan->deleteVertretungenById($vertretungsplan->loadPayloadToArray());
            } elseif ($path[2] == "date") {
                $vertretungsplan->deleteVertretungenByDay($vertretungsplan->loadPayloadToArray());
            }
        }
    } elseif ($path[0] == "klausuren") {
        if ($path[1] == "all") {
            $klausuren->deleteAll();
        }
    }
}