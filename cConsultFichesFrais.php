<?php
/** 
 * Script de contrôle et d'affichage du cas d'utilisation "Consulter une fiche de frais"
 * @package default
 * @todo  RAS
 */
  $repInclude = './include/';
  require($repInclude . "_init.inc.php");
  
  // page inaccessible si visiteur non connecté
  if ( ! estVisiteurConnecte() ) {
      header("Location: cSeConnecter.php");  
  }

  // acquisition des données entrées, ici le numéro de mois et l'étape du traitement
  $moisSaisi=lireDonneePost("lstMois", "");
  $etape=lireDonneePost("etape",""); 
 
    if ($etape != "demanderConsult" && $etape != "validerConsult") {
        // si autre valeur, on considère que c'est le début du traitement
        $etape = "demanderConsult";        
    } 
    if ($etape == "validerConsult") { // l'utilisateur valide ses nouvelles données

        // vérification de l'existence de la fiche de frais pour le mois demandé
        $existeFicheFrais = existeFicheFrais($idConnexion, $moisSaisi, obtenirIdUserConnecte());
        // si elle n'existe pas, on la crée avec les élets frais forfaitisés à 0
        if ( !$existeFicheFrais ) {
            ajouterErreur($tabErreurs, "Le mois demandé est invalide");
        }
        else {
            // récupération des données sur la fiche de frais demandée
            $tabFicheFrais = obtenirDetailFicheFrais($idConnexion, $moisSaisi, obtenirIdUserConnecte());
        }
    } 
    if(isset($_POST['button'])){
        $visiteur = obtenirDetailVisiteur($idConnexion, obtenirIdUserConnecte());
        
        
        require('fpdf17/fpdf.php');
        
        class PDF extends FPDF
        {
            // En-tête
            function Header()
            {
            // Logo
            $this->Image('images/logo.jpg', 80);
            // Saut de ligne
            $this->Ln(20);
            }
            
        }
       
        // Instanciation de la classe dérivée
        $pdf = new PDF();
        $pdf->AddPage();
        $pdf->SetFont('Arial','B',16);
        $pdf->SetDrawColor(0,0,128);        
        $pdf->SetTextColor(0,0,128);
        //titre
        $pdf->Cell(0,10,'REMBOURSEMENT DE FRAIS ENGAGES',1,1,'C');
        $pdf->SetTextColor(0);
        $pdf->SetFont('Arial','',12);
        $pdf->Cell(0,10,utf8_decode ('Visiteur: '.$visiteur['prenom'].' '.$visiteur['nom']));
        $pdf->ln();
        $pdf->Cell(0,10,'Mois: '.obtenirLibelleMois(intval(substr($moisSaisi,4,2))) . " " . substr($moisSaisi,0,4));
        $pdf->ln();
        //en-tête frais forfais
        $pdf->SetFillColor(0,0,128);
        $pdf->SetTextColor(255);
        $pdf->Cell(70,7,'Frais Forfaitaires',1,0,'C',true);
        $pdf->Cell(40,7, utf8_decode('Quantité'),1,0,'C',true);
        $pdf->Cell(40,7,'Montant unitaire',1,0,'C',true);        
        $pdf->Cell(40,7,'Total',1,0,'C',true);
        $pdf->SetTextColor(0);
        $pdf->Ln();
        $total = 0;
         // Données
            // forfaitisés du visiteur connecté pour le mois demandé
            $req = obtenirReqEltsForfaitFicheFraisPdf($moisSaisi, obtenirIdUserConnecte());
            $idJeuEltsFraisForfait = mysql_query($req, $idConnexion);
        while ($row = mysql_fetch_array($idJeuEltsFraisForfait, MYSQL_ASSOC))
        {
                    
                 $pdf->Cell(70,6, utf8_decode($row['libelle']),1);
                 $pdf->Cell(40,6,$row['quantite'],1,0,'R');
                 if(isset($row['idTypeVehicule'])){
                        $prixAuKm = obtenirPrixAuKm($idConnexion, $row['idTypeVehicule']);
                        $pdf->Cell(40,6,$prixAuKm,1,0,'R');
                        $pdf->Cell(40,6,$prixAuKm*$row['quantite'],1,0,'R');
                        $total += $prixAuKm*$row['quantite'];
                 }else{
                    $pdf->Cell(40,6,$row['montant'],1,0,'R');
                    $pdf->Cell(40,6,$row['montant']*$row['quantite'],1,0,'R');
                    $total += $row['montant']*$row['quantite'];
            }                
            $pdf->Ln();
        }
        $pdf->Ln();
        $pdf->Cell(0,10,'Autres Frais',0,0,'C');
        $pdf->Ln();
        // hors Forfais
        $pdf->SetTextColor(255);
        $pdf->Cell(40,7,'Date',1,0,'C',true);
        $pdf->Cell(110,7, utf8_decode('Libellé'),1,0,'C',true);
        $pdf->Cell(40,7,'Montant',1,0,'C',true);
        $pdf->SetTextColor(0);
        $pdf->Ln();
        // demande de la requête pour obtenir la liste des éléments hors
        // forfait du visiteur connecté pour le mois demandé
        $req = obtenirReqEltsHorsForfaitFicheFrais($moisSaisi, obtenirIdUserConnecte());
        $idJeuEltsHorsForfait = mysql_query($req, $idConnexion);
        $lgEltHorsForfait = mysql_fetch_assoc($idJeuEltsHorsForfait);
        // parcours des éléments hors forfait 
        while ( is_array($lgEltHorsForfait) ) {
                    $pdf->Cell(40,7,$lgEltHorsForfait["date"],1,0);
                    $pdf->Cell(110,7, utf8_decode(filtrerChainePourNavig($lgEltHorsForfait["libelle"]) ),1,0);
                    $pdf->Cell(40,7,$lgEltHorsForfait["montant"],1,0,'R');
                    $total += $lgEltHorsForfait["montant"];
                    $pdf->Ln();
                 $lgEltHorsForfait = mysql_fetch_assoc($idJeuEltsHorsForfait);
            }
            mysql_free_result($idJeuEltsHorsForfait);
        $pdf->Ln();
        $pdf->Cell(130,7, '',0,0,'C');
        $pdf->Cell(20,7,'Total',1,0);
        $pdf->Cell(40,7,$total,1,0,'R');
        $pdf->Ln();
        $pdf->Ln();
        $pdf->Cell(130,7,'',0,0,'C');
        $pdf->Cell(40,7,utf8_decode('Fait à Paris, le '.date("d/m/Y")),0,0,'C');
        $pdf->Ln();
        $pdf->Cell(130,7,'',0,0,'C');
        $pdf->Cell(40,7,utf8_decode("Vu l'agent comptable"),0,0,'C');
        $pdf->Output();
         
        mysql_free_result($idJeuEltsFraisForfait);
         
    }
    require($repInclude . "_entete.inc.html");
    require($repInclude . "_sommaire.inc.php");
  print_r($_POST);
?>
  <!-- Division principale -->
  <div id="contenu">
      <h2>Mes fiches de frais</h2>
      <h3>Mois à sélectionner : </h3>
      <form action="" method="post">
      <div class="corpsForm">
          <input type="hidden" name="etape" value="validerConsult" />
      <p>
        <label for="lstMois">Mois : </label>
        <select id="lstMois" name="lstMois" title="Sélectionnez le mois souhaité pour la fiche de frais">
            <?php
                // on propose tous les mois pour lesquels le visiteur a une fiche de frais
                $req = obtenirReqMoisFicheFrais(obtenirIdUserConnecte());
                $idJeuMois = mysql_query($req, $idConnexion);
                $lgMois = mysql_fetch_assoc($idJeuMois);
                while ( is_array($lgMois) ) {
                    $mois = $lgMois["mois"];
                    $noMois = intval(substr($mois, 4, 2));
                    $annee = intval(substr($mois, 0, 4));
            ?>    
            <option value="<?php echo $mois; ?>"<?php if ($moisSaisi == $mois) { ?> selected="selected"<?php } ?>><?php echo obtenirLibelleMois($noMois) . " " . $annee; ?></option>
            <?php
                    $lgMois = mysql_fetch_assoc($idJeuMois);        
                }
                mysql_free_result($idJeuMois);
            ?>
        </select>
      </p>
      </div>
      <div class="piedForm">
      <p>
        <input id="ok" type="submit" value="Valider" size="20"
               title="Demandez à consulter cette fiche de frais" />
        <input class="zone" name="button" type="submit" Value="Télécharger" action="pdf.php"/>
        <input id="annuler" type="reset" value="Effacer" size="20" />
      </p> 
      </div>
        
      </form>
<?php      

// demande et affichage des différents éléments (forfaitisés et non forfaitisés)
// de la fiche de frais demandée, uniquement si pas d'erreur détecté au contrôle
if(!isset($_POST['button'])){
          
    if ( $etape == "validerConsult" ) {
        if ( nbErreurs($tabErreurs) > 0 ) {
            echo toStringErreurs($tabErreurs) ;
        }
        else {
?>
    <h3>Fiche de frais du mois de <?php echo obtenirLibelleMois(intval(substr($moisSaisi,4,2))) . " " . substr($moisSaisi,0,4); ?> : 
    <em><?php echo $tabFicheFrais["libelleEtat"]; ?> </em>
    depuis le <em><?php echo $tabFicheFrais["dateModif"]; ?></em></h3>
    <div class="encadre">
    <p>Montant validé : <?php echo $tabFicheFrais["montantValide"] ;
        ?>              
    </p>
<?php          
            // demande de la requête pour obtenir la liste des éléments 
            // forfaitisés du visiteur connecté pour le mois demandé
            $req = obtenirReqEltsForfaitFicheFrais($moisSaisi, obtenirIdUserConnecte());
            $idJeuEltsFraisForfait = mysql_query($req, $idConnexion);
            echo mysql_error($idConnexion);
            $lgEltForfait = mysql_fetch_assoc($idJeuEltsFraisForfait);
            // parcours des frais forfaitisés du visiteur connecté
            // le stockage intermédiaire dans un tableau est nécessaire
            // car chacune des lignes du jeu d'enregistrements doit être doit être
            // affichée au sein d'une colonne du tableau HTML
            
            $tabEltsFraisForfait = array();
            while ( is_array($lgEltForfait) ) {
                $tabEltsFraisForfait[$lgEltForfait["libelle"]] = $lgEltForfait["quantite"];
                $lgEltForfait = mysql_fetch_assoc($idJeuEltsFraisForfait);
            }
            mysql_free_result($idJeuEltsFraisForfait);
            ?>
  	<table class="listeLegere">
  	   <caption>Quantités des éléments forfaitisés</caption>
        <tr>
            <?php
                        
            // premier parcours du tableau des frais forfaitisés du visiteur connecté
            // pour afficher la ligne des libellés des frais forfaitisés
            foreach ( $tabEltsFraisForfait as $unLibelle => $uneQuantite ) {
            ?>
                <th><?php echo $unLibelle ; ?></th>
            <?php
            }
            ?>
        </tr>
        <tr>
            <?php
            // second parcours du tableau des frais forfaitisés du visiteur connecté
            // pour afficher la ligne des quantités des frais forfaitisés
            foreach ( $tabEltsFraisForfait as $unLibelle => $uneQuantite ) {
            ?>
                <td class="qteForfait"><?php echo $uneQuantite ; ?></td>
            <?php
            }
            ?>
        </tr>
    </table>
  	<table class="listeLegere">
  	   <caption>Descriptif des éléments hors forfait - <?php echo $tabFicheFrais["nbJustificatifs"]; ?> justificatifs reçus -
       </caption>
             <tr>
                <th class="date">Date</th>
                <th class="libelle">Libellé</th>
                <th class="montant">Montant</th>                
             </tr>
<?php          
            // demande de la requête pour obtenir la liste des éléments hors
            // forfait du visiteur connecté pour le mois demandé
            $req = obtenirReqEltsHorsForfaitFicheFrais($moisSaisi, obtenirIdUserConnecte());
            $idJeuEltsHorsForfait = mysql_query($req, $idConnexion);
            $lgEltHorsForfait = mysql_fetch_assoc($idJeuEltsHorsForfait);
            
            // parcours des éléments hors forfait 
            while ( is_array($lgEltHorsForfait) ) {
            ?>
                <tr>
                   <td><?php echo $lgEltHorsForfait["date"] ; ?></td>
                   <td><?php echo filtrerChainePourNavig($lgEltHorsForfait["libelle"]) ; ?></td>
                   <td><?php echo $lgEltHorsForfait["montant"] ; ?></td>
                </tr>
            <?php
                $lgEltHorsForfait = mysql_fetch_assoc($idJeuEltsHorsForfait);
            }
            mysql_free_result($idJeuEltsHorsForfait);
  ?>
    </table>
  </div>
<?php
        }
    }
}
?>    
  </div>
<?php        
  require($repInclude . "_pied.inc.html");
  require($repInclude . "_fin.inc.php");
?> 