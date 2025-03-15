<?php
namespace Auth\Model;

use Exception;
use PDO;
use PDOException;

class Hackathon {
    private $db;
    private $table = 'hackathons';

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
        // Validation des données
        if (!isset($data['titre']) || empty($data['titre'])) {
            throw new Exception('Le titre est requis');
        }
        if (!isset($data['date_debut']) || empty($data['date_debut'])) {
            throw new Exception('La date de début est requise');
        }
        if (!isset($data['date_fin']) || empty($data['date_fin'])) {
            throw new Exception('La date de fin est requise');
        }

        // Vérification des dates
        $dateDebut = strtotime($data['date_debut']);
        $dateFin = strtotime($data['date_fin']);
        
        if ($dateDebut === false || $dateFin === false) {
            throw new Exception('Format de date invalide');
        }
        
        if ($dateDebut > $dateFin) {
            throw new Exception('La date de début doit être antérieure à la date de fin');
        }

        // Création du hackathon
        return $this->db->create($this->table, [
            'titre' => $data['titre'],
            'description' => $data['description'] ?? '',
            'date_debut' => $data['date_debut'],
            'date_fin' => $data['date_fin'],
            'status' => 'draft'
        ]);
    }

    public function update($id, $data) {
        $hackathon = $this->find($id);
        if (!$hackathon) {
            throw new Exception('Hackathon non trouvé');
        }

        // Validation des données
        if (isset($data['titre']) && empty($data['titre'])) {
            throw new Exception('Le titre ne peut pas être vide');
        }

        if (isset($data['date_debut']) && isset($data['date_fin'])) {
            $dateDebut = strtotime($data['date_debut']);
            $dateFin = strtotime($data['date_fin']);
            
            if ($dateDebut === false || $dateFin === false) {
                throw new Exception('Format de date invalide');
            }
            
            if ($dateDebut > $dateFin) {
                throw new Exception('La date de début doit être antérieure à la date de fin');
            }
        }

        // Mise à jour du hackathon
        $updateData = array_filter([
            'titre' => $data['titre'] ?? null,
            'description' => $data['description'] ?? null,
            'date_debut' => $data['date_debut'] ?? null,
            'date_fin' => $data['date_fin'] ?? null,
            'status' => $data['status'] ?? null
        ], function($value) { return $value !== null; });

        return $this->db->update($this->table, $id, $updateData);
    }

    public function delete($id) {
        $hackathon = $this->find($id);
        if (!$hackathon) {
            throw new Exception('Hackathon non trouvé');
        }

        // Vérifier si le hackathon peut être supprimé
        // TODO: Vérifier s'il y a des équipes inscrites

        return $this->db->delete($this->table, $id);
    }

    public function getActive() {
        $now = date('Y-m-d');
        $hackathons = $this->db->getAll($this->table);
        return array_filter($hackathons, function($h) use ($now) {
            return $h['date_debut'] <= $now && $h['date_fin'] >= $now;
        });
    }

    public function getPast() {
        $now = date('Y-m-d');
        $hackathons = $this->db->getAll($this->table);
        return array_filter($hackathons, function($h) use ($now) {
            return $h['date_fin'] < $now;
        });
    }

    public function getFuture() {
        $now = date('Y-m-d');
        $hackathons = $this->db->getAll($this->table);
        return array_filter($hackathons, function($h) use ($now) {
            return $h['date_debut'] > $now;
        });
    }

    public function getStats($id) {
        $hackathon = $this->find($id);
        if (!$hackathon) {
            return null;
        }

        $equipes = $this->db->query('equipes', ['hackathon_id' => $id]);
        $participants = [];
        $projets = [];
        
        foreach ($equipes as $equipe) {
            $equipeParticipants = $this->db->query('participants', ['equipe_id' => $equipe['id']]);
            $equipeProjets = $this->db->query('projets', ['equipe_id' => $equipe['id']]);
            $participants = array_merge($participants, $equipeParticipants);
            $projets = array_merge($projets, $equipeProjets);
        }

        return [
            'id' => $id,
            'titre' => $hackathon['titre'],
            'nombre_equipes' => count($equipes),
            'nombre_participants' => count($participants),
            'nombre_projets' => count($projets),
            'projets_soumis' => count(array_filter($projets, function($p) {
                return $p['status'] === 'submitted';
            })),
            'projets_evalues' => count(array_filter($projets, function($p) {
                return $p['status'] === 'evaluated';
            }))
        ];
    }

    public function getEquipes($id) {
        $hackathon = $this->find($id);
        if (!$hackathon) {
            throw new Exception('Hackathon non trouvé');
        }

        return $this->db->query('equipes', ['hackathon_id' => $id]);
    }

    public function getProjets($id) {
        $equipes = $this->getEquipes($id);
        $projets = [];
        
        foreach ($equipes as $equipe) {
            $equipeProjets = $this->db->query('projets', ['equipe_id' => $equipe['id']]);
            $projets = array_merge($projets, $equipeProjets);
        }
        
        return $projets;
    }
}
