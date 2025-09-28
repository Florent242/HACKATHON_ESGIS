# API de Gestion des Projets

Ce document décrit les endpoints de l'API pour la gestion des projets dans le système de hackathon.

## Table des matières

1. [Soumission d'un nouveau projet](#soumission-dun-nouveau-projet)
2. [Récupération des projets](#récupération-des-projets)
3. [Récupération d'un projet spécifique](#récupération-dun-projet-spécifique)
4. [Mise à jour d'un projet](#mise-à-jour-dun-projet)
5. [Suppression d'un projet](#suppression-dun-projet)
6. [Téléchargement du projet](#téléchargement-du-projet)
7. [Soumission pour évaluation](#soumission-pour-évaluation)
8. [Gestion des évaluations](#gestion-des-évaluations)

## Soumission d'un nouveau projet

Soumet un nouveau projet pour un défi spécifique.

```http
POST /api/projects
```

### Paramètres de la requête (multipart/form-data)

| Paramètre       | Type     | Requis | Description                                                                 |
|-----------------|----------|--------|-----------------------------------------------------------------------------|
| name            | string   | Oui    | Nom du projet                                                              |
| description     | string   | Oui    | Description détaillée du projet                                            |
| hackathon_id    | integer  | Oui    | ID du hackathon auquel le projet est associé                               |
| challenge_id    | integer  | Oui    | ID du défi auquel le projet est soumis                                     |
| project_file    | file     | Non*   | Fichier ZIP contenant le projet (soit ce champ, soit repository_url requis)|
| repository_url  | string   | Non*   | URL du dépôt GitHub du projet (soit ce champ, soit project_file requis)    |

\* Au moins un des champs `project_file` ou `repository_url` doit être fourni.

### Réponse en cas de succès (201 Created)

```json
{
  "success": true,
  "message": "Projet soumis avec succès",
  "data": {
    "project_id": 123,
    "team_id": 456
  }
}
```

### Réponse en cas d'erreur (400 Bad Request)

```json
{
  "success": false,
  "error": "Message d'erreur détaillé",
  "validation_errors": {
    "champ": ["message d'erreur spécifique"]
  }
}
```

## Récupération des projets

Récupère une liste de projets avec des filtres optionnels.

```http
GET /api/projects?hackathon_id=1&team_id=2&challenge_id=3&status=submitted
```

### Paramètres de requête

| Paramètre    | Type    | Requis | Description                                      |
|--------------|---------|--------|--------------------------------------------------|
| hackathon_id | integer | Non    | Filtre par ID de hackathon                       |
| team_id      | integer | Non    | Filtre par ID d'équipe                           |
| challenge_id | integer | Non    | Filtre par ID de défi                            |
| status       | string  | Non    | Filtre par statut (pending, submitted, evaluated, etc.) |

### Réponse en cas de succès (200 OK)

```json
{
  "success": true,
  "data": [
    {
      "id": 123,
      "name": "Mon Projet",
      "description": "Description du projet",
      "team_id": 456,
      "hackathon_id": 1,
      "challenge_id": 3,
      "status": "submitted",
      "created_at": "2023-01-01T12:00:00Z"
    }
  ]
}
```

## Récupération d'un projet spécifique

Récupère les détails d'un projet spécifique par son ID.

```http
GET /api/projects/123
```

### Réponse en cas de succès (200 OK)

```json
{
  "success": true,
  "data": {
    "id": 123,
    "name": "Mon Projet",
    "description": "Description détaillée du projet",
    "team_id": 456,
    "hackathon_id": 1,
    "challenge_id": 3,
    "repository_url": "https://github.com/username/project",
    "file_name": "project.zip",
    "file_path": "/path/to/storage/project.zip",
    "status": "submitted",
    "rule_compliance": true,
    "created_at": "2023-01-01T12:00:00Z",
    "updated_at": "2023-01-01T12:00:00Z",
    "team": {
      "id": 456,
      "name": "Équipe Gagnante",
      "description": "Notre super équipe",
      "hackathon_id": 1
    },
    "evaluations": [
      {
        "id": 789,
        "score": 85,
        "comments": "Excellent travail!",
        "evaluator_id": 101,
        "evaluator_name": "John Doe",
        "created_at": "2023-01-02T10:30:00Z"
      }
    ]
  }
}
```

## Mise à jour d'un projet

Met à jour les informations d'un projet existant.

```http
PUT /api/projects/123
```

### Corps de la requête (JSON)

```json
{
  "name": "Nouveau nom du projet",
  "description": "Nouvelle description",
  "repository_url": "https://github.com/username/updated-project"
}
```

### Champs modifiables

- `name` (string): Nouveau nom du projet
- `description` (string): Nouvelle description
- `repository_url` (string): Nouvelle URL du dépôt
- `status` (string): Nouveau statut (uniquement pour les administrateurs)

### Réponse en cas de succès (200 OK)

```json
{
  "success": true,
  "message": "Projet mis à jour avec succès"
}
```

## Suppression d'un projet

Supprime un projet existant (uniquement pour les administrateurs).

```http
DELETE /api/projects/123
```

### Réponse en cas de succès (200 OK)

```json
{
  "success": true,
  "message": "Projet supprimé avec succès"
}
```

## Téléchargement du projet

Télécharge le fichier ZIP du projet.

```http
GET /api/projects/123/download
```

### Réponse

Le fichier binaire du projet est renvoyé avec les en-têtes appropriés pour le téléchargement.

## Soumission pour évaluation

Soumet un projet pour évaluation par les juges.

```http
POST /api/projects/123/submit-evaluation
```

### Réponse en cas de succès (200 OK)

```json
{
  "success": true,
  "message": "Projet soumis pour évaluation avec succès",
  "data": {
    "project_id": 123,
    "status": "submitted"
  }
}
```

## Gestion des évaluations

### Récupérer les évaluations d'un projet

```http
GET /api/projects/123/evaluations
```

### Ajouter une évaluation

```http
POST /api/projects/123/evaluations
```

#### Corps de la requête (JSON)

```json
{
  "score": 90,
  "comments": "Excellent travail, très bonne implémentation!",
  "criteria": [
    {
      "criterion_id": 1,
      "score": 9,
      "comments": "Très bonne implémentation"
    },
    {
      "criterion_id": 2,
      "score": 10,
      "comments": "Parfait!"
    }
  ]
}
```

### Réponse en cas de succès (201 Created)

```json
{
  "success": true,
  "message": "Évaluation ajoutée avec succès",
  "data": {
    "evaluation_id": 789,
    "project_id": 123,
    "evaluator_id": 101,
    "score": 90,
    "created_at": "2023-01-02T10:30:00Z"
  }
}
```

## Codes d'erreur courants

| Code | Message | Description |
|------|---------|-------------|
| 400 | Données de requête invalides | Les données fournies ne sont pas valides |
| 401 | Non authentifié | L'utilisateur doit être connecté |
| 403 | Accès refusé | L'utilisateur n'a pas les droits nécessaires |
| 404 | Projet non trouvé | Aucun projet avec l'ID spécifié n'a été trouvé |
| 409 | Conflit | Le projet existe déjà pour cette équipe et ce défi |
| 413 | Fichier trop volumineux | Le fichier dépasse la taille maximale autorisée |
| 415 | Type de média non supporté | Le type de fichier n'est pas autorisé |
| 422 | Erreur de validation | Les données fournies ne respectent pas les règles de validation |
| 500 | Erreur interne du serveur | Une erreur inattendue s'est produite |

## Exemples d'utilisation

### Soumettre un nouveau projet avec un fichier

```bash
curl -X POST http://api.example.com/projects \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -F "name=Mon Projet" \
  -F "description=Description du projet" \
  -F "hackathon_id=1" \
  -F "challenge_id=3" \
  -F "project_file=@/chemin/vers/projet.zip"
```

### Mettre à jour un projet existant

```bash
curl -X PUT http://api.example.com/projects/123 \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Nouveau nom","description":"Nouvelle description"}'
```

### Récupérer les projets d'une équipe

```bash
curl -X GET "http://api.example.com/projects?team_id=456" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### Ajouter une évaluation

```bash
curl -X POST http://api.example.com/projects/123/evaluations \
  -H "Authorization: Bearer JUDGE_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"score":90,"comments":"Excellent travail!"}'
```
