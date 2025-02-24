<?php

class Hackathon {
    private $db;
    private $table = 'hackathons';

    public function __construct($db) {
        $this->db = $db;
    }

    public function create($data) {
        try {
            $sql = "INSERT INTO {$this->table} (title, description, start_date, end_date, max_participants, status, created_by) 
                    VALUES (:title, :description, :start_date, :end_date, :max_participants, :status, :created_by)";
            
            $stmt = $this->db->prepare($sql);
            
            $stmt->execute([
                ':title' => $data['title'],
                ':description' => $data['description'],
                ':start_date' => $data['start_date'],
                ':end_date' => $data['end_date'],
                ':max_participants' => $data['max_participants'],
                ':status' => $data['status'],
                ':created_by' => $data['created_by']
            ]);
            
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la création du hackathon : " . $e->getMessage());
        }
    }

    public function find($id) {
        $sql = "SELECT * FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function update($id, $data) {
        $sql = "UPDATE {$this->table} SET ";
        $params = [];
        
        foreach ($data as $key => $value) {
            $sql .= "$key = :$key, ";
            $params[":$key"] = $value;
        }
        
        $sql = rtrim($sql, ', ') . " WHERE id = :id";
        $params[':id'] = $id;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete($id) {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function getAll() {
        $sql = "SELECT * FROM {$this->table} ORDER BY start_date DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActive() {
        try {
            $now = date('Y-m-d H:i:s');
            $sql = "SELECT * FROM {$this->table} WHERE start_date <= :now AND end_date >= :now";
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':now', $now);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new Exception("Erreur lors de la récupération des hackathons actifs : " . $e->getMessage());
        }
    }
}
