<?php

require_once("config/config.php");
require_once("klausuren/index.php");
require_once("applications/index.php");
header('Content-Type: application/json');

$application = new Applications($pdo);
$application->loadRequest();
if (!$application->validate()) {
    $output = array();
    $output["access"] = false;
    die(json_encode($output));
}

$klausuren = new Klausuren($pdo, $config);
$json = array();
if (isset($_GET["mode"])) {
    $mode = htmlspecialchars($_GET["mode"]);
} else {
    $mode = "";
}

if ($mode == "edit") {
    $data = $klausuren->loadPayloadToArray();
    $data = $klausuren->editJsonPayload($data);
    if (isset($data["klausuren"]["delete"])) {
        $res = $klausuren->deleteBulk($data["klausuren"]["delete"]);
    }
    if (isset($data["klausuren"]["insert"])) {
        $res = $klausuren->insertBulk($data["klausuren"]["insert"]);
    }
    if (isset($data["klausuren"]["update"])) {
        $res = $klausuren->updateBulk($data["klausuren"]["update"]);
    }

    echo json_encode($res);
} else {

    if (isset($_GET['upcoming'])) {
        $klausuren->setUpcoming();
    }
    if (isset($_GET['date'])) {
        $klausuren->setDate(htmlspecialchars($_GET["date"]));
    }
    if (isset($_GET['teacher'])) {
        $klausuren->setTeacher(htmlspecialchars($_GET["teacher"]));
    }
    if (isset($_GET['active'])) {
        $klausuren->setActive();
    }
    $data = $klausuren->loadData();
    echo json_encode($data);
}
?>