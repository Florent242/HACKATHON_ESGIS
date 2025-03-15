-- Ajout des colonnes pour la réinitialisation du mot de passe
ALTER TABLE users ADD COLUMN reset_token TEXT;
ALTER TABLE users ADD COLUMN reset_token_expiry DATETIME;
