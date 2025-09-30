# Page de Validation des Projets - Guide d'utilisation

## 📋 Fonctionnalités créées

### 1. **Interface complète d'évaluation**
- Page dédiée `/frontend/admin/validation.php`
- Système de notation par critères pondérés
- Interface intuitive avec sliders et commentaires structurés

### 2. **Critères d'évaluation**
- **Innovation & Créativité** (25 points - 25%)
- **Qualité Technique** (30 points - 30%)  
- **Fonctionnalités** (25 points - 25%)
- **UI/UX & Présentation** (20 points - 20%)

### 3. **Actions de validation**
- ✅ **Valider** le projet avec attribution de score
- ⚠️ **Demander révision** avec commentaires
- ❌ **Rejeter** le projet avec justification

## 🔧 Intégration nécessaire avec l'API

### Routes API à créer/modifier dans `backend/api.php` :

```php
// Dans le case 'admin':
case 'validation-stats':
    // GET /api/admin/validation-stats
    $controllerAdmin->getValidationStats();
    break;

case 'projects':
    if ($method === 'GET' && isset($_GET['status'])) {
        // GET /api/admin/projects?status=submitted,in_evaluation
        $controllerAdmin->getProjectsByStatus($_GET['status']);
    }
    break;

case 'evaluate-project':
    if ($method === 'POST') {
        // POST /api/admin/evaluate-project
        $controllerAdmin->evaluateProject($input);
    }
    break;
```

### Méthodes à ajouter dans `AdminController.php` :

```php
public function getValidationStats() {
    try {
        $stats = [
            'pending_evaluations' => $this->project->countByStatus('submitted'),
            'validated_projects' => $this->project->countByStatus('validated'),
            'average_score' => $this->evaluation->getAverageScore(),
            'validation_rate' => $this->project->getValidationRate()
        ];
        
        jsonResponse(['success' => true, 'stats' => $stats]);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
}

public function getProjectsByStatus($statusList) {
    try {
        $statuses = explode(',', $statusList);
        $projects = $this->project->getByStatuses($statuses);
        
        jsonResponse(['success' => true, 'projects' => $projects]);
    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
}

public function evaluateProject($data) {
    try {
        // Validation des données
        if (!isset($data['project_id'], $data['criteria_scores'], $data['action'])) {
            throw new Exception('Données manquantes');
        }

        // Créer l'évaluation
        $evaluationId = $this->evaluation->create([
            'project_id' => $data['project_id'],
            'judge_id' => $this->getCurrentUserId(),
            'score' => $data['totalScore'],
            'criteria' => json_encode($data['criteria_scores']),
            'comments' => json_encode($data['comments']),
            'evaluated_at' => date('Y-m-d H:i:s')
        ]);

        // Mettre à jour le statut du projet
        $newStatus = match($data['action']) {
            'validate' => 'validated',
            'reject' => 'rejected',
            'request_revision' => 'needs_revision',
            default => 'in_evaluation'
        };

        $this->project->updateStatus($data['project_id'], $newStatus);
        $this->project->updateScore($data['project_id'], $data['totalScore']);

        // Notifier l'équipe
        $this->notifyTeam($data['project_id'], $newStatus, $data['comments']);

        jsonResponse([
            'success' => true,
            'message' => 'Évaluation enregistrée avec succès',
            'evaluation_id' => $evaluationId
        ]);

    } catch (Exception $e) {
        jsonResponse(['error' => $e->getMessage()], 500);
    }
}
```

## 🎯 **Pour tester l'interface :**

### 1. **Accéder à la page**
```
http://votre-domaine/frontend/admin/validation.php
```

### 2. **Vérifier les dépendances**
- Le fichier CSS est chargé : `/css/styles/admin/validation.css`
- Le JavaScript est fonctionnel : `/js/admin/validation.js`
- Les includes admin (header, head) sont disponibles

### 3. **Test des fonctionnalités**
- ✅ Affichage de la liste des projets
- ✅ Ouverture du modal d'évaluation
- ✅ Sliders de notation fonctionnels
- ✅ Calcul automatique du score total
- ✅ Système de commentaires par onglets
- ✅ Actions de validation (valider/révision/rejeter)

## 🔗 **Navigation**

Ajouter un lien dans le menu admin existant :
```html
<a href="/frontend/admin/validation.php" class="nav-link">
    <i class="fas fa-star me-2"></i>
    Validation des Projets
</a>
```

## 🎨 **Aperçu de l'interface**

L'interface respecte le design existant avec :
- **Statistiques** en haut (projets à évaluer, score moyen, etc.)
- **Filtres** pour rechercher et trier les projets
- **Table** avec tous les projets soumis
- **Modal d'évaluation** complet avec critères pondérés
- **Actions** de validation avec confirmations

## 🔄 **Workflow complet**

1. **Admin ouvre** la page validation
2. **Voit les projets** soumis dans le tableau
3. **Clique sur "Évaluer"** pour un projet
4. **Attribue des scores** par critère (sliders)
5. **Ajoute des commentaires** (points forts, améliorations, général)
6. **Choisit l'action** (valider/révision/rejeter)
7. **Confirme** et l'équipe est notifiée
8. **Statistiques mises à jour** automatiquement

Cette interface offre une expérience complète et professionnelle pour l'évaluation des projets de hackathon !