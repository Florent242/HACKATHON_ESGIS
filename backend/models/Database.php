<?php
namespace Auth\Model;

use PDO;
use PDOException;
use Exception;

class Database {
    private static $instance = null;
    private $connection = null;

    private $host = '127.0.0.1';
    private $dbname = 'new_db';
    private $username = 'root';
    private $password = '';

    private function __construct() {
        try {
            error_log("Tentative de connexion à la base de données - Host: {$this->host}, DB: {$this->dbname}");
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8";
            error_log("DSN: {$dsn}");

            $this->connection = new PDO(
                $dsn,
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            error_log("Connexion à la base de données établie avec succès");
        } catch(PDOException $e) {
            $error = "Erreur de connexion à la base de données: " . $e->getMessage();
            error_log($error);
            die($error);
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    private function __clone() {}
    
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}
