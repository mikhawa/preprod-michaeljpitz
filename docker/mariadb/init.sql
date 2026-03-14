-- Création automatique de la base de test au démarrage du conteneur MariaDB
CREATE DATABASE IF NOT EXISTS preprod_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON preprod_test.* TO 'preprod'@'%';
FLUSH PRIVILEGES;
