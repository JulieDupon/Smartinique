-- Active: 1767792947652@@127.0.0.1@3306@smartinique
-- crétion de la base de données smartinique
CREATE DATABASE IF NOT EXISTS smartinique;
USE smartinique;

-- création de la table role
CREATE TABLE IF NOT EXISTS role (
id_role INT AUTO_INCREMENT PRIMARY KEY,
nom_role VARCHAR(50) NOT NULL
);

-- insertion des rôles
INSERT INTO role (nom_role) VALUES   
('Membre');

-- création de la table membre 
CREATE TABLE IF NOT EXISTS membre (
id_membre INT AUTO_INCREMENT PRIMARY KEY,
nom VARCHAR(100) NOT NULL, 
prenom VARCHAR(100) NOT NULL,
email VARCHAR(150) NOT NULL UNIQUE,
mot_de_passe VARCHAR(255) NOT NULL, 
actif BOOLEAN DEFAULT TRUE,
id_role INT NOT NULL DEFAULT 2, -- Par défaut, membre 

CONSTRAINT fk_membre_role 
FOREIGN KEY (id_role) 
REFERENCES role(id_role)
ON DELETE RESTRICT -- empêcher la suppression d'un rôle s'il est attribué à un membre
ON UPDATE CASCADE -- mettre à jour le rôle d'un membre si le rôle est modifié
);

-- création de la table projet
CREATE TABLE IF NOT EXISTS projet (
id_projet INT AUTO_INCREMENT PRIMARY KEY,
nom_projet VARCHAR(150) NOT NULL,
description TEXT,
date_debut DATE NOT NULL,
date_fin DATE,
statut VARCHAR(50) DEFAULT 'En cours',
id_membre INT NOT NULL, -- Responsable du projet (doit être un admin)

CONSTRAINT fk_projet_membre 
FOREIGN KEY (id_membre) 
REFERENCES membre(id_membre)
ON DELETE RESTRICT
ON UPDATE CASCADE
);

-- création de la table tache 
CREATE TABLE IF NOT EXISTS tache (          
id_tache INT AUTO_INCREMENT PRIMARY KEY,
titre VARCHAR(150) NOT NULL,        
description TEXT,
date_creation DATE DEFAULT (CURRENT_DATE), -- Date du jour par défaut   
date_fin DATE,
statut VARCHAR(50) DEFAULT 'à faire',
id_projet INT NOT NULL,
id_membre INT NOT NULL, -- Membre assigné à la tâche                            

CONSTRAINT fk_tache_projet
    FOREIGN KEY (id_projet)
    REFERENCES projet(id_projet)    
    ON DELETE CASCADE -- Supprime les tâches si le projet est supprimé
    ON UPDATE CASCADE, -- Met à jour si le projet est modifié

CONSTRAINT fk_tache_membre
    FOREIGN KEY (id_membre)
    REFERENCES membre(id_membre)    
    ON DELETE RESTRICT -- Empêche la suppression d'un membre s'il est assigné à une tâche
    ON UPDATE CASCADE -- Met à jour si le membre est modifié
);
