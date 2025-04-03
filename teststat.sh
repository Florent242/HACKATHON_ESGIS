#!/bin/bash

# Configuration
BASE_URL="http://localhost/api/users"  # Modifié pour pointer vers le bon endpoint
ADMIN_TOKEN="votre_token_admin_ici"
USER_TOKEN="votre_token_utilisateur_ici"
TEST_USER_ID=123  # Remplacer par un ID utilisateur valide

# Fonction helper
make_api_call() {
    local url=$1
    local token=$2
    local method=${3:-GET}
    local data=${4:-""}
    
    if [ -z "$data" ]; then
        curl -s -X "$method" \
             -H "Authorization: Bearer $token" \
             -H "Content-Type: application/json" \
             "$url"
    else
        curl -s -X "$method" \
             -H "Authorization: Bearer $token" \
             -H "Content-Type: application/json" \
             -d "$data" \
             "$url"
    fi
}

echo "=== Début des tests pour /api/users/{id}/stats ==="

# Test 1: Accès sans token
echo -e "\nTest 1: Accès sans token"
response=$(make_api_call "${BASE_URL}/${TEST_USER_ID}/stats" "")
status=$(echo "$response" | grep -oP '(?<=<h2>Error )[0-9]+' || echo "200")
print_result $((status == 401)) "Devrait échouer avec 401 (Non autorisé)" "$status"

# Test 2: Accès avec token invalide
echo -e "\nTest 2: Accès avec token invalide"
response=$(make_api_call "${BASE_URL}/${TEST_USER_ID}/stats" "token_invalide")
status=$(echo "$response" | grep -oP '(?<=<h2>Error )[0-9]+' || echo "200")
print_result $((status == 401)) "Devrait échouer avec 401 (Token invalide)" "$status"

# Test 3: Utilisateur essayant d'accéder aux stats d'un autre utilisateur
echo -e "\nTest 3: Accès aux stats d'un autre utilisateur"
response=$(make_api_call "${BASE_URL}/999/stats" "$USER_TOKEN")
status=$(echo "$response" | grep -oP '(?<=<h2>Error )[0-9]+' || echo "200")
print_result $((status == 403)) "Devrait échouer avec 403 (Interdit)" "$status"

# Test 4: Admin accédant aux stats d'un autre utilisateur
echo -e "\nTest 4: Admin accède aux stats d'un autre utilisateur"
response=$(make_api_call "${BASE_URL}/${TEST_USER_ID}/stats" "$ADMIN_TOKEN")
if [[ "$response" == *"<!DOCTYPE html"* ]]; then
    echo "❌ La requête a échoué (reçu HTML au lieu de JSON)"
    echo "Vérifiez que:"
    echo "1. L'URL ${BASE_URL}/${TEST_USER_ID}/stats est correcte"
    echo "2. Le serveur API est en cours d'exécution"
    echo "3. Les routes sont bien configurées dans votre API"
    exit 1
fi

# Si on arrive ici, la réponse devrait être du JSON
if jq -e . >/dev/null 2>&1 <<<"$response"; then
    echo "✅ Format JSON valide reçu"
    echo "Réponse complète:"
    echo "$response" | jq .
else
    echo "❌ Réponse JSON invalide reçue"
    echo "Réponse brute:"
    echo "$response"
    exit 1
fi

echo -e "\n=== Fin des tests ==="