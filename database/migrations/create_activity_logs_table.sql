-- Migration pour créer la table activity_logs
CREATE TABLE IF NOT EXISTS `activity_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `timestamp` datetime NOT NULL,
  `action` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `level` varchar(20) NOT NULL DEFAULT 'info',
  `ip` varchar(45) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `user_email` varchar(255) DEFAULT NULL,
  `request_uri` varchar(255) DEFAULT NULL,
  `method` varchar(10) DEFAULT NULL,
  `context` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_action` (`action`),
  KEY `idx_level` (`level`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_timestamp` (`timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
