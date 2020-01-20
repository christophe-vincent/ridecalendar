<?php

// Database connection
global $debug;
global $databasesrv;
global $databaselogin;
global $dbpwd;

$debug = 0;

include 'config.php';

if ($debug == 1) "POST : ".print_r($_POST)."<br>";

//$connection = mysql_connect("mysql51-57.perso","vttcanalriders","ctciODpb");

$connection = mysql_connect($databasesrv, $databaselogin, $dbpwd);
if ( ! $connection ) 
die ("Problèmes de connexion à la base de données"); 


function TableCreation()
{
   echo "Creationde la table...<br>";
   $query = "CREATE TABLE `vttcanalriders`.`ridecal_proposals` (
   `id_proposal` INT NOT NULL AUTO_INCREMENT,
   `id_events` INT NOT NULL ,
   `name` TEXT NOT NULL ,
   `proposal` TEXT NOT NULL ,
   `def_proposal` TEXT NOT NULL ,
   `comment` TEXT NOT NULL ,
   PRIMARY KEY ( `id_proposal` )
   ) ENGINE = MYISAM";
   $result = mysql_query($query);
   if (!$result) 
   {
      $message  = 'Requête invalide : ' . mysql_error() . "\n";
      $message .= 'Requête complète : ' . $query;
      die($message);
   }    
   
   $query = "CREATE TABLE `vttcanalriders`.`ridecal_events` (
   `id_events` INT NOT NULL AUTO_INCREMENT,
   `date` DATE NOT NULL ,
   `title` TEXT NOT NULL ,
   `comment` TEXT NOT NULL ,
   `proposals` TEXT NOT NULL ,
   `add_default` BOOLEAN NOT NULL, 
   PRIMARY KEY ( `id_events` )
   ) ENGINE = MYISAM" ;
   $result = mysql_query($query);
   if (!$result) 
   {
      $message  = 'Requête invalide : ' . mysql_error() . "\n";
      $message .= 'Requête complète : ' . $query;
      die($message);
   }
   
   $query = "CREATE TABLE `vttcanalriders`.`ridecal_default` (
   `id_default` INT NOT NULL AUTO_INCREMENT,
   `proposal` TEXT NOT NULL ,
   PRIMARY KEY ( `id_default` )
   ) ENGINE = MYISAM" ;
   $result = mysql_query($query);
   if (!$result) 
   {
      $message  = 'Requête invalide : ' . mysql_error() . "\n";
      $message .= 'Requête complète : ' . $query;
      die($message);
   }
   
   $query = "CREATE TABLE `vttcanalriders`.`ridecal_comments` (
   `id_comment` INT NOT NULL AUTO_INCREMENT,
   `id_events` INT NOT NULL ,
   `date` DATETIME NOT NULL ,
   `pseudo` TEXT NOT NULL ,
   `comment` TEXT NOT NULL ,
   PRIMARY KEY ( `id_comment` )
   ) ENGINE = MYISAM" ;
   $result = mysql_query($query);
   if (!$result) 
   {
      $message  = 'Requête invalide : ' . mysql_error() . "\n";
      $message .= 'Requête complète : ' . $query;
      die($message);
   }
   
}


function &GetDefaultProposals()
{
   global $connection;
   $defaults = array("", "", "");
   $q = mysql_query("SELECT * FROM ridecal_default");
   if (!mysql_errno($connection))
   {  
      if ($r = mysql_fetch_array($q))
      {
         $defaults[0] = $r['proposal'];
      }
      if ($r = mysql_fetch_array($q))
      {
         $defaults[1] = $r['proposal'];
      }
      if ($r = mysql_fetch_array($q))
      {
         $defaults[2] = $r['proposal'];
      }
   }
   return $defaults;
}

function &GetNextRide()
{
   global $connection;
   global $FrenchMonthes, $Local, $debug;
   global $isRiders;
   $CurrentYear = date("Y");
   $CurrentMonth = date("m");
   $CurrentDay = date("d");
   
   // Get in DB the next ride
   $today = $CurrentYear."-".$CurrentMonth."-".$CurrentDay;
   $nextsunday = GetNextSunday(13);
   
   // If this line is used, the next ride is used, even if it is in few years !
   //$sql = "SELECT * FROM ridecal_events WHERE date >= '".$today."' ORDER BY date";
   
   // when this line is used, a ride is created for next sunday
   $sql = "SELECT * FROM ridecal_events WHERE date = '".$nextsunday."'";
   if ($debug == 1) echo $sql.'<br/>';
   $q = mysql_query($sql);
   if (mysql_errno($connection))
   {   
      //TableCreation();
      echo "Connection error...<br>";  
   }
   
   if (!mysql_errno($connection))
   {   
      $r = mysql_fetch_array($q);
      if ($r)
      {     
         $isRiders = 1;       
      }
      else
      {
         // no scheduled ride, create a default one
         $r =& CreateDefault();
      } 
   }
   else
   {
      $DisplayList = 0;
      echo "Pas de sortie programmée...";
   }
   
   return $r;
}

function GetNextSunday($hour)
{
   $CurrentYear = date("Y");
   $CurrentMonth = date("m");
   $CurrentDay = date("d");
   
   // get the date of the next sunday
   $stamp = mktime(0, 0, 0, $CurrentMonth, $CurrentDay, $CurrentYear);
   
   if (!((date("w") == 0) && (date('H') < $hour)))
   {
      $stamp += (7- date("w"))*86400;
   }
   
   $CurrentYear = date("Y", $stamp);
   $CurrentMonth = 0 + date("m", $stamp);
   $CurrentDay = date("d", $stamp);
   
   return $CurrentYear."-".$CurrentMonth."-".$CurrentDay;
}

function &CreateDefault()
{
   global $connection, $FrenchMonthes, $Local, $debug;
   $CurrentYear = date("Y");
   $CurrentMonth = date("m");
   $CurrentDay = date("d");
   
   echo "mois: ".$FrenchMonthes[3];
   
   // get the date of the next sunday
   $stamp = mktime(0, 0, 0, $CurrentMonth, $CurrentDay, $CurrentYear) + (7-date("w"))*86400;
   
   $CurrentYear = date("Y", $stamp);
   $CurrentMonth = 0 + date("m", $stamp);
   $CurrentDay = date("d", $stamp);
   $title = "Sortie du ".$CurrentDay." ".$FrenchMonthes[$CurrentMonth]." ".$CurrentYear;
  
   
   $date = "\"".$CurrentYear."-".$CurrentMonth."-".$CurrentDay."\"";
   $query='INSERT INTO ridecal_events (date, title, proposals, add_default)
      VALUES('.$date.',"'.$title.'", "", "1")';
   if ($debug == 1) echo $query.'<br/>';
   $result = mysql_query($query);
   if (!$result) 
   {
      $message  = 'Requête invalide : ' . mysql_error() . "\n";
      $message .= 'Requête complète : ' . $query;
      die($message);
   }
   
   $today = $CurrentYear."-".$CurrentMonth."-".$CurrentDay;
   // now read the ride created to get correct parameters
   $sql = "SELECT * FROM ridecal_events WHERE date >= '".$today."' ORDER BY date";
   $q = mysql_query($sql);
   
   if (!mysql_errno($connection))
   {   
      $r = mysql_fetch_array($q);
   }
    
   return $r;
}

function DeleteOldRides()
{
   global $connection, $debug;
   $CurrentYear = date("Y");
   $CurrentMonth = date("m");
   $CurrentDay = date("d");
   
   // Get in DB the next ride
   $today = $CurrentYear."-".$CurrentMonth."-".$CurrentDay;
   
   // first read all events to delete associated comments and proposals
   $sql = "SELECT * FROM ridecal_events WHERE date < '".$today."'";
   if ($debug) echo $sql."<BR>";
   $q = mysql_query($sql);
   if (mysql_errno($connection))
   {  
      echo "Connection error";
      return;  
   }
   
   while ($r = mysql_fetch_array($q))
   {
      if ($r)
      {     
         $sql = "DELETE FROM ridecal_proposals WHERE id_events = '".$r['id_events']."'";
         if ($debug) echo $sql."<BR>";
         $q2 = mysql_query($sql);
                
         $sql = "DELETE FROM ridecal_comments WHERE id_events = '".$r['id_events']."'";
         if ($debug) echo $sql."<BR>";
         $q2 = mysql_query($sql);
      }
   }
   
   $sql = "DELETE FROM ridecal_events WHERE date < '".$today."'";
   if ($debug) echo $sql."<BR>";
   $q = mysql_query($sql);   
}

function DeleteEvent($eventId)
{
   global $debug;
   
   $sql = "DELETE FROM ridecal_proposals WHERE id_events = '".$eventId."'";
   if ($debug) echo $sql."<BR>";
   $q2 = mysql_query($sql);
                
   $sql = "DELETE FROM ridecal_comments WHERE id_events = '".$eventId."'";
   if ($debug) echo $sql."<BR>";
   $q2 = mysql_query($sql);
         
   $sql = "DELETE FROM ridecal_events WHERE id_events = '".$eventId."'";
   if ($debug) echo $sql."<BR>";
   $q = mysql_query($sql);   
}
?>
