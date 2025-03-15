<?php
namespace Auth\Model;

use Exception;

class Projet {
    private $db;
    private $table = 'projets';

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
        if (empty($data['titre'])) {
            throw new Exception("Le titre du projet est requis");
        }
        if (empty($data['description'])) {
            throw new Exception("La description du projet est requise");
        }
        if (empty($data['equipe_id'])) {
            throw new Exception("L'ID de l'équipe est requis");
        }
        return $this->db->create($this->table, $data);
    }

    public function update($id, $data) {
        $projet = $this->find($id);
        if (!$projet) {
            throw new Exception("Projet non trouvé");
        }
        return $this->db->update($this->table, $id, $data);
    }

    public function delete($id) {
        return $this->db->delete($this->table, $id);
    }

    public function getByEquipe($equipeId) {
        $projets = $this->db->query($this->table, ['equipe_id' => $equipeId]);
        foreach ($projets as &$projet) {
            $evaluations = $this->db->query('evaluations', ['projet_id' => $projet['id']]);
            $projet['nombre_evaluations'] = count($evaluations);
            if (!empty($evaluations)) {
                $scores = array_column($evaluations, 'score');
                $projet['moyenne_score'] = round(array_sum($scores) / count($scores), 2);
            } else {
                $projet['moyenne_score'] = 0;
            }
        }
        return $projets;
    }

    public function getByHackathon($hackathonId) {
        $projets = [];
        $equipes = $this->db->query('equipes', ['hackathon_id' => $hackathonId]);
        foreach ($equipes as $equipe) {
            $projetEquipe = $this->getByEquipe($equipe['id']);
            foreach ($projetEquipe as &$projet) {
                $projet['equipe_nom'] = $equipe['name'];
            }
            $projets = array_merge($projets, $projetEquipe);
        }
        return $projets;
    }

    public function getEvaluations($projetId) {
        return $this->db->query('evaluations', ['projet_id' => $projetId]);
    }

    public function countByStatus($hackathonId, $status = null) {
        $count = 0;
        $equipes = $this->db->query('equipes', ['hackathon_id' => $hackathonId]);
        foreach ($equipes as $equipe) {
            $query = ['equipe_id' => $equipe['id']];
            if ($status !== null) {
                $query['status'] = $status;
            }
            $projets = $this->db->query($this->table, $query);
            $count += count($projets);
        }
        return $count;
    }

    public function submitProject($id, $repoUrl, $demoUrl = null) {
        $projet = $this->find($id);
        if (!$projet) {
            throw new Exception("Projet non trouvé");
        }

        $data = [
            'repository_url' => $repoUrl,
            'status' => 'submitted'
        ];

        if ($demoUrl) {
            $data['demo_url'] = $demoUrl;
        }

        return $this->update($id, $data);
    }
}
