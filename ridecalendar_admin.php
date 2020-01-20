<?php 

global $debug;
global $connection;


//print_r($_POST);

include 'ridecalendar_database.php';
include 'version.php';

/******************************************************************************/
/*                                                                            */
/*                                                                            */
/******************************************************************************/

$FrenchMonthes = array("", "Janvier", "Février", "Mars", "Avril", "Mai",
                       "Juin", "Juillet", "Aout", "Septembre",
                       "Octobre", "Novembre", "Décembre");
$CurrentYear = date("Y");
$CurrentMonth = date("m");
$CurrentDay = date("d");

$DisplayList = 1;

?>

<STYLE type="text/css">
<!--
table.ridetable {
    border-width:0px;
    border-style:solid;
    border-collapse: collapse;
    text-align: center;
 }

 table.ridetable th {
    border-width:1px;
    border-style:solid;
    background-color: #DDDDDD;
 }
 table.ridetable td {
    border-width:1px;
    border-style:solid;
 }
 
 table.ridetable th.emptycell {
    border-width:0px;
    border-style:none;
    background-color: #FFFFFF;
 }

table.ridetable th.name {
    border-width:1px;
    border-style:solid;
    text-align: left;
    background-color: #EEEEEE;
 }
--></STYLE>
<?php



/********************************/ 
/*                              */
/* CONNECTION - EXECUTION       */ 
/*                              */ 
/********************************/ 

$mabasededonnee="vttcanalriders";

mysql_query("SET NAMES UTF8"); 

// test la connection 
if ( ! $connection ) 
die ("connection impossible");

// Connecte la base 
mysql_select_db($mabasededonnee) or die ("pas de connection"); 
if (mysql_errno($connection))
{
   TableCreation();
}


// check if a ride has to be modified or deleted...
$EditRide = -1;
$DeleteRide = -1;
if (isset($_POST['EventIdList']))
{
   echo "Event ID = ".$_POST['EventIdList']."<BR>";
   $eventList = explode(";", $_POST['EventIdList']);
   foreach ($eventList as $id)
   {
      $keyEdit = "EditListId".$id."_x";
      $keyDel = "DeleteListId".$id."_x";
      // check if such id has been posted or not
      if (isset($_POST[$keyEdit]))
      {
        $EditRide = $id;
      }
      if (isset($_POST[$keyDel]))
      {
        $DeleteRide = $id;
      }
   }
      
}


/********************************/ 
/*                              */ 
/*    AJOUTER MODIFIER SUPP     */
/*                              */ 
/********************************/


if (isset($_POST['ajouter_sql'])) 
{
   // add the event
   $propostable = $_POST['proposals'];
   foreach($propostable as $key => $value)
   {
      if ($value == "")
         unset($propostable[$key]);   
   }

   $addDefault = false;
   if (isset($_POST['AddDefault']))
   {
      $addDefault = true;
   }
   $propos = implode("@", $propostable);
   
   $date = $_POST['year']."-".$_POST['month']."-".$_POST['day'];
   $query='INSERT INTO ridecal_events (date, title, proposals, add_default)
      VALUES("'.$date.'","'.$_POST['title'].'", "'.
      $propos.'", "'.$addDefault.'")';
   if ($debug == 1) echo $query.'<br/>';
   $result = mysql_query($query);
   if (!$result) 
   {
      $message  = 'Requète invalide : ' . mysql_error() . "\n";
      $message .= 'Requète complète : ' . $query;
      die($message);
   }
}


if (isset($_POST['modifier_sql'])) 
{
   $addDefault = 0;
   if (isset($_POST['AddDefault']))
   {
      $addDefault = 1;
   }
   
   $propostable = $_POST['proposals'];
   foreach($propostable as $key => $value)
   {
      if ($value == "")
         unset($propostable[$key]);   
   }
   $propos = implode("@", $propostable);

   $date = $_POST['year']."-".$_POST['month']."-".$_POST['day'];
   $query = 'UPDATE ridecal_events SET title="'.$_POST['title'].'", 
      date="'.$date.'",
      proposals="'.$propos.'",
      add_default="'.$addDefault.'"
      WHERE id_events="'.$_POST['id_sql'].'"';
     
   if ($debug) echo $query."<br>";
   $result = mysql_query($query);
   if (!$result) 
   {
      $message  = 'Requète invalide : ' . mysql_error() . "<br>";
      die($message);
   }
   //echo 'Modification terminée !! <br /><br />';
}

if (isset($_POST['supprimer_ok'])) 
{
   DeleteEvent($_POST['id_sql']);
}

/********************************/ 
/*                              */ 
/* Confirmation de suppression */
/*                              */ 
/********************************/
if ($DeleteRide != -1)
{
   $DisplayList = 0;
   echo 'Voulez-vous supprimer cette sortie : ';
   
   $q = mysql_query("SELECT * FROM ridecal_events WHERE id_events = ".
      $DeleteRide."");
   $r = mysql_fetch_array($q);
   $year = substr($r['date'], 0, 4);
   $month = 0 + substr($r['date'], 5, 2);
   $day = substr($r['date'], 8, 2);
   
   echo $day." ".$FrenchMonthes[$month]." ".$year." ?<br> ";
?>   
   <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
   <input type="hidden" name="id_sql" value="<?php echo $DeleteRide; ?>">
   <br>
   <input type="submit" name="supprimer_ok" value="Valider">
   <input type="submit" name="annuler" value="Annuler">
   </form>
<?php
}



/********************************/ 
/*                              */ 
/* formulaire de modification   */
/*                              */ 
/********************************/

if ($EditRide != -1) 
{
   $q = mysql_query("SELECT * FROM ridecal_events WHERE id_events = ".
      $EditRide."");
   $r = mysql_fetch_array($q);
   $propos = explode("@", $r['proposals']);
   
   $year = substr($r['date'], 0, 4);
   $month = 0 + substr($r['date'], 5, 2);
   $day = substr($r['date'], 8, 2);
    

?>
   <h2>Modifier l'évènement du 
<?php 
   echo $day." ".$FrenchMonthes[$month]." ".$year."<br>";
?> 
   </h2>
   <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
   <input type="hidden" name="id_sql" value="<?php echo $EditRide; ?>">
   <table width="500">
   <tr><th>Sortie:</th></tr>
   <tr> <td width="150">Jour</td>
   <td width="*">   
<?php
      // Jour 
      $s="<SELECT NAME=\"day\" \n"; 
      $sclt=""; 
      for ($i=1; $i<=31; $i++) 
      { 
         $i2="0$i"; 
         $i2=substr($i2, -2); 
         if ($i2==$day)  $sclt=" SELECTED"; 
         else  $sclt=""; 
         $s.="<OPTION VALUE=\"$i2\"$sclt>$i</OPTION>\n";
      } 
      $s.="</SELECT> ";
      echo $s; 

   // Mois 
   $s="<SELECT NAME=\"month\"> \n";
   $sclt=""; 
   for ($i=1; $i<=12; $i++) 
   { 
      $i2="0$i"; 
      $i2=substr($i2, -2); 
      //echo $i2."   ".$CurrentMonth."<br>";
      if ($i2==$month)  $sclt=" SELECTED"; 
      else  $sclt=""; 
      $s.="<OPTION VALUE=\"$i2\"$sclt>$FrenchMonthes[$i]</OPTION>\n";
   } 
   $s.="</SELECT> ";
   echo $s;

   // Année 
   $s="<SELECT NAME=\"year\"> \n"; 
   //$s.="<OPTION VALUE=$dy>$dy</OPTION>\n";
   $sclt=""; 
   for ($i=2009;$i<=2015;$i++) 
   { 
      if ($i==$year)  $sclt=" SELECTED"; 
      else  $sclt=""; 
      $s.="<OPTION VALUE=\"$i\"$sclt>$i</OPTION>\n";
   } 
   $s.="</SELECT>";
   echo $s;
?>
   </td>

   <tr><td>Titre</td>
<?php
   echo "<td><input name=\"title\" type=\"text\" value=\"".
      $r['title']."\"></td>";
?>
   </tr>
   <tr><th>Propositions:</th></tr>
<?php
   
   for ($i=0;$i<=5;$i++) 
   { 
      echo "<tr>";
      $prop = "";
      if (array_key_exists($i, $propos))
      {
         $prop = $propos[$i];
      }
      echo "<td><input name=proposals[] type=\"text\" 
            value=\"".$prop."\"></td>";
   }
   
?> 
   </tr>
   <tr>
   <td colspan=2>
   Doit-on ajouter les proposition par défaut à cette sortie ?
   <input type="checkbox" <?php if ($r['add_default'] == true) echo "checked"; ?> name="AddDefault">
   </td>
   </tr></table>    
   <br>
   <input type="submit" name="modifier_sql" value="Modifier">
   <input type="submit" name="annuler" value="Annuler">
  
</form> 
<?php
   $DisplayList = 0;   
}


/********************************/ 
/*                              */ 
/*  Modif proposition default   */
/*                              */ 
/********************************/
if (isset($_POST['defaultridechange']))
{
   $query="DELETE FROM `vttcanalriders`.`ridecal_default`";
      if ($debug == 1) echo $query.'<br/>';
      $result = mysql_query($query);
      if (!$result) 
      {
         $message  = 'Requète invalide : ' . mysql_error() . "\n";
         $message .= 'Requète complète : ' . $query;
         die($message);
      }

   $default1 = trim($_REQUEST['default1']);
   $default2 = trim($_REQUEST['default2']);
   $default3 = trim($_REQUEST['default3']);
   
   if (strlen($default1))
   {
      $query="INSERT INTO ridecal_default (proposal)
         VALUES(\"".$_POST['default1']."\")";
      if ($debug == 1) echo $query.'<br/>';
      $result = mysql_query($query);
      if (!$result) 
      {
         $message  = 'Requète invalide : ' . mysql_error() . "\n";
         $message .= 'Requète complète : ' . $query;
         die($message);
      }
   }
      
   if (strlen($default2))
   {
      $query="INSERT INTO ridecal_default (proposal)
         VALUES(\"".$_POST['default2']."\")";
      if ($debug == 1) echo $query.'<br/>';
      $result = mysql_query($query);
      if (!$result) 
      {
         $message  = 'Requète invalide : ' . mysql_error() . "\n";
         $message .= 'Requète complète : ' . $query;
         die($message);
      }
   }
      
   if (strlen($default3))
   {
      $query="INSERT INTO ridecal_default (proposal)
         VALUES(\"".$_POST['default3']."\")";
      if ($debug == 1) echo $query.'<br/>';
      $result = mysql_query($query);
      if (!$result) 
      {
         $message  = 'Requète invalide : ' . mysql_error() . "\n";
         $message .= 'Requète complète : ' . $query;
         die($message);
      }
   }
}


/********************************/ 
/*                              */ 
/*     formulaire d'ajout       */
/*                              */ 
/********************************/
if (isset($_POST['ajouter']))
{
?> 
   <h3>Ajouter une nouvelle sortie</h3>
   
   <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
   
   <table width="500">
   <tr><th>Sortie</th></tr>
   <tr> <td width="150">Jour</td>
   <td width="*">   
<?php
      // Jour 
      $s="<SELECT NAME=\"day\" \n"; 
      $sclt=""; 
      for ($i=1;$i<=31;$i++) 
      { 
         $i2="0$i"; 
         $i2=substr($i2, -2); 
         if ($i2==$CurrentDay)  $sclt=" SELECTED"; 
         else  $sclt=""; 
         $s.="<OPTION VALUE=\"$i2\"$sclt>$i</OPTION>\n";
      } 
      $s.="</SELECT> ";
      echo $s; 

   
   // Mois 
   $s="<SELECT NAME=\"month\"> \n";
   $sclt=""; 
   for ($i=1;$i<=12;$i++) 
   { 
      $i2="0$i"; 
      $i2=substr($i2, -2); 
      //echo $i2."   ".$CurrentMonth."<br>";
      if ($i2==$CurrentMonth)  $sclt=" SELECTED"; 
      else  $sclt=""; 
      $s.="<OPTION VALUE=\"$i2\"$sclt>$FrenchMonthes[$i]</OPTION>\n";
   } 
   $s.="</SELECT> ";
   echo $s;

   // Année 
   $s="<SELECT NAME=\"year\"> \n"; 
   $sclt=""; 
   for ($i=$CurrentYear;$i<=$CurrentYear+3;$i++) 
   { 
      if ($i==$CurrentYear)  $sclt=" SELECTED"; 
      else  $sclt=""; 
      $s.="<OPTION VALUE=\"$i\"$sclt>$i</OPTION>\n";
   } 
   $s.="</SELECT>";
   echo $s;
?>
   </td>

   <tr><td>Titre</td>
   <td><input name="title" type="text" size="60"></td>
   </tr>
   <tr><td> </td></tr>
   <tr><th>Propositions:</th></tr>
<?php
   for ($i=1;$i<=5;$i++) 
   { 
      ?><tr><td><input name=proposals[] type="text" size="20" maxlength=""20"""></td></tr> <?php
   }
?> 
   </tr>
   <tr>
   <td colspan=2>
   Doit-on ajouter les proposition par défaut à cette sortie ?
   <input type="checkbox" checked name="AddDefault">
   </td>
   </tr></table>    
   <br>
   <input type="submit" name="ajouter_sql" value="Ajouter">
   <input type="submit" name="annuler" value="Annuler">

</form> 
<?php
   $DisplayList = 0;
}

/**********************************/ 
/*                                */ 
/* Lecture des enregistrements    */
/*                                */ 
/**********************************/
if ($DisplayList == 1) 
{
   $IsRides = 1;
   
   $q = mysql_query("SELECT * FROM ridecal_events ORDER BY date desc");
   if (mysql_errno($connection))
   {   
      $IsRides = 0;  
   }
   else
   {
   
      echo "<h3>Voici la liste des prochaines sorties :</h3>";
   ?> 
      <table class="ridetable" width=680>
   
     <tr><th width="220">Date</th>
     <th width="480">Sortie</th>
     <th></th><th></th>
     </tr>
      
     <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">  
  
  <?php 
     $idEvents = array();
      while ($r = mysql_fetch_array($q)) 
      {   
         $year = substr($r['date'], 0, 4);
         $month = 0 + substr($r['date'], 5, 2);
         $day = substr($r['date'], 8, 2);
         $title = $r['title'];
         $comment = $r['comment'];
         $id = $r['id_events'];
         $idEvents[] = $id; // store ids to have input form
   ?>      
      
   <?php
         echo "<tr align=\"left\">";
         echo "<td>".$day." ".$FrenchMonthes[$month]." ".$year;
         echo "</td>";
         echo "<td>".$title;
         //echo '</td><td>';
         echo '<td align="center">';
         echo '<input type="image" src="ridecalendar/images/b_edit.png" name="EditListId'.$id.'">';
         echo '</td>';
           
   
         echo '<td align="center">';
         echo '<input type="image" src="ridecalendar/images/b_drop.png" name="DeleteListId'.$id.'">'; 
         echo '</td>';
         
         echo '</td>';
         echo "</tr>";
      }
      
  
      echo "</table>";
      }
     
     $eventString = implode(";", $idEvents);
     echo '<input type="hidden" name="EventIdList" 
        value='.$eventString.'>';
  ?>    <br>

   
   <input align="middle" type="submit" name="ajouter" value="Ajouter une sortie">
   
   <br>
   <br>
   <br>
   <br>
   <h3>Sortie par défaut:</h3>
   Ces propositions seront soit ajoutées aux sorties, soit utilisées pour créer
   une sortie si aucune n'a été définie pour le prochain dimanche.
   <br>
  
   <?php
   
   $defaults =& GetDefaultProposals($connection);
    
   echo '<table>';
   echo "<tr><td>Proposition 1</td>";
   echo '<td><input name="default1" type="text" size="60" value="'.$defaults[0].'"></td></tr>';
   echo '<tr><td>Proposition 2</td>';
   echo '<td><input name="default2" type="text" size="60" value="'.$defaults[1].'"></td></tr>';
   echo '<tr><td>Proposition 3</td>';
   echo '<td><input name="default3" type="text" size="60" value="'.$defaults[2].'"></td></tr>';
   ?>
   </table>
   
   <input align="middle" type="submit" name="defaultridechange" value="Modifier !">
   
   </form>
   
   <br><br>
   <img style="border-width:0" src="ridecalendar/images/b_edit.png">
   : Modifier une sortie <br>
   <img style="border-width:0" src="ridecalendar/images/b_drop.png">
   : Supprimer une sortie
<?php

PrintVersion();

}

?>