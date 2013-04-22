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
ADD COLUMN idTypeVehicule char(3)
