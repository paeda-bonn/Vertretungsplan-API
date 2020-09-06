<?php

//requirements
class Aushang
{
    private $conn;
    private $sqltableAushang;

    public function __construct($conn, $config)
    {
        $this->conn = $conn;
        $this->sqltableAushang = $config["sql"]["table"]["aushang"];
    }

    public function create($type)
    {
        $data = $this->loadPayloadToArray();
        $stmt = $this->conn->prepare("INSERT INTO `" . $this->sqltableAushang . "` ( `Type`, `Kurs`, `Color`, `Content`, `Content2`, `Order`, `Display`, `spalten`) VALUES ( :type, :course, :color, :content1, :content2, :order, :display, :columms);");

        if ($data["content2"] != "") {
            $columms = "true";
            $content2 = $data["content2"];
        } else {
            $content2 = "";
            $columms = "";
        }

        $course = "";
        $order = "99999999";
        $display = "1";

        $stmt->bindParam(':content2', $content2);
        $stmt->bindParam(':columms', $columms);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':course', $course);
        $stmt->bindParam(':color', $data["color"]);
        $stmt->bindParam(':content1', $data["content1"]);
        $stmt->bindParam(':order', $order);
        $stmt->bindParam(':display', $display);

        $stmt->execute();
        $this->order($type);
        return json_encode($data);
    }

    private function loadPayloadToArray()
    {
        $json = file_get_contents("php://input");
        $array = json_decode($json, true);
        return $array;
    }

    private function order($type)
    {
        $row = array();

        $sql = "SELECT * FROM `" . $this->sqltableAushang . "` WHERE `Type`='$type' ORDER BY `Order` ASC";
        $order = 0;
        foreach ($this->conn->query($sql) as $row) {
            $order = $order + 10;
            $row["Order"] = $order;
            $stmt = $this->conn->prepare("UPDATE `" . $this->sqltableAushang . "` SET `Order`= :order WHERE `ID`=:id");

            $stmt->bindParam(':order', $order);
            $stmt->bindParam(':id', $row["ID"]);

            $stmt->execute();
        }
        return $row;
    }

    public function delete()
    {
        $data = $this->loadPayloadToArray();
        $stmt = $this->conn->prepare("UPDATE `" . $this->sqltableAushang . "` SET `Type`='0',`Order`='0',`Display`='0' WHERE `ID`= :id");
        $stmt->bindParam(':id', $data["id"]);
        return $stmt->execute();
    }

    public function update()
    {
        $data = $this->loadPayloadToArray();

        if (isset($data["content2"])) {
            $stmt = $this->conn->prepare("UPDATE `" . $this->sqltableAushang . "` SET `Content`= :content1, `Content2`= :content2, `Color`= :color WHERE `ID`= :id");
            $stmt->bindParam(':content2', $data["content2"]);
        } else {
            $stmt = $this->conn->prepare("UPDATE `" . $this->sqltableAushang . "` SET `Content`= :content1, `Color`= :color WHERE `ID`= :id");

        }
        $stmt->bindParam(':content1', $data["content1"]);
        $stmt->bindParam(':color', $data["color"]);
        $stmt->bindParam(':id', $data["id"]);
        return $stmt->execute();
    }

    public function dataToJson()
    {
        $data = array();

        $sql = "SELECT * FROM `" . $this->sqltableAushang . "` WHERE `Display` = 1 AND `Type`= 1 ORDER BY `Order` ASC";
        foreach ($this->conn->query($sql) as $row) {
            for ($k = 0; $k < count($row); $k++) {
                unset($row[$k]);
            }
            array_push($data, $row);
        }
        return json_encode($data);
    }

    public function getPresets()
    {
        $data = array();

        $sql = "SELECT * FROM `" . $this->sqltableAushang . "` WHERE `Type` = 3 ORDER BY `Order` ASC";
        foreach ($this->conn->query($sql) as $row) {
            for ($k = 0; $k < count($row); $k++) {
                unset($row[$k]);
            }
            array_push($data, $row);
        }
        return json_encode($data);
    }


    public function updateOrder($type)
    {
        $data = array();
        $order_old = 0;
        $order_new = 0;
        $row = array();

        $data = $this->loadPayloadToArray();
        $sql = "SELECT * FROM `" . $this->sqltableAushang . "` WHERE `ID`='" . $data["id"] . "'";
        foreach ($this->conn->query($sql) as $row) {
            $order_old = $row["Order"];
        }
        if ($data["direction"] == "up") {
            $order_new = $order_old - 11;
        } elseif ($data["direction"] == "down") {
            $order_new = $order_old + 11;
        }
        $stmt = $this->conn->prepare("UPDATE `" . $this->sqltableAushang . "` SET `Order`= :order WHERE `ID`= :id");
        $stmt->bindParam(':order', $order_new);
        $stmt->bindParam(':id', $data["id"]);

        $stmt->execute();

        $this->order($type);
        return $row["Order"] . ";" . $order_new . ";" . $data["id"] . ";" . $sql;
    }
}


?>