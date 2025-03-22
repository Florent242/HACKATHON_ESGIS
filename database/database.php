<?php
namespace Auth\Database;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

class Database {
    private $host = "localhost";
    private $db_name = "hackathon_db";
    private $username = "root";
    private $password = "";
    private $conn;

    public function getConnection() {
        $this->conn = null;
        try {
            $this->conn = new \PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch(\PDOException $e) {
            echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
            exit();
        }
        return $this->conn;
    }

    public function getAllData() {
        try {
            $db = $this->getConnection();
            $data = [];

            // Récupérer tous les utilisateurs
            $query = "SELECT * FROM users";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $data['users'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Récupérer tous les challenges
            $query = "SELECT * FROM challenges";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $data['challenges'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Récupérer toutes les équipes
            $query = "SELECT * FROM equipes";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $data['equipes'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Récupérer toutes les évaluations
            $query = "SELECT * FROM evaluations";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $data['evaluations'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            // Récupérer tous les hackathons
            $query = "SELECT * FROM hackathons";
            $stmt = $db->prepare($query);
            $stmt->execute();
            $data['hackathons'] = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            echo json_encode([
                "status" => "success",
                "data" => $data
            ]);

        } catch(\PDOException $e) {
            echo json_encode([
                "status" => "error",
                "message" => $e->getMessage()
            ]);
        }
    }
}

// Instancier la base de données et récupérer toutes les données
$database = new Database();
$database->getAllData();