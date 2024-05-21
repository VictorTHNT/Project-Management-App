-- Création de la base de données
CREATE DATABASE IF NOT EXISTS project_management_app;

-- Utilisation de la base de données
USE project_management_app;

-- Suppression des tables existantes si elles existent
DROP TABLE IF EXISTS User_Team;
DROP TABLE IF EXISTS Tasks;
DROP TABLE IF EXISTS Projects;
DROP TABLE IF EXISTS Users;
DROP TABLE IF EXISTS Messages;
DROP TABLE IF EXISTS Files;
DROP TABLE IF EXISTS File_Comments;

-- Création de la table 'Users'
CREATE TABLE Users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('member', 'manager') DEFAULT 'member' -- Ajout du rôle
);

-- Création de la table 'Projects'
CREATE TABLE Projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    start_date DATE,
    end_date DATE,
    budget DECIMAL(10, 2),
    manager_id INT,
    FOREIGN KEY (manager_id) REFERENCES Users(id)
);

-- Création de la table 'Tasks'
CREATE TABLE Tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    status ENUM('pending', 'in_progress', 'completed') NOT NULL DEFAULT 'pending',
    start_date DATE,
    end_date DATE,
    project_id INT,
    assignee_id INT,
    FOREIGN KEY (project_id) REFERENCES Projects(id),
    FOREIGN KEY (assignee_id) REFERENCES Users(id)
);

-- Création de la table 'User_Team'
CREATE TABLE User_Team (
    team_id INT AUTO_INCREMENT PRIMARY KEY,
    team_name VARCHAR(100) NOT NULL,
    project_id INT,
    user_id INT,
    post VARCHAR(100), -- Ajout du poste dans l'équipe
    FOREIGN KEY (project_id) REFERENCES Projects(id),
    FOREIGN KEY (user_id) REFERENCES Users(id)
);

-- Ajout de la table 'Files' pour les fichiers déposés
CREATE TABLE Files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT,
    uploader_id INT,
    file_path VARCHAR(255) NOT NULL,
    upload_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES Projects(id),
    FOREIGN KEY (uploader_id) REFERENCES Users(id)
);

-- Ajout de la table 'File_Comments' pour les commentaires sur les fichiers
CREATE TABLE File_Comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_id INT,
    commenter_id INT,
    comment TEXT,
    comment_timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (file_id) REFERENCES Files(id),
    FOREIGN KEY (commenter_id) REFERENCES Users(id)
);

-- Modification de la table 'Messages' pour inclure la messagerie de groupe et les fichiers
CREATE TABLE Messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT,
    receiver_id INT, -- Peut être NULL pour les messages de groupe
    project_id INT, -- Peut être NULL pour les messages privés
    subject VARCHAR(255),
    body TEXT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    file_id INT, -- Référence optionnelle à un fichier
    FOREIGN KEY (sender_id) REFERENCES Users(id),
    FOREIGN KEY (receiver_id) REFERENCES Users(id),
    FOREIGN KEY (project_id) REFERENCES Projects(id),
    FOREIGN KEY (file_id) REFERENCES Files(id)
);
