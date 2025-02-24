<?php

class Database {
    private static $instance = null;
    private $connection = null;

    // Configuration de la base de données
    private $host = 'localhost';
    private $dbname = 'hackathon_db';
    private $username = 'root';
    private $password = '';

    private function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->dbname};charset=utf8",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch(PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }

    // Pattern Singleton pour une seule instance de connexion
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Obtenir la connexion
    public function getConnection() {
        return $this->connection;
    }

    // Empêcher la copie de l'instance
    private function __clone() {}
    private function __wakeup() {}
}

// Retourner l'instance de connexion
return Database::getInstance()->getConnection();