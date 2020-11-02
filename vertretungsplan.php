<?php
require_once("config/config.php");
require_once("vertretungsplan/index.php");
require_once("applications/index.php");
header('Content-Type: application/json');

$application = new Applications($pdo);
$application->loadRequest();
if(!$application->validate()){
	$output = array();
	$output["access"] = false;
	die(json_encode($output));
}

if(isset($_GET["mode"])){
	$mode = htmlspecialchars($_GET["mode"]);
}else{
	$mode = "";
}

$vplan = new Vertretungsplan($pdo,$config);
if($mode == "edit"){
    $res = array();
	$data = $vplan->loadPayloadToArray();
	$data = $vplan->editJsonPayload($data);

	if(isset($data["vertretungen"]["delete"])){
		$res[] = $vplan->deleteVertretungen($data["vertretungen"]["delete"]);
        echo "DEL";
	}
	if(isset($data["vertretungen"]["insert"])){
	    $res["input"] = $data["vertretungen"]["insert"];
		$res[] = $vplan->insertVertretungen($data["vertretungen"]["insert"]);
	}
    if(isset($data["aufsichten"]["delete"])){
		$res[] = $vplan->deleteAufsichten($data["aufsichten"]["delete"]);
	}
	if(isset($data["aufsichten"]["insert"])){
		$res[] = $vplan->insertAufsichten($data["aufsichten"]["insert"]);
	}
	if(isset($data["config"]["update"])){

		if(isset($data["config"]["update"]["activeDates"])){
			$res[] = $vplan->updateActiveDates($data["config"]["update"]["activeDates"]);
		}
		if(isset($data["config"]["update"]["lastRefreshed"])){
			$res[] = $vplan->updateRefreshedTime($data["config"]["update"]["lastRefreshed"]);
		}
	}

	echo json_encode($res);
}else{

	$vplan->loadInformations();
	$vplan->setDates();
	$vplan->loadVertretungen();
	$vplan->loadAufsichten();
	echo json_encode($vplan->outputData());
}

?>