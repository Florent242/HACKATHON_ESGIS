USE hackathon_db;

ALTER TABLE evaluations
DROP FOREIGN KEY evaluations_ibfk_1,
ADD CONSTRAINT evaluations_ibfk_1 
FOREIGN KEY (projet_id) REFERENCES projets(id) 
ON DELETE CASCADE;
