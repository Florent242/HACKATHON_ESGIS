<?php

namespace Auth\Model;

use Exception;
use PDO;

class Score
{
    private $db;
    private $table = 'scores';

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getLeaderboard($hackathon_id, $phase_id)
    {
        try {
            $sql = "
            SELECT 
                t.id AS team_id,
                t.name,
                s.total_points AS points,
                (SELECT COUNT(*) FROM team_members WHERE team_id = t.id) AS members,
                COALESCE(MAX(vf.validated_at), MAX(cs.submitted_at)) AS lastSubmission
            FROM scores s
            JOIN teams t ON t.id = s.team_id
            LEFT JOIN validated_flags vf 
                ON vf.challenge_id IN (
                    SELECT id FROM challenges WHERE hackathon_id = s.hackathon_id AND type = 'ctf'
                )
                AND vf.user_id IN (
                    SELECT user_id FROM team_members WHERE team_id = t.id
                )
            LEFT JOIN challenge_submissions cs 
                ON cs.challenge_id IN ( 
                    SELECT id FROM challenges WHERE hackathon_id = s.hackathon_id AND type = 'dev'
                )
                AND cs.user_id IN (
                    SELECT user_id FROM team_members WHERE team_id = t.id
                )
            WHERE s.hackathon_id = :hackathon_id
              AND s.phase_id = :phase_id
              AND s.is_active = 1
            GROUP BY t.id
            ORDER BY points DESC, lastSubmission ASC
            LIMIT 100   
        ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':hackathon_id' => $hackathon_id,
                ':phase_id' => $phase_id
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception(
                "Erreur lors de la récupération du leaderboard !"
                // pour debug
                // . $e->getMessage()
            );
        }
    }

    public function getPhases($hackathon_id)
    {
        try {
            $sql = "
                SELECT 
                    id,
                    name,
                    start,
                    end
                FROM phases
                WHERE hackathon_id = :hackathon_id
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':hackathon_id' => $hackathon_id
            ]);

            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception(
                "Erreur lors de la récupération des phases !"
                // pour debug
                // . $e->getMessage()
            );
        }
    }

    public function getPhase($hackathon_id, $phase_id)
    {
        try {
            $sql = "
                SELECT *
                FROM phases
                WHERE hackathon_id = :hackathon_id
                  AND id = :phase_id
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':hackathon_id' => $hackathon_id,
                ':phase_id' => $phase_id
            ]);

            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception(
                "Erreur lors de la récupération de la phase !"
                // pour debug
                // . $e->getMessage()
            );
        }
    }

    // TODO : Instruction interdite aux participants
    // public function updateScore($team_id, $hackathon_id, $phase_id, $points)
    // {
    //     try {
    //         // Vérifie si une ligne existe
    //         $stmt = $this->db->prepare("
    //         SELECT id FROM scores 
    //         WHERE team_id = :team_id AND hackathon_id = :hackathon_id AND phase_id = :phase_id
    //     ");
    //         $stmt->execute([
    //             ':team_id' => $team_id,
    //             ':hackathon_id' => $hackathon_id,
    //             ':phase_id' => $phase_id
    //         ]);

    //         $scoreId = $stmt->fetchColumn();

    //         if ($scoreId) {
    //             // Mise à jour
    //             $stmt = $this->db->prepare("
    //             UPDATE scores 
    //             SET total_points = total_points + :points 
    //             WHERE id = :id
    //         ");
    //             $stmt->execute([
    //                 ':points' => $points,
    //                 ':id' => $scoreId
    //             ]);
    //         } else {
    //             // Insertion
    //             $stmt = $this->db->prepare("
    //             INSERT INTO scores (team_id, hackathon_id, phase_id, total_points)
    //             VALUES (:team_id, :hackathon_id, :phase_id, :points)
    //         ");
    //             $stmt->execute([
    //                 ':team_id' => $team_id,
    //                 ':hackathon_id' => $hackathon_id,
    //                 ':phase_id' => $phase_id,
    //                 ':points' => $points
    //             ]);
    //         }
    //     } catch (Exception $e) {
    //         throw new Exception(
    //             "Erreur lors de la mise à jour du score !"
    //             // pour debug
    //             // . $e->getMessage()
    //         );
    //     }
    // }
}
