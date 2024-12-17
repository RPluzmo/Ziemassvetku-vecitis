<?php
require_once 'config.php';

class Database {
    private $conn;

    public function __construct() {
        $this->conn = new mysqli('server43.areait.lv', 'jekabsar_reader', 'Christmas2022', 'jekabsar_christmas');
        if ($this->conn->connect_error) {
            die("Savienojuma kļūda: " . $this->conn->connect_error);
        }
    }
    // Getter to access the connection
    public function getConnection() {
        return $this->conn;
    }
}
?>
