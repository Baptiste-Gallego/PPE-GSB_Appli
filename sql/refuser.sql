--
-- Modification de la table `lignefraishorsforfait`
-- ajouter de colune pour valider l'acepetation 
-- 1 valider 0 refuser
--
ALTER TABLE lignefraishorsforfait
ADD COLUMN accepter boolean
