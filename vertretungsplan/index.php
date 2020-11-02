<?php

class Vertretungsplan
{
	private $lastrefresh;
	private $vertretungen;
	private $aufsichten;
	private $sqlConn;
	private $refreshed;
	private $dates;
	private $sqlTableVplan;
	private $sqlTableConfig;
	private $sqlTableAufsichten;

	public function __construct($conn,$config){
		$this->sqlConn = $conn;
		$this->sqlTableVplan = $config["sql"]["table"]["vertretungen"];
		$this->sqlTableConfig = $config["sql"]["table"]["config"];
		$this->sqlTableAufsichten = $config["sql"]["table"]["aufsichten"];
	}

	private function correctDate($input){

		$explode = explode("-",$input);
		$year = $explode[0];
		$month = $explode[1];
		$day = $explode[2];

		if(strlen($month) == 1){
			$month = "0".$month;
		}
		if(strlen($day) == 1){
			$day = "0".$day;
		}

		$date = $year."-".$month."-".$day;

		return $date;
	}

	public function loadVertretungen(){
        $vertretungen = array();
		$where = "WHERE";

		for($i = 0; $i < count($this->dates);$i++){
			if($i == 0){
				$where .= "`Datum`='".$this->dates[$i]."'";
			}else{
				$where .= " OR `Datum`='".$this->dates[$i]."'";
			}
		}

		if(isset($_GET["order"])){
			$order = htmlspecialchars($_GET["order"]);
			$sql = "SELECT * FROM ".$this->sqlTableVplan." ".$where." ORDER BY ".$order." asc";
		}else{
			$sql = "SELECT * FROM ".$this->sqlTableVplan." ".$where." ORDER BY `Datum` ,`Kurs` asc";
		}


		foreach ($this->sqlConn->query($sql) as $row) {
			for($i = 0; $i < 12; $i++){
				unset($row[$i]);
			}
			$vertretungen[$row["Datum"]][] = $row;
		}

		$this->vertretungen = $vertretungen;
	}

	public function loadAufsichten(){
		$sql = "SELECT * FROM ".$this->sqlTableAufsichten;
		$aufsichten = array();
		foreach ($this->sqlConn->query($sql) as $row) {
			for($i = 0; $i < 12; $i++){
				unset($row[$i]);
			}
			$aufsichten[$row["Datum"]][] = $row;
		}
		$this->aufsichten = $aufsichten;

        return true;
	}

	public function loadInformations(){
		$sql = "SELECT * FROM ".$this->sqlTableConfig." WHERE `Name`='vertretungsplan_refreshed'";
        $refreshed = NULL;
		foreach ($this->sqlConn->query($sql) as $row) {
			$refreshed = $row["Value"];
		}
		$this->refreshed = $refreshed;
	}

	private function loadActiveDates(){
		$sql = "SELECT * FROM ".$this->sqlTableConfig." WHERE `Name`='vertretungsplan_active_days'";
        $row = array();
		foreach ($this->sqlConn->query($sql) as $row) {}
		$date = $row["Value"];

		$this->dates = array();
		$dates = explode(",",$date);
		foreach($dates as $date){
			$this->dates[] = $this->correctDate($date);
		}
	}

	public function setDates(){
		if(isset($_GET["dates"])){
			$dates = htmlspecialchars($_GET["dates"]);
			if(strpos($dates,",")){
				$date = explode(",",$dates);
				foreach($date as $datum){
					$this->dates[] = $datum;
				}
			}else{
				$this->dates[] = $dates;
			}
		}else{
			$this->loadActiveDates();
		}
	}

	public function updateActiveDates($days){
		$stmt = $this->sqlConn->prepare("UPDATE ".$this->sqlTableConfig." SET `Value` = :value WHERE `Name`='vertretungsplan_active_days';");

		$stmt->bindParam(':value', $days);
		return $stmt->execute();
	}
	public function updateRefreshedTime($time){
		$stmt = $this->sqlConn->prepare("UPDATE ".$this->sqlTableConfig." SET `Value` = :value WHERE `Name`='vertretungsplan_refreshed';");

		$stmt->bindParam(':value', $time);
		$sql = "INSERT INTO `log` (`ID`, `level`, `message`) VALUES ('', '0', 'Vplan Inserted: ".$time.";".time()."');";
		$this->sqlConn->query($sql);

		return $stmt->execute();
	}

	public function loadPayloadToArray(){
		$json = file_get_contents("php://input");
		$array = json_decode($json,true);
		return($array);
	}

	public function deleteVertretungen($array){
        $output = array();
        $stmt = $this->sqlConn->prepare("DELETE FROM `".$this->sqlTableVplan."` WHERE (`id` = :id);");
		foreach($array as $id){
            $stmt->bindParam(':id', $id);
            $output[$id] = $stmt->execute();
		}

		return $output;
	}

	public function insertVertretungen($array){
		$output = array();
		$stmt = $this->sqlConn->prepare("INSERT INTO ".$this->sqlTableVplan." (`id`, `Datum`, `Stunde`, `Lehrer`, `Kurs`,`Fach`, `LehrerNeu`, `RaumNew`, `FachNew`, `info`) VALUES (:id, :date, :lesson, :teacher, :class, :subject, :newTeacher, :newRoom, :newSubject, :info) ON DUPLICATE KEY UPDATE `Datum`= :date,`LehrerNeu`= :newTeacher,`RaumNew`= :newRoom, `FachNew`= :newSubject, `info`=:info");

		foreach($array as $entry){
			$stmt->bindParam(':id', $entry["id"]);
			$stmt->bindParam(':date', $entry["date"]);
			$stmt->bindParam(':lesson', $entry["lesson"]);
			$stmt->bindParam(':teacher', $entry["teacher"]);
			$stmt->bindParam(':class', $entry["class"]);
			$stmt->bindParam(':subject', $entry["subject"]);
			$stmt->bindParam(':newTeacher', $entry["newTeacher"]);
			if(is_array($entry["newRoom"])){
				$stmt->bindParam(':newRoom', $entry["newRoom"]);
			}else{
				$stmt->bindParam(':newRoom', $entry["newRoom"]);
			}

			$stmt->bindParam(':newSubject', $entry["newSubject"]);
			$stmt->bindParam(':info', $entry["info"]);

			$output[] = $stmt->execute();
			$output[] = $stmt->errorInfo();
		}
		return $output;
	}

    public function insertAufsichten($array){
		$output = array();
		$stmt = $this->sqlConn->prepare("INSERT INTO ".$this->sqlTableAufsichten." (`Datum`, `Zeit`, `Lehrer`, `Ort`) VALUES (:date, :time, :teacher, :location);");

		foreach($array as $entry){
			$stmt->bindParam(':date', $entry["date"]);
			$stmt->bindParam(':time', $entry["time"]);
			$stmt->bindParam(':teacher', $entry["teacher"]);
			$stmt->bindParam(':location', $entry["location"]);

			$output[$entry["id"]] = $stmt->execute();
		}
		return $output;
	}


    public function deleteAufsichten($array){

        $stmt = $this->sqlConn->prepare("DELETE FROM `".$this->sqlTableAufsichten."` WHERE (`id` = :id);");
		foreach($array as $id){
            $stmt->bindParam(':id', $id);
            $stmt->execute();
		}

		return $array;
	}
	public function editJsonPayload($data){
		$output = array();

		foreach($data as $operator){
			$operationMode = $operator["mode"];
			$type = $operator["type"];
			$datasets = $operator["data"];
			$output[$type][$operationMode] = $datasets;
		}

		return $output;
	}

	public function outputData(){
		$output = array();
		$output["info"] = array();

		$output["info"]["days"] = $this->dates;
		$output["info"]["refreshed"] = $this->refreshed;
		$output["data"]["vertretungen"] = $this->vertretungen;
		$output["data"]["aufsichten"] = $this->aufsichten;

		return $output;
	}
}
?>