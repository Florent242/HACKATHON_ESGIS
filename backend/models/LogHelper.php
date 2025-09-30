<?php

namespace Auth\Helper;

use Auth\Model\ActivityLog;

class LogHelper
{
    private static $activityLog;

    public static function init($db)
    {
        self::$activityLog = new ActivityLog($db);
    }

    /**
     * Enregistre une connexion
     */
    public static function logLogin($userId, $username)
    {
        return self::$activityLog->log(
            $userId,
            'user_login',
            "L'utilisateur {$username} s'est connecté à la plateforme",
            null,
            'info'
        );
    }

    /**
     * Enregistre une déconnexion
     */
    public static function logLogout($userId, $username)
    {
        return self::$activityLog->log(
            $userId,
            'user_logout',
            "L'utilisateur {$username} s'est déconnecté",
            null,
            'info'
        );
    }

    /**
     * Enregistre la création d'une équipe
     */
    public static function logTeamCreate($userId, $teamName, $teamId)
    {
        return self::$activityLog->log(
            $userId,
            'team_create',
            "A créé une nouvelle équipe '{$teamName}'",
            ['team_id' => $teamId],
            'info'
        );
    }


    /**
     * Enregistre la soumission d'un challenge
     */
    public static function logChallengeSubmit($userId, $challengeName, $submissionId)
    {
        return self::$activityLog->log(
            $userId,
            'challenge_submit',
            "A soumis une solution pour le challenge '{$challengeName}'",
            ['submission_id' => $submissionId],
            'info'
        );
    }

    /**
     * Enregistre la validation d'un flag
     */
    public static function logFlagValidate($userId, $challengeName, $points)
    {
        return self::$activityLog->log(
            $userId,
            'flag_validate',
            "A validé un flag pour le challenge '{$challengeName}' (+{$points} points)",
            ['points' => $points],
            'success'
        );
    }

    /**
     * Enregistre l'inscription à un hackathon
     */
    public static function logHackathonRegister($userId, $hackathonName)
    {
        return self::$activityLog->log(
            $userId,
            'hackathon_register',
            "S'est inscrit au hackathon '{$hackathonName}'",
            null,
            'info'
        );
    }

    /**
     * Enregistre la soumission d'un projet
     */
    public static function logProjectSubmit($userId, $projectName, $projectId)
    {
        return self::$activityLog->log(
            $userId,
            'project_submit',
            "A soumis le projet '{$projectName}'",
            ['project_id' => $projectId],
            'info'
        );
    }

    /**
     * Enregistre une modification de profil
     */
    public static function logProfileUpdate($userId, $username)
    {
        return self::$activityLog->log(
            $userId,
            'profile_update',
            "L'utilisateur {$username} a modifié son profil",
            null,
            'info'
        );
    }

    /**
     * Enregistre un changement de mot de passe
     */
    public static function logPasswordChange($userId, $username)
    {
        return self::$activityLog->log(
            $userId,
            'password_change',
            "L'utilisateur {$username} a changé son mot de passe",
            null,
            'warning'
        );
    }

    public static function logTeamRequest($userId, $teamId, $message)
    {
        return self::$activityLog->log(
            $userId,
            'team_request',
            "A soumis une demande d'adhésion pour l'équipe '{$teamId}'",
            ['team_id' => $teamId],
            'info'
        );
    }

    public static function logTeamRequestVerify($userId, $teamId, $message)
    {
        return self::$activityLog->log(
            $userId,
            'team_request_verify',
            "A soumis une demande d'adhésion pour l'équipe '{$teamId}'",
            ['team_id' => $teamId],
            'info'
        );
    }

    public static function logTeamRequestReject($userId, $teamId, $message)
    {
        return self::$activityLog->log(
            $userId,
            'team_request_reject',
            "A soumis une demande d'adhésion pour l'équipe '{$teamId}'",
            ['team_id' => $teamId],
            'info'
        );
    }

    public static function logTeamCodeUpdate($userId, $teamId, $code)
    {
        return self::$activityLog->log(
            $userId,
            'team_code_update',
            "A mis à jour le code d'invitation pour l'équipe '{$teamId}'",
            ['team_id' => $teamId, 'code' => $code],
            'info'
        );
    }

    public static function logTeamMemberAdd($userId, $teamId, $memberId)
    {
        return self::$activityLog->log(
            $userId,
            'team_member_add',
            "A ajouté l'utilisateur '{$memberId}' à l'équipe '{$teamId}'",
            ['team_id' => $teamId, 'member_id' => $memberId],
            'info'
        );
    }
    public static function logTeamRequestAccept($userId, $teamId, $memberId)
    {
        return self::$activityLog->log(
            $userId,
            'team_request_accept',
            "A accepté une demande d'adhésion pour l'équipe '{$teamId}'",
            ['team_id' => $teamId, 'member_id' => $memberId],
            'info'
        );
    }

    public static function logTeamMemberRemove($userId, $teamId, $memberId)
    {
        return self::$activityLog->log(
            $userId,
            'team_member_remove',
            "A retiré l'utilisateur '{$memberId}' de l'équipe '{$teamId}'",
            ['team_id' => $teamId, 'member_id' => $memberId],
            'info'
        );
    }

    public static function logTeamMemberUpdate($userId, $teamId, $memberId)
    {
        return self::$activityLog->log(
            $userId,
            'team_member_update',
            "A mis à jour l'utilisateur '{$memberId}' de l'équipe '{$teamId}'",
            ['team_id' => $teamId, 'member_id' => $memberId],
            'info'
        );
    }

    public static function logTeamMemberPromote($userId, $teamId, $memberId)
    {
        return self::$activityLog->log(
            $userId,
            'team_member_promote',
            "A promu l'utilisateur '{$memberId}' de l'équipe '{$teamId}'",
            ['team_id' => $teamId, 'member_id' => $memberId],
            'info'
        );
    }

    public static function logTeamMemberDemote($userId, $teamId, $memberId)
    {
        return self::$activityLog->log(
            $userId,
            'team_member_demote',
            "A dépromu l'utilisateur '{$memberId}' de l'équipe '{$teamId}'",
            ['team_id' => $teamId, 'member_id' => $memberId],
            'info'
        );
    }

    public static function logTeamMemberLeave($userId, $teamId, $memberId)
    {
        return self::$activityLog->log(
            $userId,
            'team_member_leave',
            "A quitté l'équipe '{$teamId}'",
            ['team_id' => $teamId, 'member_id' => $memberId],
            'info'
        );
    }

    public static function logTeamMemberKick($userId, $teamId, $memberId)
    {
        return self::$activityLog->log(
            $userId,
            'team_member_kick',
            "A kick l'utilisateur '{$memberId}' de l'équipe '{$teamId}'",
            ['team_id' => $teamId, 'member_id' => $memberId],
            'info'
        );
    }

    /**
     * Enregistre une tentative de connexion échouée
     */
    public static function logLoginFailed($email, $reason = '')
    {
        return self::$activityLog->log(
            null,
            'login_failed',
            "Tentative de connexion échouée pour {$email}" . ($reason ? " - {$reason}" : ""),
            ['email' => $email],
            'warning'
        );
    }

    public static function logTeamLeaderChange($userId, $teamId, $newLeaderId)
    {
        return self::$activityLog->log(
            $userId,
            'team_leader_change',
            "A changé le leader de l'équipe '{$teamId}'",
            ['team_id' => $teamId, 'new_leader_id' => $newLeaderId],
            'info'
        );
    }

    /**
     * Enregistre une erreur système
     */
    public static function logError($userId, $action, $error)
    {
        return self::$activityLog->log(
            $userId,
            $action . '_error',
            "Erreur lors de {$action}: {$error}",
            ['error' => $error],
            'error'
        );
    }

    /**
     * Enregistre une action admin
     */
    public static function logAdminAction($userId, $action, $description, $data = null)
    {
        return self::$activityLog->log(
            $userId,
            'admin_' . $action,
            $description,
            $data,
            'warning'
        );
    }

    /**
     * Enregistre la création d'un challenge
     */
    public static function logChallengeCreate($userId, $challengeName, $challengeId)
    {
        return self::$activityLog->log(
            $userId,
            'challenge_create',
            "A créé le challenge '{$challengeName}'",
            ['challenge_id' => $challengeId],
            'info'
        );
    }

    /**
     * Enregistre la modification d'un challenge
     */
    public static function logChallengeUpdate($userId, $challengeName)
    {
        return self::$activityLog->log(
            $userId,
            'challenge_update',
            "A modifié le challenge '{$challengeName}'",
            null,
            'info'
        );
    }

    /**
     * Enregistre la suppression d'un challenge
     */
    public static function logChallengeDelete($userId, $challengeName)
    {
        return self::$activityLog->log(
            $userId,
            'challenge_delete',
            "A supprimé le challenge '{$challengeName}'",
            null,
            'warning'
        );
    }

    /**
     * Enregistre la création d'un hackathon
     */
    public static function logHackathonCreate($userId, $hackathonName, $hackathonId)
    {
        return self::$activityLog->log(
            $userId,
            'hackathon_create',
            "A créé le hackathon '{$hackathonName}'",
            ['hackathon_id' => $hackathonId],
            'info'
        );
    }

    /**
     * Enregistre une action personnalisée
     */
    public static function logCustom($userId, $action, $description, $data = null, $level = 'info')
    {
        return self::$activityLog->log($userId, $action, $description, $data, $level);
    }
}