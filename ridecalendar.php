<link rel="stylesheet" type="text/css" href="ridecalendar.css" />

<script>

function checkQuestion()
{
   var resp = parseInt(document.getElementById("humanResponse").value);
   if (resp != "")
   {
      document.getElementById("submit_add_name").innerHTML='<input type="hidden" name="add_name" value="">';
      document.getElementById("mainform").submit();
   }
   else
   {
      document.getElementById("humanResponse").value='';
      document.getElementById("humanInfo").innerHTML="Mot de passe s'il vous plait !";
   }
}
</script>
<?php
global $pwd;
$pwd = "vttcr";

global $debug;
global $FrenchMonthes;

$FrenchMonthes = array("", "Janvier", "Février", "Mars", "Avril", "Mai",
                       "Juin", "Juillet", "Aout", "Septembre",
                       "Octobre", "Novembre", "Décembre");

$DisplayList = 1;
$ChangeResponse = 0;
$UserId = 0;
global $connection;

include 'ridecalendar_database.php';
include 'version.php';

function checkPassword()
{
   global $pwd;
   if ($_POST['humanResponse'] != $pwd)
   {
      ?>
      Pas de mot de passe, pas d'inscription !
      <form>
      <input type="submit" name="cancel" value="retour">
      </form>
      <?php
      die ("");
   }
}

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
   echo "Error selecting database: ".mysql_error()."<br>";
   TableCreation();
}

DeleteOldRides();

/********************************/ 
/*                                                  */ 
/*    AJOUTER MODIFIER SUPP  */
/*                                                  */ 
/********************************/

if ((isset($_POST['add_name'])) && (isset($_POST['name'])))
{
   if ($_POST['name'] == "")
   {
      ?>
      Merci vous identifier...
      <form>
      <input type="submit" name="cancel" value="retour">
      </form>
      <?php
      die ("");
   }
   
   checkPassword();
   
   // add the responses
   $nbResp = 0;
   $propos = "";
   if (isset($_POST['nbResponses']))
      $nbResp = $_POST['nbResponses'];
   for ($i=0; $i<$nbResp; $i++)
   {
      if (isset($_POST['etat']))
      {
         if (in_array($i, $_POST['etat']))
            $responses[$i] = 1;
         else 
            $responses[$i] = 2;
      }
      else $responses[$i] = 2; 
   }
   if ($nbResp > 0)
      $propos = implode("@", $responses);
   
   $nbDefResp = 0;
   if (isset($_POST['nbDefResponses']))
      $nbDefResp = $_POST['nbDefResponses'];
   
   $def_propos = "";
   for ($i=0; $i<$nbDefResp; $i++)
   {
      if (isset($_POST['def_etat']))
      {
         if (in_array($i, $_POST['def_etat']))
            $responses[$i] = 1;
         else 
            $responses[$i] = 2;
      }
      else $responses[$i] = 2; 
   }
   if ($nbDefResp > 0)
      $def_propos = implode("@", $responses);
   
   if ($nbDefResp + $nbResp == 0) die("No default proposals detected...");
   
   $query='INSERT INTO ridecal_proposals (name, id_events, proposal, def_proposal, comment)
      VALUES("'.$_POST['name'].'",'.$_POST['id_event'].',"'
      .$propos.'","'.$def_propos.'","'.$_POST['comment'].'")';
   
  
   if ($debug) echo $query.'<br/>';
   $result = mysql_query($query);
   if (!$result) 
   {
      $message  = 'Requête invalide : ' . mysql_error() . "\n";
      $message .= 'Requête complète : ' . $query;
      die($message);
   }
}

if (isset($_POST['change_name'])) 
{
   checkPassword();
   
   // change a response
   $nbResp = 0;
   for ($i=0; $i<$_POST['nbResponses']; $i++)
   {
      if(isset($_POST['etat']))
      {
         if (in_array($i, $_POST['etat']))
            $responses[$i] = 1;
         else 
            $responses[$i] = 2;
      }
      else
         $responses[$i] = 2;
   }
   if (isset($responses))
      $propos = implode("@", $responses);
   
   $nbDefResp = 0;
   if (isset($_POST['nbDefResponses']))
      $nbDefResp = $_POST['nbDefResponses'];
   
   $def_propos = "";
   for ($i=0; $i<$nbDefResp; $i++)
   {
      if (isset($_POST['def_etat']))
      {
         if (in_array($i, $_POST['def_etat']))
            $responses[$i] = 1;
         else 
            $responses[$i] = 2;
      }
      else $responses[$i] = 2; 
   }
   if ($nbDefResp > 0)
      $def_propos = implode("@", $responses);
   
   if ($nbDefResp + $nbResp == 0) die("No default proposals detected...");

   
   $query='UPDATE ridecal_proposals SET ';
   if (isset($propos)) $query .= 'proposal="'.$propos.'", ';
    $query.= 'def_proposal="'.$def_propos.'",
      comment="'.$_POST['comment'].'"
      WHERE id_proposal='. $_POST['id_proposal'];
   
   if ($debug == 1) echo $query.'<br/>';
   $result = mysql_query($query);
   if (!$result) 
   {
      $message  = 'Requête invalide : ' . mysql_error() . "\n";
      $message .= 'Requête complète : ' . $query;
      die($message);
   }
   $ChangeResponse = 0;
   $UserId = 0;
   
   //mysql_free_result($result); // WARNING ???
   //echo 'Modification effectuée <br /><br />';
}


if (isset($_POST['cancel'])) 
{
   //echo "CANCEL";
   $ChangeResponse = 0;
   $UserId = 0;
}

/********************************/ 
/*                                                  */ 
/* formulaire de modification   */
/*                                                   */ 
/********************************/
if (isset($_GET['change']))
{
   if ($_GET['change'] == 'ok') 
   {
      //echo "Wil change something !".$_GET['nameIndex'];
      $ChangeResponse = 1;
      $UserId = $_GET['nameIndex'];
   }
}


/**********************************/ 
/*                                                     */ 
/* Lecture des enregistrements*/
/*                                                     */ 
/**********************************/
if ($DisplayList == 1) 
{
   $r =& GetNextRide();
   $defPropos = array();
   if ($r['add_default'] == true)
   {
      $defPropos =& GetDefaultProposals();
   }
   $nbResponses = 0;
   $nbDefResponses = 0;
   $nameIndex = 0;
   
   $year = substr($r['date'], 0, 4);
   $month = 0 + substr($r['date'], 5, 2);
   $day = substr($r['date'], 8, 2);
   $propos = explode("@", $r['proposals']);

   // display the date (center is added when used with joomla)
   echo '<div class="rc_date"><center>'.$day.
      ' '.$FrenchMonthes[$month].' '.$year.'</center></div>';
   
   // display the title
   echo '<div class="rc_title"><center>'.$r['title'].'</center></div>'; 
   
   // get list of registered persons
   $query = "SELECT * FROM ridecal_proposals  
            WHERE id_events = ".$r['id_events'];
   $q = mysql_query($query);
   if (mysql_errno($connection))
   {
      //echo "Error ".mysql_error();
      $isRiders = 0;   
   }  
}      
   
if ($DisplayList == 1) 
{

   //display table
?>

   <form id="mainform" action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
   <table class="ridetable" width=700>
   <tr><th class="emptycell" width="40"> </th> 
   <th  class="emptycell" width="3"> </th>
   
<?php
   // Loop on proposals
   $nbPropos = count($propos);
   for ($i=0; $i<$nbPropos; $i++)
   {
      $total[$i] = 0;
      if ($propos[$i] != "")  
      {
         echo "<th>".$propos[$i]."</th>";
         $nbResponses++;
       }
   }
   for ($i=0; $i<count($defPropos); $i++)
   {
      $def_total[$i] = 0;
      if ($defPropos[$i] != "")
      {
         echo "<th>".$defPropos[$i]."</th>";
         $nbDefResponses++;
      }
   }
   echo "<th>Commentaire</th> ";
   echo "</tr>";

   // loop on each person that has fill the query
   while ($resp = mysql_fetch_array($q)) 
   {
      $responses = explode("@", $resp['proposal']);
      $def_responses = explode("@", $resp['def_proposal']);
      echo "<tr>";
      echo "<th class=\"name\">".$resp['name']."</th>";

      // display icon to change a response
      echo "<td align=\"center\"><a title=\"Modifier les choix\" href=\""
         .$_SERVER['PHP_SELF'].
         "?change=ok&nameIndex=".$resp['id_proposal']."\">
         <img style=\"border-width:0\" src=\"ridecalendar/images/b_edit.png\"></a></td>";

   	//proposals associated to the event
      
      for ($i=0; $i<$nbPropos; $i++)
      {  // display color depending on response
         if ($propos[$i] != "")
         {	    
             if (($ChangeResponse==0) || ($UserId != $resp['id_proposal']))
   	       {
                // display colors
                if (array_key_exists($i, $responses))
                {
      	          if ($responses[$i] == 1)
      	          { // Ok
                      echo "<td style=\"background-color:#3AFF3A\"> </td>";
      	             $total[$i] ++;
      	          }
      	          else if ($responses[$i] == 2)
      	          { // No
                       echo "<td style=\"background-color:#FF3A3A\"> </td>";
      	          }
      	          else 
      	          { // No response...
      	             echo "<td style=\"background-color:#808080\"> </td>";
      	          }
                }
                else
                { // No response...
      	           echo "<td style=\"background-color:#808080\"> </td>";
      	       }
   	       }
   	       else
   	       {
                // display checkboxes
   	          if ($responses[$i] == 1)
   	          { // Ok
   	             $check = "checked";
   	          }
   	          else $check =" ";
   	          
                echo "<td align=\"center\"><input name=\"etat[]\" 
                         type=\"checkbox\"value=\"".$i."\"".$check.">";
                echo "</td>";
   	       }
          }   
   	 }
      
      // default proposals
      for ($i=0; $i<count($defPropos); $i++)
   	{  // display color depending on response
         if ($defPropos[$i] != "")
         {    
             if (($ChangeResponse==0) || ($UserId != $resp['id_proposal']))
   	       {
                // display colors
                if (array_key_exists($i, $def_responses))
                {
      	          if ($def_responses[$i] == 1)
      	          { // Ok
                      echo "<td style=\"background-color:#3AFF3A\"> </td>";
                      $def_total[$i]++;
      	          }
      	          else if ($def_responses[$i] == 2)
      	          { // No
                     echo "<td style=\"background-color:#FF3A3A\"> </td>";
      	          }
      	          else 
      	          { // No response...
      	             echo "<td style=\"background-color:#808080\"> </td>";
      	          }
                }
                else
                { // No response...
      	           echo "<td style=\"background-color:#808080\"> </td>";
      	       }
   	       }
   	       else
   	       {
                // display checkboxes
   	          if ($def_responses[$i] == 1)
   	          { // Ok
   	             $check = "checked";
   	          }
   	          else $check =" ";
   	          echo "<td align=\"center\"><input name=\"def_etat[]\" 
                         type=\"checkbox\"value=\"".$i."\"".$check.">";
               echo "</td>";
   	       }
          }   
   	 } 
   	// display comment
   	if ($UserId != $resp['id_proposal'])
   	   echo "<td align=\"left\">".$resp['comment']."</td";
   	else
   	    echo '<td align="left"><input name="comment" type="text" value="'.
   	     $resp['comment'].'" size="20"></td>';
   	echo "</tr>";
   	$nameIndex++;
   }

   if ($ChangeResponse == 0)
   {   
      echo '<tr><td colspan="2">';
      echo '<input name="name" type="text" size="20"></td>';
   
      // display checkboxes
      for ($i=0; $i<$nbPropos; $i++)
      {
         if ($propos[$i] != "")
         {
            echo "<td align=\"center\"><input name=\"etat[]\" 
               type=\"checkbox\" value=\"".$i."\">";
            echo "</td>";
         }
      }
      for ($i=0; $i<count($defPropos); $i++)
   	{  // display color depending on response
         if ($defPropos[$i] != "")
         { 
            echo "<td align=\"center\"><input name=\"def_etat[]\" 
                type=\"checkbox\" value=\"".$i."\">";
            echo "</td>";
         }
      }
      echo '<td align="left"><input name="comment" type="text" size="25"></td>';
   }
?>
  </tr>
   
   <tr>
   <td colspan="2" align="right">Total</td>
<?php
   for ($i=0; $i<$nbPropos; $i++)
   {
      if ($propos[$i] != "")
      {
         echo "<td align=\"center\">";
         if ($total[$i] == "") echo "0";
         else echo $total[$i];
         echo "</td>";
      }
   }
   for ($i=0; $i<count($defPropos); $i++)
   {
      if ($defPropos[$i] != "")
      {
         echo "<td align=\"center\">";
         if ($def_total[$i] == "") echo "0";
         else echo $def_total[$i];
         echo "</td>";
      }
   }
?>
   </tr>
      
   </table>
   <br>
   
   <span id="humanQuestion">
   Mot de passe :   
   </span>
   
   <?php
      $upwd = "";
      if (isset($_GET['p']))
      {
         $upwd = $_GET['p'];
      }
   ?>
   
   <input type="text" name="humanResponse" id="humanResponse" value="<?php echo $upwd?>">
   <font color="red"><b><span id="humanInfo"></span></font></b>
   
   <input type="hidden" name="nbResponses" value="
      <?php echo $nbResponses; ?>">
   <input type="hidden" name="nbDefResponses" value="
      <?php echo $nbDefResponses; ?>">
   <input type="hidden" name="id_event" value="
      <?php echo $r['id_events']; ?>">
      <table width="700"><tr><td>
<?php    
    if ($ChangeResponse == 0)
   {
      echo '<span id="submit_add_name"> </span>';
      //echo '<input type="submit" name="add_name" value="valider">';
      echo '<input type="button" name="add_name" value="valider" onClick="checkQuestion()">';
      if ((isset($_POST['add_name'])) && (!isset($_POST['name'])))
      echo "<span style=\"color:red\">   L'identifiant est obligatoire</span>";
   }
   else
   {
      echo "<input type=\"hidden\" name=\"id_proposal\" value=\"
         ".$UserId."\">";
      echo '<input type="submit" name="change_name" value="Modifier">';
      echo '  <input type="submit" name="cancel" value="Annuler">';
   }
   
?> 
   </td><td>
   <div class="rc_modifychoice"><img style="border-width:0" src="ridecalendar/images/b_edit.png">
   : Modifier les choix</div>
   </td></tr></table>
   </form>
   <br>
<?php
   PrintVersion();
}

if ($DisplayList == 1) 
{
   include 'ridecalendar_comment.php';
}
?>

