select idFraisForfait, libelle, quantite, montant, idTypeVehicule 
from LigneFraisForfait inner join FraisForfait on FraisForfait.id = LigneFraisForfait.idFraisForfait 
where idVisiteur='a131' and mois='201304' 

SELECT prixAuKm 
FROM typevehicule
WHERE id='V4D'