<?php

class Vertretungsplan
{
    private $vertretungen;
    private $aufsichten;
    private $sqlConn;
    private $refreshed;
    private $dates;

    /**
     * Vertretungsplan constructor.
     * @param $conn
     */
    public function __construct($conn)
    {
        $this->sqlConn = $conn;
    }

    /**
     * @param $date
     * @return array
     */
    public function getVertretungenByDate($date)
    {
        $sql = "SELECT * FROM vertretungsdata WHERE Datum='" . $date . "'";
        $vertretungen = [];

        foreach ($this->sqlConn->query($sql) as $row) {
            for ($i = 0; $i < 12; $i++) {
                unset($row[$i]);
            }
            $vertretungen[] = $row;
        }

        return $vertretungen;
    }

    /**
     * @return bool
     */
    public function loadAufsichten()
    {
        $sql = "SELECT * FROM aufsichten";
        $aufsichten = array();
        foreach ($this->sqlConn->query($sql) as $row) {
            for ($i = 0; $i < 12; $i++) {
                unset($row[$i]);
            }
            $aufsichten[$row["Datum"]][] = $row;
        }
        $this->aufsichten = $aufsichten;

        return true;
    }

    /**
     * @return false|string[]
     */
    public function getActiveDates()
    {
        $sql = "SELECT * FROM config WHERE `Name`='vertretungsplan_active_days'";
        $row = array();
        foreach ($this->sqlConn->query($sql) as $row) {
        }
        $date = $row["Value"];

        $this->dates = array();
        $dates = explode(",", $date);
        foreach ($dates as $date) {
            $this->dates[] = $date;
        }
        return $dates;
    }

    /**
     * @param $daysArray
     * @return mixed
     */
    public function setActiveDates($daysArray)
    {

        $stmt = $this->sqlConn->prepare("UPDATE config SET `Value` = :value WHERE `Name`='vertretungsplan_active_days';");
        $days = "";
        $i = 0;
        foreach ($daysArray as $day) {
            if ($i != 0) {
                $days = $days . ",";
            } else {
                $i++;
            }
            $days = $days . $day;
        }
        $stmt->bindParam(':value', $days);
        return $stmt->execute();
    }

    /**
     * @param $time
     * @return mixed
     */
    public function updateRefreshedTime($time)
    {
        $stmt = $this->sqlConn->prepare("UPDATE config SET `Value` = :value WHERE `Name`='vertretungsplan_refreshed';");

        $stmt->bindParam(':value', $time);

        return $stmt->execute();
    }

    /**
     * @return mixed
     */
    public function loadPayloadToArray()
    {
        $json = file_get_contents("php://input");
        $array = json_decode($json, true);
        return ($array);
    }

    /**
     * @param $array
     * @return array
     */
    public function deleteVertretungenByID($array)
    {
        $output = array();
        $stmt = $this->sqlConn->prepare("DELETE FROM vertretungsdata WHERE (`id` = :id);");
        foreach ($array as $id) {
            $stmt->bindParam(':id', $id);
            $output[$id] = $stmt->execute();
        }

        return $output;
    }

    /**
     * @param $array
     * @return array
     */
    public function deleteVertretungenByDay($array)
    {
        $output = array();
        $stmt = $this->sqlConn->prepare("DELETE FROM vertretungsdata WHERE (`Datum` = :date);");
        foreach ($array as $date) {
            $stmt->bindParam(':date', $date);
            $output[$date] = $stmt->execute();
        }

        return $output;
    }

    /**
     * @param $array
     * @return array
     */
    public function insertVertretungen($array)
    {
        $output = array();
        $stmt = $this->sqlConn->prepare("INSERT INTO vertretungsdata (`id`, `Datum`, `Stunde`, `Lehrer`, `Kurs`,`Fach`, `LehrerNeu`, `RaumNew`, `FachNew`, `info`) VALUES (:id, :date, :lesson, :teacher, :class, :subject, :newTeacher, :newRoom, :newSubject, :info) ON DUPLICATE KEY UPDATE `Datum`= :date,`LehrerNeu`= :newTeacher,`RaumNew`= :newRoom, `FachNew`= :newSubject, `info`=:info");

        foreach ($array as $entry) {
            $stmt->bindParam(':id', $entry["id"]);
            $stmt->bindParam(':date', $entry["date"]);
            $stmt->bindParam(':lesson', $entry["lesson"]);
            $stmt->bindParam(':teacher', $entry["teacher"]);
            $stmt->bindParam(':class', $entry["class"]);
            $stmt->bindParam(':subject', $entry["subject"]);
            $stmt->bindParam(':newTeacher', $entry["newTeacher"]);
            if (is_array($entry["newRoom"])) {
                $stmt->bindParam(':newRoom', $entry["newRoom"]);
            } else {
                $stmt->bindParam(':newRoom', $entry["newRoom"]);
            }

            $stmt->bindParam(':newSubject', $entry["newSubject"]);
            $stmt->bindParam(':info', $entry["info"]);

            $output[] = $stmt->execute();
            $output[] = $stmt->errorInfo();
        }
        return $output;
    }

    /**
     * @param $array
     * @return array
     */
    public function insertAufsichten($array)
    {
        $output = array();
        $stmt = $this->sqlConn->prepare("INSERT INTO aufsichten (`date`, `time`, `teacher`, `location`) VALUES (:date, :time, :teacher, :location);");

        foreach ($array as $entry) {
            $stmt->bindParam(':date', $entry["date"]);
            $stmt->bindParam(':time', $entry["time"]);
            $stmt->bindParam(':teacher', $entry["teacher"]);
            $stmt->bindParam(':location', $entry["location"]);

            $output[$entry["id"]] = $stmt->execute();
        }
        return $output;
    }


    /**
     * @param $array
     * @return mixed
     */
    public function deleteAufsichten($array)
    {

        $stmt = $this->sqlConn->prepare("DELETE FROM aufsichten WHERE (`idaufsichten` = :id);");
        foreach ($array as $id) {
            $stmt->bindParam(':id', $id);
            $stmt->execute();
        }

        return $array;
    }

    /**
     * @param $data
     * @return array
     */
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

    /**
     * @return array
     */
    public function outputData()
    {
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