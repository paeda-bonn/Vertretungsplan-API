<?php

class Aushang
{
    private $conn;

    /**
     * Aushang constructor.
     * @param $conn
     */
    public function __construct($conn)
    {
        $this->conn = $conn;
    }

    /**
     * @param $data
     * @return false|string
     */
    public function create($data)
    {
        $event = new AushangEvent($data["content"], $data["color"], $data["display"], $data["type"]);
        $stmt = $this->conn->prepare("INSERT INTO aushang2 (type, color, content, `order`, displaying) VALUES ( :type, :color, :content, :order, :display);");

        $order = "99999999";
        $display = true;

        $stmt->bindParam(':type', $event->getType());
        $stmt->bindParam(':color', $event->getColor());
        $stmt->bindParam(':content', json_encode($event->getContent()));

        $stmt->bindParam(':order', $order);
        $stmt->bindParam(':display', $display);

        $stmt->execute();
        $this->order($event->getType());
        return json_encode($event);
    }

    /**
     * @param $type
     * @return array
     */
    private function order($type)
    {
        $row = array();
        echo "order";
        $sql = "SELECT * FROM aushang2 WHERE `type`='$type' ORDER BY `order`";
        $order = 0;
        foreach ($this->conn->query($sql) as $row) {
            $order = $order + 10;
            $row["order"] = $order;
            $stmt = $this->conn->prepare("UPDATE aushang2 SET `order`= :order WHERE `id`=:id");

            $stmt->bindParam(':order', $order);
            $stmt->bindParam(':id', $row["id"]);

            $stmt->execute();
        }
        return $row;
    }

    /**
     * @param int $id
     * @return mixed
     */
    public function deleteById(int $id)
    {
        $stmt = $this->conn->prepare("UPDATE aushang2 SET `type`='0',`order`='0',`displaying`='0' WHERE `id`= :id");
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * @param $event
     * @return mixed
     */
    public function update($event)
    {

        $stmt = $this->conn->prepare("UPDATE aushang2 SET `content`= :content, `color`= :color WHERE `id`= :id");


        $stmt->bindParam(':content', $event["content"]);
        $stmt->bindParam(':color', $event["color"]);
        $stmt->bindParam(':id', $event["id"]);
        return $stmt->execute();
    }

    /**
     * @return array
     */
    public function getAll()
    {
        $data = array();

        $sql = "SELECT * FROM aushang2 WHERE `displaying` = 1 AND `type`= 1 ORDER BY `order`";
        foreach ($this->conn->query($sql) as $row) {
            for ($k = 0; $k < count($row); $k++) {
                unset($row[$k]);

            }
            $row["content"] = json_decode($row["content"]);
            array_push($data, $row);
        }
        return $data;
    }

    /**
     * @return array
     */
    public function getPresets()
    {
        $data = array();

        $sql = "SELECT * FROM aushang2 WHERE `Type` = 3 ORDER BY `Order`";
        foreach ($this->conn->query($sql) as $row) {
            for ($k = 0; $k < count($row); $k++) {
                unset($row[$k]);
            }
            $row["content"] = json_decode($row["content"]);
            array_push($data, $row);
        }
        return $data;
    }

    /**
     * @param $id
     * @param $direction
     * @return string
     */
    public function updateOrder($id, $direction)
    {
        $data = array();
        $order_old = 0;
        $order_new = 0;
        $row = array();
        $type = 5;
        $sql = "SELECT * FROM aushang2 WHERE `id`='" . $id . "'";
        foreach ($this->conn->query($sql) as $row) {
            $order_old = $row["order"];
            $type = $row["type"];

        }

        if ($direction == "up") {
            $order_new = $order_old - 11;
        } elseif ($direction == "down") {
            $order_new = $order_old + 11;
        }
        $stmt = $this->conn->prepare("UPDATE aushang2 SET `order`= :order WHERE `id`= :id");
        $stmt->bindParam(':order', $order_new);
        $stmt->bindParam(':id', $id);

        $stmt->execute();

        $this->order($type);

        return $row["Order"] . ";" . $order_new . ";" . $data["id"] . ";" . $sql;
    }
}


?>