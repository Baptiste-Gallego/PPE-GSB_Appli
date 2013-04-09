<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
echo $_POST["lstVisiteur"]; 

// conttrole si la valeur et vide 
if($_POST["dateValid"]!=""){
   echo $_POST["dateValid"];
}else{
   echo "valeur null";
}
?>
