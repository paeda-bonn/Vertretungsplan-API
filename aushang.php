<?php
require_once("aushang/index.php");
require_once("config/config.php");
require_once("applications/index.php");

$application = new Applications($pdo);
$application->loadRequest();
if (!$application->validate()) {
    $output = array();
    $output["access"] = false;
    die(json_encode($output));
}

$aushang = new Aushang($pdo, $config);
if (isset($_GET["aushang"])) {
    $mode = htmlspecialchars($_GET["aushang"]);
} else {
    $mode = "";
}

if ($mode == "create") {
    echo $aushang->create("1");
} elseif ($mode == "createPreset") {
    echo $aushang->create("3");
} elseif ($mode == "delete") {
    echo $aushang->delete();
} elseif ($mode == "update") {
    echo $aushang->update();
} elseif ($mode == "updateOrder") {
    echo $aushang->updateOrder("1");
} elseif ($mode == "updateOrderPreset") {
    echo $aushang->updateOrder("3");
} elseif ($mode == "presets") {
    echo $aushang->getPresets();
} else {
    $result = $aushang->dataToJson();
    echo $result;
}
?>