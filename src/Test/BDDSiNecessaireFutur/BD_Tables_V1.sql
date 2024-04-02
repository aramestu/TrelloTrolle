CREATE TABLE Utilisateurs(
   login VARCHAR(32),
   nomUtilisateur VARCHAR(32) NOT NULL,
   prenomUtilisateur VARCHAR(32) NOT NULL,
   emailUtilisateur VARCHAR(64) NOT NULL,
   mdpHache VARCHAR(256) NOT NULL,
   PRIMARY KEY(login)
);

CREATE TABLE Tableaux(
   idTableau SERIAL,
   codeTableau VARCHAR(64) NOT NULL,
   titreTableau VARCHAR(64) NOT NULL,
   proprietaireTableau VARCHAR(32) NOT NULL,
   PRIMARY KEY(idTableau),
   FOREIGN KEY(proprietaireTableau) REFERENCES Utilisateurs(login)
);

CREATE TABLE Colonnes(
   idColonne SERIAL,
   titreColonne VARCHAR(64) NOT NULL,
   idTableau INT NOT NULL,
   PRIMARY KEY(idColonne),
   FOREIGN KEY(idTableau) REFERENCES Tableaux(idTableau)
);

CREATE TABLE Cartes(
   idCarte SERIAL,
   titreCarte VARCHAR(64) NOT NULL,
   descriptifCarte TEXT,
   couleurCarte VARCHAR(7),
   idColonne INT NOT NULL,
   PRIMARY KEY(idCarte),
   FOREIGN KEY(idColonne) REFERENCES Colonnes(idColonne)
);

CREATE TABLE Participer(
   login VARCHAR(32),
   idTableau INT,
   PRIMARY KEY(login, idTableau),
   FOREIGN KEY(login) REFERENCES Utilisateurs(login),
   FOREIGN KEY(idTableau) REFERENCES Tableaux(idTableau)
);

CREATE TABLE Affecter(
   login VARCHAR(32),
   idCarte INT,
   PRIMARY KEY(login, idCarte),
   FOREIGN KEY(login) REFERENCES Utilisateurs(login),
   FOREIGN KEY(idCarte) REFERENCES Cartes(idCarte)
);
