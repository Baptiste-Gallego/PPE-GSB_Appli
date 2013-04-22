-- Tâche 1 : Gestion du refus de certains frais hors forfait
--
-- Modification de la table `lignefraishorsforfait`
-- ajouter de colune pour valider l'acepetation 
-- 1 valider 0 refuser
--
ALTER TABLE lignefraishorsforfait
ADD COLUMN accepter boolean;

-- Tâche 2 : Sécurisation des mots de passe stockés
--
-- Codage du mot de passe en SHA1
--
UPDATE Visiteur SET mdp = SHA1(mdp);

-- Tâche 3 : Gestion plus fine de l'indemnisation kilométrique
--
-- Structure de la table `typeVehicule`
--
CREATE TABLE IF NOT EXISTS `typeVehicule`(
	`id` char(3) NOT NULL,
	`libelle` char(25) DEFAULT NULL,
	`prixAuKm` decimal(5,2) DEFAULT NULL,
	PRIMARY KEY (`id`)
);

--
-- Contenu de la table `typeVehicule`
--
INSERT INTO typeVehicule (`id`, `libelle`, `prixAuKm`) VALUES
('V4D', 'Vehicule 4CV Diesel', 0.52),
('V5D','Vehicule 5/6CV Diesel', 0.58),
('V4E','Vehicule 4CV Essence', 0.62),
('V5E','Vehicule 5/6CV Essence', 0.67);

--
-- Modification de la table `lignefraisforfait`
--
ALTER TABLE lignefraisforfait
ADD COLUMN idTypeVehicule char(3);