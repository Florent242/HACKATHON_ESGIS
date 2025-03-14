<?php
namespace Auth\Model;

use Exception;
use PDO;

class Equipe {
    private $db;
    private $table = 'equipes';

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll() {
        return $this->db->getAll($this->table);
    }

    public function find($id) {
        return $this->db->find($this->table, $id);
    }

    public function create($data) {
        if (empty($data['name'])) {
            throw new Exception("Le nom de l'équipe est requis");
        }
        if (empty($data['hackathon_id'])) {
            throw new Exception("L'ID du hackathon est requis");
        }
        return $this->db->create($this->table, $data);
    }

    public function update($id, $data) {
        $equipe = $this->find($id);
        if (!$equipe) {
            throw new Exception("Équipe non trouvée");
        }
        return $this->db->update($this->table, $id, $data);
    }

    public function delete($id) {
        return $this->db->delete($this->table, $id);
    }

    public function getByHackathon($hackathonId) {
        return $this->db->query($this->table, ['hackathon_id' => $hackathonId]);
    }

    public function addMembre($equipeId, $participantId, $role = 'member') {
        $equipe = $this->find($equipeId);
        if (!$equipe) {
            throw new Exception("Équipe non trouvée");
        }

        return $this->db->create('equipe_membres', [
            'equipe_id' => $equipeId,
            'participant_id' => $participantId,
            'role' => $role
        ]);
    }

    public function getMembres($equipeId) {
        $membres = $this->db->query('equipe_membres', ['equipe_id' => $equipeId]);
        $result = [];
        
        foreach ($membres as $membre) {
            $participant = $this->db->find('participants', $membre['participant_id']);
            if ($participant) {
                $participant['role'] = $membre['role'];
                $result[] = $participant;
            }
        }
        
        return $result;
    }

    public function countMembres($equipeId) {
        $membres = $this->db->query('equipe_membres', ['equipe_id' => $equipeId]);
        return count($membres);
    }

    public function removeMembre($equipeId, $participantId) {
        $membres = $this->db->query('equipe_membres', [
            'equipe_id' => $equipeId,
            'participant_id' => $participantId
        ]);
        
        if (!empty($membres)) {
            foreach ($membres as $membre) {
                $this->db->delete('equipe_membres', $membre['id']);
            }
        }
        
        return true;
    }

    public function getProjets($equipeId) {
        return $this->db->query('projets', ['equipe_id' => $equipeId]);
    }
}
