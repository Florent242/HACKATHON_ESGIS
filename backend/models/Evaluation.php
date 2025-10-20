<?php
namespace Auth\Model;

use Exception;

class Evaluation {
    private $db;
    private $table = 'evaluations';

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
        try {
            $sql = "INSERT INTO {$this->table} (
                        project_id, 
                        judge_id, 
                        score, 
                        criteria, 
                        comments, 
                        created_at
                    ) VALUES (
                        :project_id, 
                        :judge_id, 
                        :score, 
                        :criteria, 
                        :comments, 
                        NOW()
                    )";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':project_id' => $data['project_id'],
                ':judge_id' => $data['judge_id'],
                ':score' => $data['score'],
                ':criteria' => $data['criteria'],
                ':comments' => $data['comments']
                
            ]);

            if (!$result) {
                throw new Exception('Erreur lors de la création de l\'évaluation');
            }

            return $this->db->lastInsertId();

        } catch (PDOException $e) {
            throw new Exception('Erreur base de données : ' . $e->getMessage());
        }
    }

     public function update($id, $data) {
        try {
            $sql = "UPDATE {$this->table} SET ";
            $fields = [];
            $params = [':id' => $id];

            if (isset($data['score'])) {
                $fields[] = "score = :score";
                $params[':score'] = $data['score'];
            }
            if (isset($data['criteria'])) {
                $fields[] = "criteria = :criteria";
                $params[':criteria'] = $data['criteria'];
            }
            if (isset($data['comments'])) {
                $fields[] = "comments = :comments";
                $params[':comments'] = $data['comments'];
            }
            
            $fields[] = "updated_at = NOW()";
            $sql .= implode(', ', $fields) . " WHERE id = :id";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);

        } catch (PDOException $e) {
            throw new Exception('Erreur lors de la mise à jour : ' . $e->getMessage());
        }
    }

   public function delete($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);

        } catch (PDOException $e) {
            throw new Exception('Erreur lors de la suppression : ' . $e->getMessage());
        }
    }


    public function getByProjet($projetId) {
        return $this->db->query($this->table, ['projet_id' => $projetId]);
    }

    public function getByJury($juryId) {
        return $this->db->query($this->table, ['juge_id' => $juryId]);
    }

    /**
     * Récupère les évaluations d'un projet (nouvelle méthode compatible avec le contrôleur)
     * @param int $projectId ID du projet
     * @return array
     */
    public function getByProject($projectId) {
        // Vérifier si le schéma de la table utilise 'projet_id' ou 'project_id'
        try {
            return $this->db->query($this->table, ['project_id' => $projectId]);
        } catch (Exception $e) {
            // Si échoue avec 'project_id', essayer avec 'projet_id'
            return $this->getByProjet($projectId);
        }
    }

    /**
     * Récupère les évaluations d'un juge (nouvelle méthode compatible avec le contrôleur)
     * @param int $judgeId ID du juge
     * @return array
     */
    public function getByJudge($judgeId) {
        // Vérifier si le schéma de la table utilise 'juge_id' ou 'jury_id'
        try {
            return $this->db->query($this->table, ['jury_id' => $judgeId]);
        } catch (Exception $e) {
            // Si échoue avec 'jury_id', essayer avec 'juge_id'
            return $this->getByJury($judgeId);
        }
    }

    public function getAverageScore($projetId) {
        $evaluations = $this->getByProjet($projetId);
        if (empty($evaluations)) {
            return 0;
        }
        $scores = array_column($evaluations, 'score');
        return array_sum($scores) / count($scores);
    }

    public function getProjectScores($projetId) {
        $evaluations = $this->getByProjet($projetId);
        $scores = [];
        foreach ($evaluations as $eval) {
            $juge = $this->db->find('users', $eval['juge_id']);
            $scores[] = [
                'evaluation_id' => $eval['id'],
                'score' => $eval['score'],
                'commentaire' => $eval['commentaire'],
                'juge' => $juge ? $juge['nom'] . ' ' . $juge['prenom'] : 'Inconnu',
                'date' => $eval['created_at'] ?? null
            ];
        }
        return $scores;
    }

    public function countByHackathon($hackathonId) {
        $count = 0;
        $equipes = $this->db->query('equipes', ['hackathon_id' => $hackathonId]);
        foreach ($equipes as $equipe) {
            $projets = $this->db->query('projets', ['equipe_id' => $equipe['id']]);
            foreach ($projets as $projet) {
                $evaluations = $this->getByProjet($projet['id']);
                $count += count($evaluations);
            }
        }
        return $count;
    }

    public function getAverageScoreByHackathon($hackathonId) {
        $scores = [];
        $equipes = $this->db->query('equipes', ['hackathon_id' => $hackathonId]);
        foreach ($equipes as $equipe) {
            $projets = $this->db->query('projets', ['equipe_id' => $equipe['id']]);
            foreach ($projets as $projet) {
                $moyenne = $this->getAverageScore($projet['id']);
                if ($moyenne > 0) {
                    $scores[] = $moyenne;
                }
            }
        }
        return empty($scores) ? 0 : array_sum($scores) / count($scores);
    }

    public function countEvaluatedProjects($hackathonId) {
        $count = 0;
        $equipes = $this->db->query('equipes', ['hackathon_id' => $hackathonId]);
        foreach ($equipes as $equipe) {
            $projets = $this->db->query('projets', ['equipe_id' => $equipe['id']]);
            foreach ($projets as $projet) {
                if (!empty($this->getByProjet($projet['id']))) {
                    $count++;
                }
            }
        }
        return $count;
    }

    public function countNonEvaluatedProjects($hackathonId) {
        $count = 0;
        $equipes = $this->db->query('equipes', ['hackathon_id' => $hackathonId]);
        foreach ($equipes as $equipe) {
            $projets = $this->db->query('projets', ['equipe_id' => $equipe['id']]);
            foreach ($projets as $projet) {
                if (empty($this->getByProjet($projet['id']))) {
                    $count++;
                }
            }
        }
        return $count;
    }

    public function getMoyenneProjet($projetId) {
        $projet = $this->db->find('projets', $projetId);
        if (!$projet) {
            return null;
        }

        $evaluations = $this->getByProjet($projetId);
        $equipe = $this->db->find('equipes', $projet['equipe_id']);

        $stats = [
            'projet_id' => $projetId,
            'projet_titre' => $projet['titre'],
            'equipe_nom' => $equipe ? $equipe['name'] : 'Inconnue',
            'nombre_evaluations' => count($evaluations),
            'moyenne_score' => 0,
            'score_min' => null,
            'score_max' => null,
            'evaluateurs' => []
        ];

        if (!empty($evaluations)) {
            $scores = array_column($evaluations, 'score');
            $stats['moyenne_score'] = round(array_sum($scores) / count($scores), 2);
            $stats['score_min'] = min($scores);
            $stats['score_max'] = max($scores);

            foreach ($evaluations as $eval) {
                $juge = $this->db->find('users', $eval['juge_id']);
                if ($juge) {
                    $stats['evaluateurs'][] = $juge['nom'] . ' ' . $juge['prenom'];
                }
            }
        }

        return $stats;
    }
}
