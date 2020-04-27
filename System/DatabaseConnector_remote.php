<?php

namespace System;

class DatabaseConnector
{

    private $dbConnection = null;

    public function __construct()
    {
        $host = "localhost";//getenv('DB_HOST');
        $port = "3306";//getenv('DB_PORT');
        $db   = "c1440662_sae_web";//getenv('DB_DATABASE');
        $user = "c1440662_sae_web";//getenv('DB_USERNAME');
        $pass = "se36VIbemi";//getenv('DB_PASSWORD');
        //echo "DatabaseConnector" . "host=" . $host . ";port=" . $port . ";charset=utf8mb4;dbname=" . $db . "," . $user . "," . $pass . "\n";
        try {
            $this->dbConnection = new \PDO(
                "mysql:host=$host;port=$port;charset=utf8mb4;dbname=$db",
                $user,
                $pass
            );
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->dbConnection;
    }
}
