<?php

class Klausuren
{
    private $sqlConn;


    function __construct($conn)
    {
        $this->sqlConn = $conn;
    }

    public function setUpcoming()
    {
        $upcoming = "true";
    }

    public function setActive()
    {
        $active = "x";
    }

    public function setDate($date)
    {
        $date1 = $date;
    }

    public function setTeacher($teacher)
    {
        $teacher1 = $teacher;
    }

    public function loadPayloadToArray()
    {

        $json = file_get_contents("php://input");
        return json_decode($json, true);
    }

    public function editJsonPayload($data)
    {
        $output = array();
        foreach ($data as $operator) {
            $operationMode = $operator["mode"];
            $type = $operator["type"];
            $datasets = $operator["data"];

            $output[$type][$operationMode] = $datasets;
        }
        return $output;
    }

    public function updateBulk($array)
    {

        return $array;
    }

    public function deleteBulk($array)
    {

        $stmt = $this->sqlConn->prepare("DELETE FROM klausuren2 WHERE `id` LIKE :id");

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_CLASS);
    }

    //GET

    public function getAll()
    {
        $stmt = $this->sqlConn->prepare("SELECT * FROM klausuren2 ");
        $stmt->execute();
        return $this->formatArray($stmt->fetchAll(PDO::FETCH_CLASS));
    }

    private function formatArray($data)
    {
        for ($i = 0; $i < sizeof($data); $i++) {
            $dataset = $data[$i];
            $dataset->supervisors = json_decode($dataset->supervisors);

            if ($data[$i]->active == true) $dataset->active = true; else $dataset->active = false;

            $data[$i] = $dataset;
        }
        return $data;
    }

    public function getActive()
    {
        $timeElement = date('H:i',time());
        $stmt = $this->sqlConn->prepare("SELECT * FROM klausuren2 WHERE `active`=1 && (date > CURRENT_DATE() ||(date = CURRENT_DATE() && `to`>='".$timeElement."'))");
        $stmt->execute();
        return $this->formatArray($stmt->fetchAll(PDO::FETCH_CLASS));
    }

    public function getUpcoming()
    {
        $stmt = $this->sqlConn->prepare("SELECT * FROM klausuren2 WHERE date > CURRENT_DATE()");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_CLASS);
    }

    public function getByDate($date)
    {
        $stmt = $this->sqlConn->prepare("SELECT * FROM klausuren2 WHERE `date` LIKE :date");
        $stmt->bindParam(':date', $date);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_CLASS);
    }

    //Insert
    public function insertArray($array)
    {
        $output = array();

        $stmt = $this->sqlConn->prepare("INSERT INTO `klausuren2` (date, active, `from`, `to`, grade, course, teacher, room, supervisors) VALUES ( :date, :active, :from, :to, :grade, :course, :teacher, :room, :supervisors);");

        foreach ($array as $dataset) {
            $stmt->bindParam(':date', $dataset["date"]);
            $stmt->bindParam(':from', $dataset["from"]);
            $stmt->bindParam(':to', $dataset["to"]);
            $stmt->bindParam(':active', $dataset["active"]);
            $stmt->bindParam(':grade', $dataset["grade"]);
            $stmt->bindParam(':course', $dataset["course"]);
            $stmt->bindParam(':teacher', $dataset["teacher"]);
            $stmt->bindParam(':room', $dataset["room"]);
            $stmt->bindParam(':supervisors', json_encode($dataset["supervisors"]));

            $stmt->execute();

            $output[] = $stmt->errorInfo();
        }
        return $output;
    }

    //Delete
    public function deleteAll()
    {
        $stmt = $this->sqlConn->prepare("TRUNCATE klausuren2");
        return $stmt->execute();
    }
}

?>