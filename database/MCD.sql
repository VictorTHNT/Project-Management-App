-- Suppression des tables existantes si elles existent
DROP TABLE IF EXISTS Project_Team;
DROP TABLE IF EXISTS Tasks;
DROP TABLE IF EXISTS Projects;
DROP TABLE IF EXISTS Users;

-- Création de la base de données
CREATE DATABASE IF NOT EXISTS project_management_app;

-- Utilisation de la base de données
USE project_management_app;

-- Création de la table 'Users'
CREATE TABLE Users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
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

-- Création de la table 'Project_Team'
CREATE TABLE Project_Team (
    project_id INT,
    user_id INT,
    PRIMARY KEY (project_id, user_id),
    FOREIGN KEY (project_id) REFERENCES Projects(id),
    FOREIGN KEY (user_id) REFERENCES Users(id)
);
