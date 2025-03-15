USE hackathon_db;

-- Création d'un utilisateur organisateur (mot de passe: organisateur123)
INSERT INTO users (username, email, password, role, full_name) VALUES 
('organisateur', 'organisateur@esgis.bj', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'organisateur', 'Organisateur Test');
