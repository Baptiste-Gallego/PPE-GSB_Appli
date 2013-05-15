-- Mission 2 : Développement de la partie comptable
--
-- Modification de la table `visiteur`
-- ajouter de colune pour Type Visiteur ou Comptable 
-- 1 valider 0 refuser
--
ALTER TABLE visiteur
ADD COLUMN typeVisiteur INT(1);

ALTER TABLE `visiteur` CHANGE `typeVisiteur` `typeVisiteur` INT( 1 ) NOT NULL ;

INSERT INTO visiteur 
(id, nom ,prenom ,login ,mdp ,adresse ,cp ,ville ,dateEmbauche ,typeVisiteur)
VALUES ('bts1', 'comptable', 'Bts', 'comptableBts', 'Btssio13', '1 rue du comptable' , '43130' , 'Retournac' , '2013-01-01', '1'),
		('bts2', 'visiteur', 'Bts', 'visiteurBts', 'Btssio13', '1 rue du comptable' , '43130' , 'Retournac' , '2013-01-01', '0');
