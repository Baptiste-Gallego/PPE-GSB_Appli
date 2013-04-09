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
	<form name="formValidFrais" method="post" action="cValideFrais.php">
		<h1> Validation des frais par visiteur </h1>
		<label class="titre">Choisir le visiteur :</label>
			<select name="lstVisiteur" class="zone">
                            <?php
                            
                            obtenirListeVisiteur();
                            $req1 = "SELECT id, nom FROM visiteur WHERE type=0";
                            $result1 = mysql_query($req1,  $idConnexion);
                            while ($nom = mysql_fetch_row($result1)) {
                                // envoie l'ID 
                                echo '<option value="'.$nom[0].'"';
                                if(isset($_POST['lstVisiteur'])){
                                    if($_POST['lstVisiteur']==$nom[0]){
                                        echo 'selected="selected"';
                                    }
                                }
                                echo  '>'.$nom[1].'</option>';
                            }
                            mysql_free_result($result);
                            ?>
                        </select>
                        <label class="titre">Mois :</label>
                        <select name="dateValid" class="zone">
                        <?php
                            //<input class="zone" type="text" name="dateValid" size="12" />
                            // on propose tous les mois pour lesquels le visiteur a une fiche de frais
                            $req2 = obtenirReqMois();
                            $idJeuMois = mysql_query($req2, $idConnexion);
                            $lgMois = mysql_fetch_assoc($idJeuMois);
                            while ( is_array($lgMois) ) {
                                $mois = $lgMois["mois"];
                                $noMois = intval(substr($mois, 4, 2));
                                $annee = intval(substr($mois, 0, 4));
                        ?>    
                        <option value="<?php 
                        echo $mois; 
                        ?>"
                        <?php
                          if(isset($_POST['dateValid'])){
                                    if($_POST['dateValid']==$mois){
                                        echo 'selected="selected"';
                                    }
                                }
                        
                        ?>
                        ><?php 
                        echo obtenirLibelleMois($noMois) . " " . $annee; 
                        ?>
                        </option>
                        <?php
                                $lgMois = mysql_fetch_assoc($idJeuMois);        
                            }
                            mysql_free_result($idJeuMois);
                        ?>
                        </select>
                         <input id="ok" type="submit" name="button" value="Valider" size="20"
               title="Demandez à consulter cette fiche de frais" />
                         <br />
                         
                <?php
                if(isset($_POST['lstVisiteur'])){
                        if(isset($_POST)){
                            if ($_POST['button']=='Envoyer'){
                                // modifier frais forfait
                                modifierLigneFraisForfait($idConnexion, $_POST['repas'], $_POST['lstVisiteur'], $_POST['dateValid'], 'REP');
                                modifierLigneFraisForfait($idConnexion, $_POST['nuitee'], $_POST['lstVisiteur'], $_POST['dateValid'], 'NUI');
                                modifierLigneFraisForfait($idConnexion, $_POST['etape'], $_POST['lstVisiteur'], $_POST['dateValid'], 'ETP');
                                modifierLigneFraisForfait($idConnexion, $_POST['km'], $_POST['lstVisiteur'], $_POST['dateValid'], 'KM');                            
                              
                                                                                                                               
                                echo 'la modification à été prise en compte';
                            }
                            //passe la fiche a l'etat validée
                            if($_POST['situ']=='V'){
                                modifierEtatFicheFrais($idConnexion, $_POST['dateValid'], $_POST['lstVisiteur'], 'VA');
                            }
                            ////passe la fiche a l'etat renboursée
                            if($_POST['situ']=='R'){
                                modifierEtatFicheFrais($idConnexion, $_POST['dateValid'], $_POST['lstVisiteur'], 'RB');
                            }
                        }
                    echo '<p class="titre" /><div style="clear:left;"><h2>Frais au forfait </h2></div>';
                    $req3 = obtenirLigneFraisForfait($_POST['dateValid'], $_POST['lstVisiteur']);
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
                            $etat = obtenirDetailFicheFrais($idConnexion, $_POST['dateValid'], $_POST['lstVisiteur']);
                ?>
                        
		<table border="1">
			<tr><th>Repas midi</th><th>Nuitée </th><th>Etape</th><th>Km </th><th>Situation</th></tr>
			<tr align="center"><td width="80" ><input type="text" size="3" name="repas" value="<?php echo $repas; ?>"/></td>
				<td width="80"><input type="text" size="3" name="nuitee" value="<?php echo $nuitee; ?>"/></td> 
				<td width="80"> <input type="text" size="3" name="etape" value="<?php echo $etape; ?>"/></td>
				<td width="80"> <input type="text" size="3" name="km" value="<?php echo $km; ?>"/></td>
				<td width="80"> 
					<select size="1" name="situ">
						<option value="E">Enregistré</option>
						<option value="V" <?php if($etat['idEtat']=='VA'){ echo 'selected="selected"';}?>>Validé</option>
						<option value="R" <?php if($etat['idEtat']=='RB'){ echo 'selected="selected"';}?>>Remboursé</option>
					</select></td>
				</tr>
		</table>
		<?php }else{ echo "Pas de ficher de frais pour ce visiteur ce mois"; } 
                echo '<p class="titre" /><div style="clear:left;"><h2>Hors Forfait</h2></div>';
               if(isset($_POST['hcMontant'])){
                for($i = 1; $i <= $_POST['hcMontant']; $i++){
                    
                     if($_POST['hfSitu'.$i]=="N"){                         
                        modifierLigneFraisHorsForfait($idConnexion, $_POST['id'.$i], "REFUSE: ".$_POST['hfLib'.$i], $_POST['hfMont'.$i]);
                     }else{                         
                        modifierLigneFraisHorsForfait($idConnexion, $_POST['id'.$i], $_POST['hfLib'.$i], $_POST['hfMont'.$i]);
                     }
                    
                }
               }
                $nb = 0;
                            $req4 = obtenirLigneFraisHorsForfait($_POST['dateValid'], $_POST['lstVisiteur']);
                            $result4 = mysql_query($req4,  $idConnexion);
                            if(mysql_num_rows($result4)!=0){
                ?>
		
		<table border="1">
			<tr><th>Ref</th><th>Date</th><th>Libellé </th><th>Montant</th><th>Situation</th></tr>
                        <?php 
                            
                            
                            while ($ligneFraisHorsForfait = mysql_fetch_row($result4)) {
                                $nb++;
                        ?>
			<tr align="center">
                                <td width="50" ><input type="text" size="12" name="id<?php echo $nb;?>" value="<?php echo $ligneFraisHorsForfait[0];?>"  readonly/></td>
                                <td width="100" ><input type="text" size="12" name="hfDate<?php echo $nb;?>" value="<?php echo $ligneFraisHorsForfait[4];?>"  readonly/></td>
				<td width="220"><input type="text" size="30" name="hfLib<?php echo $nb;?>" value="<?php echo $ligneFraisHorsForfait[3];?>"/></td> 
				<td width="90"> <input type="text" size="10" name="hfMont<?php echo $nb;?>" value="<?php echo $ligneFraisHorsForfait[5];?>"/></td>
				<td width="80"> 
					<select size="1" name="hfSitu<?php echo $nb;?>">
						<option value="V">Validé</option>
						<option value="N">Non Valide</option>
					</select></td>
				</tr>
                        <?php
                            }
                        ?>
		</table>		
		<p class="titre"></p>
		<div class="titre">Nb Justificatifs</div><input type="text" class="zone" size="4" name="hcMontant" value="<?php echo $nb;?>"/>
                <?php }else{ echo "Pas de ficher de frais pour ce visiteur ce mois"; } ?>
		<p class="titre" /><label class="titre">&nbsp;</label><input class="zone" type="reset" /><input class="zone" name="button" type="submit"/>
                <?php
                
                }
                ?>
	</form>
	</div>
</div>

<?php        
  require($repInclude . "_pied.inc.html");
  require($repInclude . "_fin.inc.php");
?>