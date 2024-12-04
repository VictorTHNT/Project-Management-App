-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 04 déc. 2024 à 12:31
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `project_management_app`
--

-- --------------------------------------------------------

--
-- Structure de la table `files`
--

CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `uploader_id` int(11) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `upload_timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `file_comments`
--

CREATE TABLE `file_comments` (
  `id` int(11) NOT NULL,
  `file_id` int(11) DEFAULT NULL,
  `commenter_id` int(11) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `comment_timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `messages`
--

CREATE TABLE `messages` (
  `id` int(11) NOT NULL,
  `sender_id` int(11) DEFAULT NULL,
  `receiver_id` int(11) DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `body` text DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  `file_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `budget` decimal(10,2) DEFAULT NULL,
  `manager_id` int(11) DEFAULT NULL,
  `color` varchar(7) NOT NULL DEFAULT '#FFFFFF',
  `cahier_charge` varchar(255) DEFAULT NULL,
  `user_team_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `projects`
--

INSERT INTO `projects` (`id`, `title`, `description`, `start_date`, `end_date`, `budget`, `manager_id`, `color`, `cahier_charge`, `user_team_id`) VALUES
(22, 'DZ', 'DZ', '0001-01-01', '0001-01-01', 0.00, 1, '#480f0f', NULL, NULL),
(27, 'dsq', 'dqs', '0006-05-04', '0034-02-03', 435.00, 5, '#000000', NULL, NULL),
(35, 'azer', 'azeretry', '7777-09-09', '6777-06-07', 4678.00, 5, '#000000', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `status` enum('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `assignee_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `profile` varchar(255) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('member','manager','admin') DEFAULT 'member',
  `activation` enum('oui','non') NOT NULL DEFAULT 'non'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `profile`, `nom`, `prenom`, `email`, `password`, `role`, `activation`) VALUES
(1, 'assets/images/WhatsApp Image 2023-12-29 à 18.38.07_550c0d25.jpg', 'thienot', 'victor', 'victor.thnt@gmail.com', '$2y$10$DCODFF.EuHHU58yOaCig5uKNIDSnMgxshP.lmYSCUvy4D1O3OQIHG', 'admin', 'oui'),
(4, 'assets/images/victor.jpg', 'test', 'test', 'test@test.com', '$2y$10$pBDJPO.IWHFebRxXDuBksejxUeSEEUNntxzCkyZ4AU/UYE3k6YYHC', 'member', 'oui'),
(5, '', 'Hamadi', 'aziz', 'aziz.hamadi@gmail.com', '$2y$10$QTGCmSOB0wHAMgHjbXc1XukqnuPRUteO5mC6w1s3N/AdGV3KYYNvW', 'member', 'oui'),
(6, 'assets/images/default.png', 'DUHOO', 'Lucas', 'lucas.duhoo@gmail.com', '$2y$10$zNev3NCIaN91BF8Ey.IL0eg0itmnrGHaHVaQEi2K8WpyJ4O5WZVli', 'member', 'oui');

-- --------------------------------------------------------

--
-- Structure de la table `user_team`
--

CREATE TABLE `user_team` (
  `team_id` int(11) NOT NULL,
  `team_name` varchar(100) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `post` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `uploader_id` (`uploader_id`);

--
-- Index pour la table `file_comments`
--
ALTER TABLE `file_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `file_id` (`file_id`),
  ADD KEY `commenter_id` (`commenter_id`);

--
-- Index pour la table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `file_id` (`file_id`);

--
-- Index pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `assignee_id` (`assignee_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `user_team`
--
ALTER TABLE `user_team`
  ADD PRIMARY KEY (`team_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `files`
--
ALTER TABLE `files`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `file_comments`
--
ALTER TABLE `file_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `messages`
--
ALTER TABLE `messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT pour la table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT pour la table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `user_team`
--
ALTER TABLE `user_team`
  MODIFY `team_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `files_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`),
  ADD CONSTRAINT `files_ibfk_2` FOREIGN KEY (`uploader_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `file_comments`
--
ALTER TABLE `file_comments`
  ADD CONSTRAINT `file_comments_ibfk_1` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`),
  ADD CONSTRAINT `file_comments_ibfk_2` FOREIGN KEY (`commenter_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`),
  ADD CONSTRAINT `messages_ibfk_4` FOREIGN KEY (`file_id`) REFERENCES `files` (`id`);

--
-- Contraintes pour la table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`),
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`assignee_id`) REFERENCES `users` (`id`);

--
-- Contraintes pour la table `user_team`
--
-- ALTER TABLE `user_team`
--   ADD CONSTRAINT `user_team_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`),
--   ADD CONSTRAINT `user_team_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
-- COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
