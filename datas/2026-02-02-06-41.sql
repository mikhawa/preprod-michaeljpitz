-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : mariadb
-- Généré le : lun. 02 fév. 2026 à 05:41
-- Version du serveur : 10.11.15-MariaDB-ubu2204
-- Version de PHP : 8.3.29

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Base de données : `portfolio`
--
DROP DATABASE IF EXISTS `portfolio`;
CREATE DATABASE IF NOT EXISTS `portfolio` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `portfolio`;

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

CREATE TABLE `article` (
                           `id` int(10) UNSIGNED NOT NULL,
                           `title` varchar(255) NOT NULL,
                           `slug` varchar(154) NOT NULL,
                           `content` longtext NOT NULL,
                           `excerpt` longtext DEFAULT NULL,
                           `featured_image` varchar(255) DEFAULT NULL,
                           `is_published` tinyint(4) DEFAULT 0,
                           `created_at` datetime DEFAULT current_timestamp(),
                           `published_at` datetime DEFAULT NULL,
                           `updated_at` datetime DEFAULT NULL,
                           `category_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `article`
--

INSERT INTO `article` (`id`, `title`, `slug`, `content`, `excerpt`, `featured_image`, `is_published`, `created_at`, `published_at`, `updated_at`, `category_id`) VALUES
                                                                                                                                                                     (1, 'DataGrip | JetBrains for Data', 'datagrip-jetbrains-for-data', '<div>DataGrip IDE Professional Database Environment is a multi-database SQL development platform developed by JetBrains. It supports major database systems including datagrip postgres, datagrip oracle, datagrip mssql, datagrip sql server, and mongodb datagrip. This intelligent environment allows developers and database engineers to write, debug, and optimize queries efficiently.<br><br></div><div>The datagrip ide is part of the JetBrains ecosystem, built on the same foundation as intellij datagrip, datagrip pycharm, and datagrip phpstorm. It features contextual code completion, refactoring, query analysis, and real-time syntax validation across connected databases.<br><br></div><div>With datagrip sql and datagrip database management tools, users can navigate schemas, view execution plans, and track query performance. The interface integrates datagrip git and datagrip github extensions for version control, supporting collaboration and CI/CD workflows.<br><br></div><div>JetBrains datagrip includes features such as data visualization, smart search, and schema synchronization. It is ideal for developers using data grip sql environments and dbeaver datagrip compatibility modes for cross-platform database work.<br><br><figure data-trix-attachment=\"{&quot;contentType&quot;:&quot;image/jpeg&quot;,&quot;filename&quot;:&quot;IA-a-tester.jpg&quot;,&quot;filesize&quot;:187668,&quot;height&quot;:676,&quot;href&quot;:&quot;/uploads/articles/content/78f5d224c52fbc5f609d47bd7e642fd1.jpg&quot;,&quot;url&quot;:&quot;/uploads/articles/content/78f5d224c52fbc5f609d47bd7e642fd1.jpg&quot;,&quot;width&quot;:1200}\" data-trix-content-type=\"image/jpeg\" data-trix-attributes=\"{&quot;presentation&quot;:&quot;gallery&quot;}\" class=\"attachment attachment--preview attachment--jpg\"><a href=\"/uploads/articles/content/78f5d224c52fbc5f609d47bd7e642fd1.jpg\"><img src=\"/uploads/articles/content/78f5d224c52fbc5f609d47bd7e642fd1.jpg\" width=\"1200\" height=\"676\"><figcaption class=\"attachment__caption\"><span class=\"attachment__name\">IA-a-tester.jpg</span> <span class=\"attachment__size\">183.27 KB</span></figcaption></a></figure></div>', 'DataGrip IDE Professional Database Environmen', 'ec7f34930f35e635ecfc06a88994b8572f871b70.png', 1, '2026-01-31 18:32:09', '2026-01-31 19:31:00', '2026-02-01 19:57:59', 1),
                                                                                                                                                                     (2, 'sql.sh site d\'information SQL', 'sqlsh-site-dinformation-sql', '<div><br></div><h1>Apprendre le SQL<br><br></h1><div>Le <strong>SQL</strong> (Structured Query Language) est un langage permettant de communiquer avec une base de données. Ce langage informatique est notamment très utilisé par les développeurs web pour communiquer avec les données d’un site web. SQL.sh recense des cours de SQL et des explications sur les principales commandes pour lire, insérer, modifier et supprimer des données dans une base.<br><br></div><div><strong>Cours<br></strong><br></div><div>Les cours ont pour but d’apprendre les principales commandes SQL telles que: <a href=\"https://sql.sh/cours/select\">SELECT</a>, <a href=\"https://sql.sh/cours/insert-into\">INSERT INTO</a>, <a href=\"https://sql.sh/cours/update\">UPDATE</a>, <a href=\"https://sql.sh/cours/delete\">DELETE</a>, <a href=\"https://sql.sh/cours/sql-drop-table\">DROP TABLE</a>… Chaque commande SQL est présentée par des exemples clairs et concis. Ces tutoriels peuvent vous aider à faire votre propre <strong>formation SQL</strong>.<br><br><figure data-trix-attachment=\"{&quot;contentType&quot;:&quot;image/png&quot;,&quot;filename&quot;:&quot;nicolas-icon.png&quot;,&quot;height&quot;:125,&quot;href&quot;:&quot;/uploads/articles/content/ecc52e55f533c40c25e313e10c54f5d0.png&quot;,&quot;url&quot;:&quot;/uploads/articles/content/ecc52e55f533c40c25e313e10c54f5d0.png&quot;,&quot;width&quot;:125}\" data-trix-content-type=\"image/png\" data-trix-attributes=\"{&quot;presentation&quot;:&quot;gallery&quot;}\" class=\"attachment attachment--preview attachment--png\"><a href=\"/uploads/articles/content/ecc52e55f533c40c25e313e10c54f5d0.png\"><img src=\"/uploads/articles/content/ecc52e55f533c40c25e313e10c54f5d0.png\" width=\"125\" height=\"125\"><figcaption class=\"attachment__caption\"><span class=\"attachment__name\">nicolas-icon.png</span></figcaption></a></figure><br><br></div><div>En plus de la liste des commandes SQL, les cours présentes des fiches mnémotechniques présentant les fonctions SQL telles que <a href=\"https://sql.sh/fonctions/agregation/avg\">AVG()</a>, <a href=\"https://sql.sh/fonctions/agregation/count\">COUNT()</a>, <a href=\"https://sql.sh/fonctions/agregation/max\">MAX()</a> …<br><br></div><div><figure data-trix-attachment=\"{&quot;contentType&quot;:&quot;image/png&quot;,&quot;filename&quot;:&quot;sql-sh-logo-245.png&quot;,&quot;filesize&quot;:3377,&quot;height&quot;:63,&quot;href&quot;:&quot;/uploads/articles/content/edf206a9c71fe84ed83c54f83a5ecf47.png&quot;,&quot;url&quot;:&quot;/uploads/articles/content/edf206a9c71fe84ed83c54f83a5ecf47.png&quot;,&quot;width&quot;:245}\" data-trix-content-type=\"image/png\" data-trix-attributes=\"{&quot;presentation&quot;:&quot;gallery&quot;}\" class=\"attachment attachment--preview attachment--png\"><a href=\"/uploads/articles/content/edf206a9c71fe84ed83c54f83a5ecf47.png\"><img src=\"/uploads/articles/content/edf206a9c71fe84ed83c54f83a5ecf47.png\" width=\"245\" height=\"63\"><figcaption class=\"attachment__caption\"><span class=\"attachment__name\">sql-sh-logo-245.png</span> <span class=\"attachment__size\">3.3 KB</span></figcaption></a></figure><br>hjgjyguyg<br><figure data-trix-attachment=\"{&quot;contentType&quot;:&quot;image/png&quot;,&quot;filename&quot;:&quot;repository-fin-prefo.png&quot;,&quot;filesize&quot;:65417,&quot;height&quot;:830,&quot;href&quot;:&quot;/uploads/articles/content/d43883e15c1ab847bac06a5d17249c51.png&quot;,&quot;url&quot;:&quot;/uploads/articles/content/d43883e15c1ab847bac06a5d17249c51.png&quot;,&quot;width&quot;:851}\" data-trix-content-type=\"image/png\" data-trix-attributes=\"{&quot;presentation&quot;:&quot;gallery&quot;}\" class=\"attachment attachment--preview attachment--png\"><a href=\"/uploads/articles/content/d43883e15c1ab847bac06a5d17249c51.png\"><img src=\"/uploads/articles/content/d43883e15c1ab847bac06a5d17249c51.png\" width=\"851\" height=\"830\"><figcaption class=\"attachment__caption\"><span class=\"attachment__name\">repository-fin-prefo.png</span> <span class=\"attachment__size\">63.88 KB</span></figcaption></a></figure><br><br>gersgth<br><br><br></div>', 'Site aide-mémoire sur les commandes et versions de SQL', 'b083642aa98c1a3167460f196711bb6075f0fad6.png', 1, '2026-02-01 01:30:37', '2026-02-01 02:30:00', '2026-02-01 07:51:21', 1),
                                                                                                                                                                     (3, 'Présentation de Gemini Code Assist', 'presentation-de-gemini-code-assist', '<div><figure data-trix-attachment=\"{&quot;contentType&quot;:&quot;image/png&quot;,&quot;filename&quot;:&quot;Capture d’écran 2026-02-01 033422.png&quot;,&quot;height&quot;:177,&quot;href&quot;:&quot;/uploads/articles/content/3a674c4ff137244e3f982994708b5f2b.png&quot;,&quot;url&quot;:&quot;/uploads/articles/content/3a674c4ff137244e3f982994708b5f2b.png&quot;,&quot;width&quot;:823}\" data-trix-content-type=\"image/png\" data-trix-attributes=\"{&quot;presentation&quot;:&quot;gallery&quot;}\" class=\"attachment attachment--preview attachment--png\"><a href=\"/uploads/articles/content/3a674c4ff137244e3f982994708b5f2b.png\"><img src=\"/uploads/articles/content/3a674c4ff137244e3f982994708b5f2b.png\" width=\"823\" height=\"177\"><figcaption class=\"attachment__caption\"><span class=\"attachment__name\">Capture d’écran 2026-02-01 033422.png</span></figcaption></a></figure><br>Gemini Code Assist offre une assistance optimisée par l\'IA pour aider votre équipe de développement à créer, déployer et exploiter des applications tout au long du cycle de vie de développement logiciel à l\'aide du modèle Gemini 2.5. Gemini Code Assist est disponible dans les éditions suivantes :<br><br></div><ul><li><a href=\"https://developers.google.com/gemini-code-assist/docs/overview?hl=fr#supported-features-gca\">Gemini Code Assist pour les particuliers</a>, disponible sans frais.</li><li><a href=\"https://developers.google.com/gemini-code-assist/docs/overview?hl=fr#supported-features\">Gemini Code Assist Standard</a>, un produit du portefeuille <a href=\"https://cloud.google.com/gemini/docs/overview?hl=fr\">Gemini pour Google Cloud</a>.</li><li><a href=\"https://developers.google.com/gemini-code-assist/docs/overview?hl=fr#supported-features\">Gemini Code Assist Enterprise</a>, un produit du portefeuille <a href=\"https://cloud.google.com/gemini/docs/overview?hl=fr\">Gemini pour Google Cloud</a>.</li></ul><div><strong><br>Remarque</strong> : Les développeurs individuels qui utilisent la version sans frais de Gemini Code Assist, Gemini Code Assist pour les particuliers, peuvent bénéficier de limites de requêtes quotidiennes plus élevées en souscrivant un abonnement à Google AI Pro ou Ultra. Cela permettra d\'augmenter les limites de requêtes de modèle quotidiennes partagées entre Gemini Code Assist, la CLI Gemini et le mode Agent. <a href=\"https://blog.google/technology/developers/gemini-cli-code-assist-higher-limits?hl=fr\">En savoir plus<br></a><br></div><div><br>Vous pouvez utiliser Gemini Code Assist dans les <a href=\"https://developers.google.com/gemini-code-assist/docs/supported-languages?hl=fr#supported_ides\">IDE compatibles</a>, tels que les IDE VS Code et JetBrains ou Android Studio, afin de bénéficier d\'une assistance IA pour le codage dans de <a href=\"https://developers.google.com/gemini-code-assist/docs/supported-languages?hl=fr\">nombreux langages populaires</a>. Vous pouvez bénéficier de la complétion de code au fil de la rédaction, générer des fonctions et des blocs de code complets à partir des commentaires, générer des tests unitaires et obtenir de l\'aide pour le débogage, la compréhension et la documentation de votre code.<br><br></div><div><br>Gemini Code Assist fournit des réponses contextualisées à vos prompts, avec des <a href=\"https://developers.google.com/gemini-code-assist/docs/works?hl=fr#how-when-gemini-cites-sources\">citations de sources</a> concernant la documentation et les exemples de code utilisés par Gemini Code Assist pour générer ses réponses.<br><br></div><div><br>Les grands modèles de langage (LLM) Gemini utilisés par Gemini Code Assist sont entraînés sur des ensembles de données de code disponible publiquement, de contenu spécifique à Google Cloud et d\'autres informations techniques pertinentes, en plus des ensembles de données utilisés pour entraîner les <a href=\"https://storage.googleapis.com/deepmind-media/gemini/gemini_1_report.pdf\">modèles de fondation</a> Gemini. Les modèles sont entraînés pour que les réponses de Gemini Code Assist soient aussi utiles que possible à ses utilisateurs.<br><br></div><ul><li><a href=\"https://developers.google.com/gemini-code-assist/docs/data-governance?hl=fr\">Découvrez comment et quand Gemini Code Assist Standard et Enterprise utilisent vos données.</a></li><li><a href=\"https://developers.google.com/gemini-code-assist/resources/privacy-notice-gemini-code-assist-individuals?hl=fr\">Découvrez comment et quand Gemini Code Assist pour les particuliers utilise vos données.</a></li></ul>', 'Gemini Code Assist offre une assistance optimisée par l\'IA pour aider votre équipe de développement à créer, déployer et exploiter des applications tout au long du cycle de vie de développement logiciel à l\'aide du modèle Gemini 2.5. Gemini Code Assist est disponible dans les éditions suivantes', 'f6b9ab1680a9e272de5c8d576c8be8789d451e91.png', 1, '2026-02-01 02:48:03', NULL, '2026-02-01 19:56:28', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `category`
--

CREATE TABLE `category` (
                            `id` int(10) UNSIGNED NOT NULL,
                            `title` varchar(100) NOT NULL,
                            `slug` varchar(104) NOT NULL,
                            `color` varchar(7) DEFAULT NULL,
                            `description` varchar(600) DEFAULT NULL,
                            `level` int(10) UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `category`
--

INSERT INTO `category` (`id`, `title`, `slug`, `color`, `description`, `level`) VALUES
    (1, 'Bases de données', 'bases-de-donnees', '#e6803d', 'Ensemble organisé de données structurées, stockées électroniquement pour permettre leur gestion, leur consultation et leur mise à jour efficaces via un Système de Gestion de Base de Données (SGBD)', 0);

-- --------------------------------------------------------

--
-- Structure de la table `comment`
--

CREATE TABLE `comment` (
                           `id` int(10) UNSIGNED NOT NULL,
                           `content` longtext NOT NULL,
                           `created_at` datetime NOT NULL,
                           `user_id` int(10) UNSIGNED NOT NULL,
                           `article_id` int(10) UNSIGNED NOT NULL,
                           `is_approved` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `comment`
--

INSERT INTO `comment` (`id`, `content`, `created_at`, `user_id`, `article_id`, `is_approved`) VALUES
                                                                                                  (2, 'Intéressant, j\'espère que le code est bien protégé ! <br><br>', '2026-02-01 01:46:05', 2, 2, 1),
                                                                                                  (3, 'Un outil bien pratique !', '2026-02-01 01:47:00', 2, 1, 1),
                                                                                                  (4, 'petit test', '2026-02-01 08:18:47', 2, 1, 1),
                                                                                                  (5, 'coucou', '2026-02-01 08:40:46', 3, 1, 1),
                                                                                                  (6, 'yes', '2026-02-01 08:51:19', 4, 2, 1),
                                                                                                  (7, 'Superbe article !', '2026-02-02 05:39:55', 3, 3, 0);

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
                                               `version` varchar(191) NOT NULL,
                                               `executed_at` datetime DEFAULT NULL,
                                               `execution_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
                                                                                           ('DoctrineMigrations\\Version20260131164708', '2026-01-31 16:47:12', 46),
                                                                                           ('DoctrineMigrations\\Version20260131182313', '2026-01-31 18:23:23', 634),
                                                                                           ('DoctrineMigrations\\Version20260201081649', '2026-02-01 08:16:57', 17),
                                                                                           ('DoctrineMigrations\\Version20260201082340', '2026-02-01 08:23:50', 11),
                                                                                           ('DoctrineMigrations\\Version20260201082743', '2026-02-01 08:27:53', 29),
                                                                                           ('DoctrineMigrations\\Version20260201195226', '2026-02-01 19:52:39', 18);

-- --------------------------------------------------------

--
-- Structure de la table `messenger_messages`
--

CREATE TABLE `messenger_messages` (
                                      `id` bigint(20) NOT NULL,
                                      `body` longtext NOT NULL,
                                      `headers` longtext NOT NULL,
                                      `queue_name` varchar(190) NOT NULL,
                                      `created_at` datetime NOT NULL,
                                      `available_at` datetime NOT NULL,
                                      `delivered_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `messenger_messages`
--

INSERT INTO `messenger_messages` (`id`, `body`, `headers`, `queue_name`, `created_at`, `available_at`, `delivered_at`) VALUES
    (1, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:39:\\\"Symfony\\\\Bridge\\\\Twig\\\\Mime\\\\TemplatedEmail\\\":5:{i:0;s:26:\\\"email/activation.html.twig\\\";i:1;N;i:2;a:2:{s:4:\\\"user\\\";O:15:\\\"App\\\\Entity\\\\User\\\":10:{s:19:\\\"\\0App\\\\Entity\\\\User\\0id\\\";i:3;s:22:\\\"\\0App\\\\Entity\\\\User\\0email\\\";s:20:\\\"michael.pitz@cf2m.be\\\";s:22:\\\"\\0App\\\\Entity\\\\User\\0roles\\\";a:0:{}s:25:\\\"\\0App\\\\Entity\\\\User\\0password\\\";s:60:\\\"$2y$13$vtZywNx0HX6ABWN2d8SQbeRg3i86A.tJjM24jNvgANAs1hFDMkpnq\\\";s:25:\\\"\\0App\\\\Entity\\\\User\\0userName\\\";s:7:\\\"mikhawa\\\";s:32:\\\"\\0App\\\\Entity\\\\User\\0activationToken\\\";s:64:\\\"b3540bdae5590095565edb2512ce3e8cab2c8a39ec498583c130164403b82bc3\\\";s:23:\\\"\\0App\\\\Entity\\\\User\\0status\\\";i:0;s:26:\\\"\\0App\\\\Entity\\\\User\\0createdAt\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-02-01 08:33:34.424265\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:25:\\\"\\0App\\\\Entity\\\\User\\0comments\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:0:{}}s:14:\\\"\\0*\\0initialized\\\";b:1;}s:24:\\\"\\0App\\\\Entity\\\\User\\0ratings\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:0:{}}s:14:\\\"\\0*\\0initialized\\\";b:1;}}s:14:\\\"activation_url\\\";s:97:\\\"http://localhost:8080/activation/b3540bdae5590095565edb2512ce3e8cab2c8a39ec498583c130164403b82bc3\\\";}i:3;a:6:{i:0;N;i:1;N;i:2;N;i:3;N;i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:3:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:18:\\\"noreply@mikhawa.be\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:10:\\\"CV Mikhawa\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:20:\\\"michael.pitz@cf2m.be\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:20:\\\"Activez votre compte\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}i:4;N;}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2026-02-01 08:33:34', '2026-02-01 08:33:34', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `rating`
--

CREATE TABLE `rating` (
                          `id` int(10) UNSIGNED NOT NULL,
                          `rating` smallint(6) NOT NULL,
                          `created_at` datetime NOT NULL,
                          `user_id` int(10) UNSIGNED NOT NULL,
                          `article_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `rating`
--

INSERT INTO `rating` (`id`, `rating`, `created_at`, `user_id`, `article_id`) VALUES
                                                                                 (2, 4, '2026-01-31 18:42:52', 2, 1),
                                                                                 (4, 5, '2026-02-01 01:31:49', 2, 2),
                                                                                 (5, 5, '2026-02-01 08:51:28', 4, 2),
                                                                                 (6, 4, '2026-02-01 19:55:24', 4, 1),
                                                                                 (7, 5, '2026-02-02 05:39:46', 3, 3);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
                        `id` int(10) UNSIGNED NOT NULL,
                        `email` varchar(180) NOT NULL,
                        `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`roles`)),
                        `password` varchar(255) NOT NULL,
                        `user_name` varchar(50) NOT NULL,
                        `activation_token` varchar(64) DEFAULT NULL,
                        `status` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
                        `created_at` datetime NOT NULL,
                        `reset_password_token` varchar(64) DEFAULT NULL,
                        `reset_password_requested_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `email`, `roles`, `password`, `user_name`, `activation_token`, `status`, `created_at`, `reset_password_token`, `reset_password_requested_at`) VALUES
                                                                                                                                                                            (2, 'admin@portfolio.local', '[\"ROLE_ADMIN\"]', '$2y$13$hGj.4zMawUZjC/dPhVqbROarqQNDFnNC18RS7QYqMIR59kEPbt5ze', 'admin', NULL, 1, '2026-02-01 08:27:53', NULL, NULL),
                                                                                                                                                                            (3, 'michael.pitz@cf2m.be', '[]', '$2y$13$Fo5jafvjjme3P5dleCXld.ZirQcRB0mtMvCR77ehi2qERYQ/tX3DG', 'mikhawa', 'b3540bdae5590095565edb2512ce3e8cab2c8a39ec498583c130164403b82bc3', 1, '2026-02-01 08:33:34', NULL, NULL),
                                                                                                                                                                            (4, 'michael.j.pitz@gmail.com', '[]', '$2y$13$VK1zV0lSvfRtnHePewfazePQRl8j/mYsq6dtgAqHWZ6n0M2emY0o2', 'MikePitz', NULL, 1, '2026-02-01 08:47:54', NULL, NULL);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `article`
--
ALTER TABLE `article`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `UNIQ_23A0E66989D9B62` (`slug`),
    ADD KEY `IDX_23A0E6612469DE2` (`category_id`);

--
-- Index pour la table `category`
--
ALTER TABLE `category`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `UNIQ_64C19C1989D9B62` (`slug`);

--
-- Index pour la table `comment`
--
ALTER TABLE `comment`
    ADD PRIMARY KEY (`id`),
    ADD KEY `IDX_9474526CA76ED395` (`user_id`),
    ADD KEY `IDX_9474526C7294869C` (`article_id`);

--
-- Index pour la table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
    ADD PRIMARY KEY (`version`);

--
-- Index pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
    ADD PRIMARY KEY (`id`),
    ADD KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`,`available_at`,`delivered_at`,`id`);

--
-- Index pour la table `rating`
--
ALTER TABLE `rating`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `unique_user_article_rating` (`user_id`,`article_id`),
    ADD KEY `IDX_D8892622A76ED395` (`user_id`),
    ADD KEY `IDX_D88926227294869C` (`article_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `article`
--
ALTER TABLE `article`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `category`
--
ALTER TABLE `category`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `comment`
--
ALTER TABLE `comment`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
    MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `rating`
--
ALTER TABLE `rating`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `article`
--
ALTER TABLE `article`
    ADD CONSTRAINT `FK_23A0E6612469DE2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`);

--
-- Contraintes pour la table `comment`
--
ALTER TABLE `comment`
    ADD CONSTRAINT `FK_9474526C7294869C` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`),
    ADD CONSTRAINT `FK_9474526CA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `rating`
--
ALTER TABLE `rating`
    ADD CONSTRAINT `FK_D88926227294869C` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`),
    ADD CONSTRAINT `FK_D8892622A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
--
-- Base de données : `portfolio_test`
--
DROP DATABASE IF EXISTS `portfolio_test`;
CREATE DATABASE IF NOT EXISTS `portfolio_test` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `portfolio_test`;

-- --------------------------------------------------------

--
-- Structure de la table `article`
--

CREATE TABLE `article` (
                           `id` int(10) UNSIGNED NOT NULL,
                           `title` varchar(255) NOT NULL,
                           `slug` varchar(154) NOT NULL,
                           `content` longtext NOT NULL,
                           `excerpt` longtext DEFAULT NULL,
                           `featured_image` varchar(255) DEFAULT NULL,
                           `is_published` tinyint(4) DEFAULT 0,
                           `created_at` datetime DEFAULT current_timestamp(),
                           `published_at` datetime DEFAULT NULL,
                           `updated_at` datetime DEFAULT NULL,
                           `category_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `category`
--

CREATE TABLE `category` (
                            `id` int(10) UNSIGNED NOT NULL,
                            `title` varchar(100) NOT NULL,
                            `slug` varchar(104) NOT NULL,
                            `color` varchar(7) DEFAULT NULL,
                            `description` varchar(600) DEFAULT NULL,
                            `level` int(10) UNSIGNED DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `comment`
--

CREATE TABLE `comment` (
                           `id` int(10) UNSIGNED NOT NULL,
                           `content` longtext NOT NULL,
                           `created_at` datetime NOT NULL,
                           `is_approved` tinyint(4) NOT NULL,
                           `user_id` int(10) UNSIGNED NOT NULL,
                           `article_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `messenger_messages`
--

CREATE TABLE `messenger_messages` (
                                      `id` bigint(20) NOT NULL,
                                      `body` longtext NOT NULL,
                                      `headers` longtext NOT NULL,
                                      `queue_name` varchar(190) NOT NULL,
                                      `created_at` datetime NOT NULL,
                                      `available_at` datetime NOT NULL,
                                      `delivered_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `rating`
--

CREATE TABLE `rating` (
                          `id` int(10) UNSIGNED NOT NULL,
                          `rating` smallint(6) NOT NULL,
                          `created_at` datetime NOT NULL,
                          `user_id` int(10) UNSIGNED NOT NULL,
                          `article_id` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
                        `id` int(10) UNSIGNED NOT NULL,
                        `email` varchar(180) NOT NULL,
                        `roles` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`roles`)),
                        `password` varchar(255) NOT NULL,
                        `user_name` varchar(50) NOT NULL,
                        `activation_token` varchar(64) DEFAULT NULL,
                        `status` smallint(5) UNSIGNED NOT NULL DEFAULT 0,
                        `reset_password_token` varchar(64) DEFAULT NULL,
                        `reset_password_requested_at` datetime DEFAULT NULL,
                        `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `email`, `roles`, `password`, `user_name`, `activation_token`, `status`, `reset_password_token`, `reset_password_requested_at`, `created_at`) VALUES
    (1, 'active@example.com', '[]', '$2y$04$Nv7gAOI6925owj5RDEACdO40ryV29kJar60ZQ2XeycHGhnXxTIdWO', 'activeuser', NULL, 1, NULL, NULL, '2026-02-02 05:36:24');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `article`
--
ALTER TABLE `article`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `UNIQ_23A0E66989D9B62` (`slug`),
    ADD KEY `IDX_23A0E6612469DE2` (`category_id`);

--
-- Index pour la table `category`
--
ALTER TABLE `category`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `UNIQ_64C19C1989D9B62` (`slug`);

--
-- Index pour la table `comment`
--
ALTER TABLE `comment`
    ADD PRIMARY KEY (`id`),
    ADD KEY `IDX_9474526CA76ED395` (`user_id`),
    ADD KEY `IDX_9474526C7294869C` (`article_id`);

--
-- Index pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
    ADD PRIMARY KEY (`id`),
    ADD KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`,`available_at`,`delivered_at`,`id`);

--
-- Index pour la table `rating`
--
ALTER TABLE `rating`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `unique_user_article_rating` (`user_id`,`article_id`),
    ADD KEY `IDX_D8892622A76ED395` (`user_id`),
    ADD KEY `IDX_D88926227294869C` (`article_id`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
    ADD PRIMARY KEY (`id`),
    ADD UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `article`
--
ALTER TABLE `article`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `category`
--
ALTER TABLE `category`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `comment`
--
ALTER TABLE `comment`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
    MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `rating`
--
ALTER TABLE `rating`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
    MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `article`
--
ALTER TABLE `article`
    ADD CONSTRAINT `FK_23A0E6612469DE2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`);

--
-- Contraintes pour la table `comment`
--
ALTER TABLE `comment`
    ADD CONSTRAINT `FK_9474526C7294869C` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`),
    ADD CONSTRAINT `FK_9474526CA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);

--
-- Contraintes pour la table `rating`
--
ALTER TABLE `rating`
    ADD CONSTRAINT `FK_D88926227294869C` FOREIGN KEY (`article_id`) REFERENCES `article` (`id`),
    ADD CONSTRAINT `FK_D8892622A76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`);
SET FOREIGN_KEY_CHECKS=1;
COMMIT;
