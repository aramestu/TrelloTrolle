-- Insérer des utilisateurs
INSERT INTO Utilisateurs (login, nomUtilisateur, prenomUtilisateur, emailUtilisateur, mdpHache)
VALUES ('utilisateur1', 'Nom Utilisateur 1', 'Prenom Utilisateur 1', 'utilisateur1@example.com', 'mot_de_passe_1_hash'),
       ('utilisateur2', 'Nom Utilisateur 2', 'Prenom Utilisateur 2', 'utilisateur2@example.com', 'mot_de_passe_2_hash');

-- Insérer des tableaux
INSERT INTO Tableaux (codeTableau, titreTableau, proprietaireTableau)
VALUES ('code_tableau_1', 'Titre Tableau 1', 'utilisateur1'),
       ('code_tableau_2', 'Titre Tableau 2', 'utilisateur2');

-- Insérer des colonnes
INSERT INTO Colonnes (titreColonne, idTableau)
VALUES ('Colonne A', (SELECT idTableau FROM Tableaux WHERE codeTableau = 'code_tableau_1')),
       ('Colonne B', (SELECT idTableau FROM Tableaux WHERE codeTableau = 'code_tableau_1')),
       ('Colonne X', (SELECT idTableau FROM Tableaux WHERE codeTableau = 'code_tableau_2')),
       ('Colonne Y', (SELECT idTableau FROM Tableaux WHERE codeTableau = 'code_tableau_2'));

-- Insérer des cartes
INSERT INTO Cartes (titreCarte, descriptifCarte, couleurCarte, idColonne)
VALUES ('Carte 1', 'Description Carte 1', '#FF0000', (SELECT idColonne FROM Colonnes WHERE titreColonne = 'Colonne A')),
       ('Carte 2', 'Description Carte 2', '#00FF00', (SELECT idColonne FROM Colonnes WHERE titreColonne = 'Colonne B')),
       ('Carte 3', 'Description Carte 3', '#0000FF', (SELECT idColonne FROM Colonnes WHERE titreColonne = 'Colonne X')),
       ('Carte 4', 'Description Carte 4', '#FFFF00', (SELECT idColonne FROM Colonnes WHERE titreColonne = 'Colonne Y'));

-- Insérer des participations
INSERT INTO Participer (login, idTableau)
VALUES ('utilisateur1', (SELECT idTableau FROM Tableaux WHERE codeTableau = 'code_tableau_1')),
       ('utilisateur2', (SELECT idTableau FROM Tableaux WHERE codeTableau = 'code_tableau_2'));

-- Insérer des affectations
INSERT INTO Affecter (login, idCarte)
VALUES ('utilisateur1', (SELECT idCarte FROM Cartes WHERE titreCarte = 'Carte 1')),
       ('utilisateur2', (SELECT idCarte FROM Cartes WHERE titreCarte = 'Carte 2')),
       ('utilisateur1', (SELECT idCarte FROM Cartes WHERE titreCarte = 'Carte 3')),
       ('utilisateur2', (SELECT idCarte FROM Cartes WHERE titreCarte = 'Carte 4'));
