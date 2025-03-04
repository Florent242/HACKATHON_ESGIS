// test.js

// Exemple de fonction à tester
function addition(a, b) {
    return a + b;
}

// Tests
function runTests() {
    console.log("Début des tests...");

    // Test 1: addition
    const result1 = addition(2, 3);
    console.assert(result1 === 5, `Échec du Test 1: attendu 5, obtenu ${result1}`);

    // Test 2: addition avec des nombres négatifs
    const result2 = addition(-1, -1);
    console.assert(result2 === -2, `Échec du Test 2: attendu -2, obtenu ${result2}`);

    // Test 3: addition avec zéro
    const result3 = addition(0, 5);
    console.assert(result3 === 5, `Échec du Test 3: attendu 5, obtenu ${result3}`);

    console.log("Tests terminés.");
}

// Exécuter les tests
runTests();
