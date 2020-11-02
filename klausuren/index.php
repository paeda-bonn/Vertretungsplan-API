<?php

class Klausuren {
	private $sqlConn;
	private $upcoming;
	private $active;
	private $date;
	private $teacher;


	function __construct($conn,$config){
		$this->sqlConn = $conn;
		$this->sqlTable = $config["sql"]["table"]["klausuren"];
	}

	public function setUpcoming(){
		$this->upcoming = "true";
	}

	public function setActive(){
		$this->active = "x";
	}

	public function setDate($date){
		$this->date = $date;
	}

	public function setTeacher($teacher){
		$this->teacher = $teacher;
	}


	function loadData(){

        $where = NULL;

		if($this->id != "" OR $this->id != NULL){
			$where = "WHERE id = :id";
		}
		if($this->active != "" OR $this->active != NULL){
			if(!isset($where)){
				$where = "WHERE";
			}else{
				$where .= " AND";
			}

			$where .= " Anzeigen = :active";
		}

		if($this->date != "" OR $this->date != NULL){
			if(!isset($where)){
				$where = "WHERE";
			}else{
				$where .= " AND";
			}

			$where .= " Datum = :date";
		}

		if($this->teacher != "" OR $this->teacher != NULL){
			if(!isset($where)){
				$where = "WHERE";
			}else{
				$where .= " AND";
			}

			$where .= " Lehrer LIKE :teacher";
		}

		if($this->upcoming){
			if(isset($where)){
				$where .= " AND";
			}else{
				$where = "WHERE";
			}
			$where .= " (`Exceldatum`> :exceldatum OR (`Exceldatum`=:exceldatum AND `Bis`> :time))";
		}


		$stmt = $this->sqlConn->prepare("SELECT * FROM `".$this->sqlTable."` ".$where." ORDER BY `Exceldatum` asc");

		if($this->id != "" OR $this->id != NULL){
			$stmt->bindParam(':id', $this->id );
		}
		if($this->active != "" OR $this->active != NULL){
			$stmt->bindParam(':active', $this->active );
		}
		if($this->date != "" OR $this->date != NULL){
			$stmt->bindParam(':date', $this->date );
		}
		if($this->teacher != "" OR $this->teacher != NULL){
			$teacher = $this->teacher."%";
			$stmt->bindParam(':teacher', $teacher );
		}
		if($this->upcoming){

			$timestamp = time();
			$zeit = date("H:i:s");
			
			$exceldatum = floor($timestamp / 86400 + 25569);
			$stmt->bindParam(':exceldatum', $exceldatum );
			$stmt->bindParam(':time', $zeit );
		}
		
		$stmt->execute();

		return $stmt->fetchAll(PDO::FETCH_CLASS);
	}

	public function loadPayloadToArray(){

		$json = file_get_contents("php://input");
		$array = json_decode($json,true);

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

	public function updateBulk($array){

		return $array;
	}

	public function insertBulk($array){

		$output = array();

		$stmt = $this->sqlConn->prepare("INSERT INTO `klausuren` (`Datum`, `ExcelDatum`, `UnixDatum`, `Von`, `Bis`, `Anzeigen`, `Std`, `Stufe`, `Kurs`, `Lehrer`, `Raum`, `1`, `2`, `3`, `4`, `5`, `6`, `7`) VALUES (:date , :excelDate, :unixTime, :from, :to, :display, :lesson, :grade, :course, :teacher, :room, :lessonOne, :lessonTwo, :lessonThree, :lessonFour, :lessonFive, :lessonSix, :lessonSeven);");
		
        foreach($array as $dataset){
			$stmt->bindParam(':date', $dataset["date"] );		
			$stmt->bindParam(':excelDate', $dataset["excelDate"] );		
			$stmt->bindParam(':unixTime', $dataset["unixtime"] );		
			$stmt->bindParam(':from', $dataset["from"] );		
			$stmt->bindParam(':to', $dataset["to"] );		
			$stmt->bindParam(':display', $dataset["display"] );		
			$stmt->bindParam(':lesson', $dataset["lesson"] );		
			$stmt->bindParam(':grade', $dataset["grade"] );		
			$stmt->bindParam(':course', $dataset["course"] );		
			$stmt->bindParam(':teacher', $dataset["teacher"] );		
			$stmt->bindParam(':room', $dataset["room"] );		
			$stmt->bindParam(':lessonOne', $dataset["lessonOne"] );		
			$stmt->bindParam(':lessonTwo', $dataset["lessonTwo"] );		
			$stmt->bindParam(':lessonThree', $dataset["lessonThree"] );		
			$stmt->bindParam(':lessonFour', $dataset["lessonFour"] );		
			$stmt->bindParam(':lessonFive', $dataset["lessonFive"] );		
			$stmt->bindParam(':lessonSix', $dataset["lessonSix"] );		
			$stmt->bindParam(':lessonSeven', $dataset["lessonSeven"] );

			$stmt->execute();

			$output[] = $stmt->errorInfo();
		}
		
		return $output;
	}

	public function deleteBulk($array){

		$output = array();

		$stmt = $this->sqlConn->prepare("DELETE FROM `".$this->sqlTable."` WHERE `id` LIKE :id");
		foreach($array as $id){
			$stmt->bindParam(':id', $id );

			$output[$id] = $stmt->execute();
		}

		return $output;
	}
}
?>