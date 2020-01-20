<?php

function date_fr ($date)
{
   $date = str_replace("Monday ", "lundi", $date);
   $date = str_replace("Tueday", "mardi", $date);
   $date = str_replace("Wednesday", "mercredi", $date);
   $date = str_replace("Tuesday", "jeudi", $date);
   $date = str_replace("Friday", "vendredi", $date);
   $date = str_replace("Saturday", "samedi", $date);
   $date = str_replace("Sunday", "dimanche", $date);
   
   $date = str_replace("January", "janvier", $date);
   $date = str_replace("February", "février", $date);
   $date = str_replace("March", "mars", $date);
   $date = str_replace("April", "avril", $date);
   $date = str_replace("May", "mai", $date);
   $date = str_replace("June", "juin", $date);
   $date = str_replace("July", "juillet", $date);
   $date = str_replace("August", "août", $date);
   $date = str_replace("September", "septembre", $date);
   $date = str_replace("October", "octobre", $date);
   $date = str_replace("Novembre", "novembre", $date);
   $date = str_replace("Decembre", "décembre", $date);
   
   return $date;
}

if (!isset($_POST['new_comment'])) 
{
    ?>
    <p></p>
   <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
   <input type="submit" name="new_comment" value="Nouveau commentaire">
   </form>
   <?php
}

if (isset($_POST['record_comment']))
{
    $today = date("o")."-".date("m")."-".date("d")." ";
    $today .= date("H").":".date("i").":".date("s");
    $query='INSERT INTO ridecal_comments (id_events, date, pseudo, comment)
      VALUES("'.$r['id_events'].'","'.$today.'","'.$_POST['pseudo'].'","'.
      $_POST['comment_comment'].'")';
   
  
  // echo $query.'<br/>';
   $result = mysql_query($query);
   if (!$result) 
   {
      $message  = 'Requête invalide : ' . mysql_error() . "\n";
      $message .= 'Requête complète : ' . $query;
      die($message);
   }
   
}


// display here comments, if any
$sql = 'SELECT * FROM ridecal_comments WHERE id_events = "'.$r['id_events'].'" ORDER BY date';
//echo $sql."<br>";

$q = mysql_query($sql);
if (mysql_errno($connection))
{   
  echo "error in query...".mysql_error();  
}

$count = 0;
if (!mysql_errno($connection))
{
    while ($resp = mysql_fetch_array($q)) 
   {
       $count++;
       $timeep = strtotime($resp['date']);
       $cdate = strftime('le %A %d %B à %Hh%M', $timeep);
       
       echo '<p><table width=700 class="rcc_comment">';
       echo '<tr class="rcc_header"><td>';
       echo '<span class="rcc_pseudo">'.$resp['pseudo'].', </span>';
       echo '<span class="rcc_date">'.$cdate.'</span>';
       echo "</td></tr>";
       echo "<tr class=\"name\"><td>".$resp['comment']."</td></tr>";
       echo "</table></p>";
   }
}


if (isset($_POST['new_comment'])) 
{
   // echo 'Ajouter un commentaire';
   ?>
   <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
   Pseudo: <input type="text" name="pseudo"><br>
   Commentaire
   <br><textarea rows="5" cols="40" name="comment_comment"></textarea><br>
   <input type="submit" name="record_comment" value="Valider">
   <input type="submit" name="cancel_comment" value="Annuler">
   </form>
   <?php
}
else if ($count > 3)
{
   ?>
   <form action="<?php echo $_SERVER['PHP_SELF'];?>" method="post">
   <input type="submit" name="new_comment" value="Nouveau commentaire">
   </form>
   <?php
}
?>