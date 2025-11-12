# Formats de données attendus

## 1. Prix du Hackathon (`hackathon.prizes`)
Format JSON attendu pour les prix du hackathon :

```json
[
  {
    "label": "string - Libellé du prix (ex: '1er Prix')",
    "reward": "string - Description de la récompense"
  }
  // Jusqu'à 3 prix maximum (or, argent, bronze)
]
```

## 2. Règles du Hackathon (`hackathon.rules`)
Format JSON attendu pour les règles :

```json
[
  {
    "title": "string - Titre de la règle",
    "description": "string - Description détaillée de la règle"
  }
  // Autres règles...
]
```

## 3. Équipes d'Inscription
Format attendu pour l'inscription d'une équipe :

```json
{
  "teamName": "string - Nom de l'équipe",
  "members": [
    {
      "name": "string - Nom complet",
      "email": "string - Email",
      "role": "string - Rôle dans l'équipe"
    }
  ],
  "project": {
    "title": "string - Titre du projet",
    "description": "string - Description du projet",
    "technologies": ["string - Liste des technologies"]
  }
}
```

## 4. Phases du Hackathon
Format attendu pour les phases :

```json
[
  "string - Phase 1: Description",
  "string - Phase 2: Description"
  // Autres phases...
]
```
