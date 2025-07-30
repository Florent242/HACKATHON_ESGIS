#!/bin/bash

# Script d'application des migrations SQLite
# Date: 2025-07-14

echo "🚀 Application des migrations SQLite"
echo "===================================="

# Couleurs pour l'affichage
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Fonction pour afficher les résultats
function show_result() {
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}✅ $2${NC}"
    else
        echo -e "${RED}❌ $2${NC}"
    fi
}

DB_PATH="backend/database/hackathon.db"

echo -e "${BLUE}📁 Vérification de la base de données SQLite${NC}"

if [ -f "$DB_PATH" ]; then
    show_result 0 "Base de données SQLite trouvée: $DB_PATH"
else
    show_result 1 "Base de données SQLite non trouvée: $DB_PATH"
    echo -e "${YELLOW}💡 Création de la base de données...${NC}"
    touch "$DB_PATH"
    show_result 0 "Base de données créée"
fi

echo -e "${BLUE}📊 Application des migrations${NC}"

# Convertir la migration MySQL en SQLite
cat > migration_sqlite.sql << 'EOF'
-- Migration SQLite pour les défis algorithmiques
-- Date: 2025-07-14

-- Table pour les cas de test des défis algorithmiques
CREATE TABLE IF NOT EXISTS challenge_test_cases (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    challenge_id INTEGER NOT NULL,
    input TEXT NOT NULL,
    expected_output TEXT NOT NULL,
    is_visible INTEGER DEFAULT 1,
    weight INTEGER DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (challenge_id) REFERENCES challenges(id) ON DELETE CASCADE
);

-- Table pour les soumissions de code
CREATE TABLE IF NOT EXISTS challenge_submissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    challenge_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    team_id INTEGER DEFAULT NULL,
    language TEXT NOT NULL,
    code TEXT NOT NULL,
    status TEXT DEFAULT 'pending',
    score INTEGER DEFAULT 0,
    total_test_cases INTEGER DEFAULT 0,
    passed_test_cases INTEGER DEFAULT 0,
    execution_time INTEGER DEFAULT 0,
    memory_used INTEGER DEFAULT 0,
    submitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (challenge_id) REFERENCES challenges(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE SET NULL
);

-- Table pour les résultats détaillés des cas de test
CREATE TABLE IF NOT EXISTS test_case_results (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    submission_id INTEGER NOT NULL,
    test_case_id INTEGER NOT NULL,
    actual_output TEXT,
    expected_output TEXT,
    execution_time INTEGER DEFAULT 0,
    memory_used INTEGER DEFAULT 0,
    status TEXT DEFAULT 'pending',
    error_message TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (submission_id) REFERENCES challenge_submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (test_case_id) REFERENCES challenge_test_cases(id) ON DELETE CASCADE
);

-- Ajouter des colonnes aux challenges existants si elles n'existent pas
ALTER TABLE challenges ADD COLUMN type TEXT DEFAULT 'ctf';
ALTER TABLE challenges ADD COLUMN max_execution_time INTEGER DEFAULT 5000;
ALTER TABLE challenges ADD COLUMN max_memory INTEGER DEFAULT 128;
ALTER TABLE challenges ADD COLUMN allowed_languages TEXT DEFAULT 'python,javascript,java,cpp';
ALTER TABLE challenges ADD COLUMN validation_patterns TEXT;
ALTER TABLE challenges ADD COLUMN scoring_method TEXT DEFAULT 'binary';

-- Index pour optimiser les performances
CREATE INDEX IF NOT EXISTS idx_challenge_test_cases_challenge ON challenge_test_cases(challenge_id);
CREATE INDEX IF NOT EXISTS idx_challenge_submissions_challenge ON challenge_submissions(challenge_id);
CREATE INDEX IF NOT EXISTS idx_challenge_submissions_user ON challenge_submissions(user_id);
CREATE INDEX IF NOT EXISTS idx_challenge_submissions_team ON challenge_submissions(team_id);
CREATE INDEX IF NOT EXISTS idx_test_case_results_submission ON test_case_results(submission_id);
CREATE INDEX IF NOT EXISTS idx_test_case_results_test_case ON test_case_results(test_case_id);
EOF

# Appliquer la migration
if sqlite3 "$DB_PATH" < migration_sqlite.sql; then
    show_result 0 "Migration SQLite appliquée avec succès"
else
    show_result 1 "Erreur lors de l'application de la migration"
    exit 1
fi

echo -e "${BLUE}🧪 Vérification des tables créées${NC}"

# Vérifier que les tables ont été créées
tables_check=$(sqlite3 "$DB_PATH" "SELECT name FROM sqlite_master WHERE type='table' AND name IN ('challenge_test_cases', 'challenge_submissions', 'test_case_results');")

expected_tables=("challenge_test_cases" "challenge_submissions" "test_case_results")
for table in "${expected_tables[@]}"; do
    if echo "$tables_check" | grep -q "$table"; then
        show_result 0 "Table '$table' créée"
    else
        show_result 1 "Table '$table' non créée"
    fi
done

echo -e "${BLUE}📝 Insertion de données de test${NC}"

# Insérer des données de test
cat > test_data_sqlite.sql << 'EOF'
-- Données de test pour les défis algorithmiques

-- Insérer un défi algorithmique de test (si pas déjà présent)
INSERT OR IGNORE INTO challenges (
    id, title, description, type, difficulty, points, 
    max_execution_time, max_memory, allowed_languages, 
    validation_patterns, scoring_method, hackathon_id, phase_id
) VALUES (
    999, 
    'Addition Simple', 
    'Écrivez un programme qui additionne deux nombres.',
    'algorithmic',
    'easy',
    100,
    5000,
    128,
    'python,javascript,java,cpp',
    'print,console.log,System.out,cout',
    'test_cases',
    1,
    1
);

-- Insérer des cas de test
INSERT OR IGNORE INTO challenge_test_cases (id, challenge_id, input, expected_output, is_visible, weight) VALUES
(1, 999, '2 3', '5', 1, 1),
(2, 999, '10 15', '25', 1, 1),
(3, 999, '0 0', '0', 0, 1),
(4, 999, '-5 5', '0', 0, 1);

-- Insérer une soumission de test (utilisateur ID 1 si existe)
INSERT OR IGNORE INTO challenge_submissions (
    id, challenge_id, user_id, language, code, status, 
    score, total_test_cases, passed_test_cases
) VALUES (
    1, 999, 1, 'python', 
    'a, b = map(int, input().split())\nprint(a + b)',
    'completed', 100, 4, 4
);
EOF

if sqlite3 "$DB_PATH" < test_data_sqlite.sql; then
    show_result 0 "Données de test insérées"
else
    show_result 1 "Erreur lors de l'insertion des données de test"
fi

# Nettoyer
rm -f migration_sqlite.sql test_data_sqlite.sql

echo ""
echo -e "${BLUE}📊 Résumé de la base de données${NC}"
echo "================================"

# Afficher le nombre d'enregistrements dans chaque table
echo "Tables créées:"
sqlite3 "$DB_PATH" "SELECT name FROM sqlite_master WHERE type='table' ORDER BY name;" | while read table; do
    count=$(sqlite3 "$DB_PATH" "SELECT COUNT(*) FROM $table;")
    echo "  $table: $count enregistrements"
done

echo ""
echo -e "${GREEN}🎉 Base de données prête pour les défis algorithmiques !${NC}"
echo ""
echo -e "${YELLOW}🎯 Prochaine étape:${NC}"
echo "   ./test_integration_db.sh pour valider l'intégration complète"
