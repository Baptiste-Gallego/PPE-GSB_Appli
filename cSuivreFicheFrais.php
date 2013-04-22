<?php

/** 
 * Script de contrôle et d'affichage du cas d'utilisation "Valider fiche de frais"
 * @author GALLEGO Baptiste
 * @package default
 * @todo  RAS
 */
  $repInclude = './include/';
  require($repInclude . "_init.inc.php");
  
   // page inaccessible si visiteur non connecté
  if ( ! estVisiteurConnecte() ) {
      header("Location: cSeConnecter.php");  
  }
  require($repInclude . "_entete.inc.html");
  require($repInclude . "_sommaire.inc.php");

?>
<!-- Division principale -->
<div name="droite" style="float:left;width:80%;">
	<div name="haut" style="margin: 2 2 2 2 ;height:10%;float:left;"><h1>Validation des Frais</h1></div>	
	<div name="bas" style="margin : 10 2 2 2;clear:left;background-color:EE8844;height:88%;">
	<form name="formValidFrais" method="post">
		<h1> Suivre le paiement fiche de frais</h1>
		<label class="titre">Choisir le visiteur :</label>
			<select name="lstVisiteur" class="zone">
                            <?php                         
                            $req1 = "SELECT fichefrais.*, visiteur.nom
                                    FROM fichefrais LEFT JOIN  visiteur ON (fichefrais.idVisiteur = visiteur.id)
                                    WHERE idEtat = 'VA'";
                            $result1 = mysql_query($req1,  $idConnexion);
                            while ($nom = mysql_fetch_row($result1)) {
                                // envoie l'ID 
                                echo '<option value="'.$nom[0].'$'.$nom[1].'"';
                                if(isset($_POST['lstVisiteur'])){
                                    if($_POST['lstVisiteur']==$nom[2]){
                                        echo 'selected="selected"';
                                    }
                                }
                                $noMois = intval(substr($nom[1], 4, 2));
                                $annee = intval(substr($nom[1], 0, 4));
                                echo  '>'.obtenirLibelleMois($noMois) . " " . $annee.' '.$nom[6].'</option>';
                            }
                            mysql_free_result($result);
                            ?>
                        </select>
                       
                         <input id="ok" type="submit" name="button" value="Afficher" size="20" 
               title="Demandez à consulter cette fiche de frais" />
                         <br />
                         
               
                       <?php
                       if(isset($_POST)){
                           if(isset($_POST['lstVisiteur'])){
                               $post = explode('$', $_POST['lstVisiteur']);
                           if(isset($_POST['button'])){
                               if($_POST['button']=="Renboursée"){
                                   modifierEtatFicheFrais($idConnexion, $post[1], $post[0], "RB");
                                   echo "Fiche modifier";
                                    }
                                
                               if($_POST['button']=="Afficher"){
                                echo '<p class="titre" /><div style="clear:left;"><h2>Frais au forfait </h2></div>';
                                         $req3 = obtenirLigneFraisForfait($post[1], $post[0]);
                                                 $result3 = mysql_query($req3,  $idConnexion);
                                                if(mysql_num_rows($result3)!=0){
                                                 while ($ligneFraisForfait = mysql_fetch_row($result3)) {                                    
                                                         if($ligneFraisForfait[2]=="REP"){
                                                             $repas = $ligneFraisForfait[3];
                                                         }elseif ($ligneFraisForfait[2]=="NUI") {
                                                             $nuitee = $ligneFraisForfait[3];                                        
                                                         }elseif ($ligneFraisForfait[2]=="ETP") {
                                                             $etape = $ligneFraisForfait[3];
                                                         }elseif ($ligneFraisForfait[2]=="KM") {
                                                             $km = $ligneFraisForfait[3];
                                                         }                                
                                                 }


                             ?>

                                     <table border="1">
                                             <tr><th>Repas midi</th><th>Nuitée </th><th>Etape</th><th>Km </th></tr>
                                             <tr align="center"><td width="80" ><input type="text" size="3" name="repas" value="<?php echo $repas; ?>"/></td>
                                                     <td width="80"><input type="text" size="3" name="nuitee" value="<?php echo $nuitee; ?>"/></td> 
                                                     <td width="80"> <input type="text" size="3" name="etape" value="<?php echo $etape; ?>"/></td>
                                                     <td width="80"> <input type="text" size="3" name="km" value="<?php echo $km; ?>"/></td>

                                                     </tr>
                                     </table> 
                                      <?php 
                                             }
                            echo '<p class="titre" /><div style="clear:left;"><h2>Hors Forfait</h2></div>';
                            $nb = 0;
                            $req4 = obtenirLigneFraisHorsForfait($post[1], $post[0]);
                            $result4 = mysql_query($req4,  $idConnexion);
                            if(mysql_num_rows($result4)!=0){
                                 ?>

                                 <table border="1">
                                         <tr><th>Ref</th><th>Date</th><th>Libellé </th><th>Montant</th></tr>
                                         <?php 


                                while ($ligneFraisHorsForfait = mysql_fetch_row($result4)) {
                                    $nb++;
                                         ?>
                                         <tr align="center">
                                                 <td width="50" ><input type="text" size="12" name="id<?php echo $nb;?>" value="<?php echo $ligneFraisHorsForfait[0];?>"  readonly/></td>
                                                 <td width="100" ><input type="text" size="12" name="hfDate<?php echo $nb;?>" value="<?php echo $ligneFraisHorsForfait[4];?>"  readonly/></td>
                                                 <td width="220"><input type="text" size="30" name="hfLib<?php echo $nb;?>" value="<?php echo $ligneFraisHorsForfait[3];?>"/></td> 
                                                 <td width="90"> <input type="text" size="10" name="hfMont<?php echo $nb;?>" value="<?php echo $ligneFraisHorsForfait[5];?>"/></td>

                                                 </tr>
                                         <?php
                                 }
                                            
                                        ?>
                        </table>		
                        <p class="titre"></p>
                        <div class="titre">Nb Justificatifs</div><input type="text" class="zone" size="4" name="hcMontant" value="<?php echo $nb;?>"/>
                        <?php }else{ echo "Pas de ficher de frais pour ce visiteur ce mois"; } ?>
                        <p class="titre" /><label class="titre">&nbsp;</label><input class="zone" name="button" type="submit" value="Renboursée"/>
                           
                        <?php   
                            }
                           }
                         }
                       }
                      ?>
			
	
	</form>
	</div>
</div>

<?php        
  require($repInclude . "_pied.inc.html");
  require($repInclude . "_fin.inc.php");

