<?php
// Database connection
global $debug;
global $databasesrv;
global $databaselogin;
global $dbpwd;
global $pdo;
$debug = 1;

include 'config.php';

if ($debug == 1) "POST : ".print_r($_POST)."<br>";

global $db_host;
global $db_name;
global $db_user;
global $db_pass;
global $pdo;
$charset = 'utf8';

$dsn = "mysql:host=$db_host;dbname=$db_name;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];
try {
   echo "SET PDO";
   $pdo = new PDO($dsn, $db_user, $db_pass, $options);
   echo "connection done";
} catch (PDOException $e) {
     echo "Exception";
     die($e->getMessage());
}

function TableCreation()
{
   global $pdo;
   echo "Creation de la table...<br>";
   $query = "CREATE TABLE `ridecal_proposals` (
   `id_proposal` INT NOT NULL AUTO_INCREMENT,
   `id_events` INT NOT NULL ,
   `name` TEXT NOT NULL ,
   `proposal` TEXT NOT NULL ,
   `def_proposal` TEXT NOT NULL ,
   `comment` TEXT NOT NULL ,
   PRIMARY KEY ( `id_proposal` )
   )";
   try {
      $pdo->query($query);
   } catch (PDOException $e) {
      echo "Impossible de créer une table...";
   }    
   
   $query = "CREATE TABLE `ridecal_events` (
   `id_events` INT NOT NULL AUTO_INCREMENT,
   `date` DATE NOT NULL ,
   `title` TEXT NOT NULL ,
   `comment` TEXT NOT NULL ,
   `proposals` TEXT NOT NULL ,
   `add_default` BOOLEAN NOT NULL, 
   PRIMARY KEY ( `id_events` )
   )" ;
   try {
      $pdo->query($query);
   } catch (PDOException $e) {
      echo "Impossible de créer une table...";
   }
   
   $query = "CREATE TABLE `ridecal_default` (
   `id_default` INT NOT NULL AUTO_INCREMENT,
   `proposal` TEXT NOT NULL ,
   PRIMARY KEY ( `id_default` )
   )" ;
   try {
      $pdo->query($query);
   } catch (PDOException $e) {
      echo "Impossible de créer une table...";
   }
   
   $query = "CREATE TABLE `ridecal_comments` (
   `id_comment` INT NOT NULL AUTO_INCREMENT,
   `id_events` INT NOT NULL ,
   `date` DATETIME NOT NULL ,
   `pseudo` TEXT NOT NULL ,
   `comment` TEXT NOT NULL ,
   PRIMARY KEY ( `id_comment` )
   )" ;
   try {
      $pdo->query($query);
   } catch (PDOException $e) {
      echo "Impossible de créer une table...";
   }
   
}


function &GetDefaultProposals()
{
   global $pdo;
   try {
       $q = $pdo->query("SELECT * FROM ridecal_default");
   } catch (PDOException $e) {
      TableCreation();
      die("Initialization de la database effectuée");       
   }
     
   $r = $q->fetch();
   
   $defaults = explode('@', $r['proposal']);
   echo "<br>";
   print_r($defaults);
   return $defaults;
}

function WriteDefaultProposals($values)
{
    global $pdo;
    $value = implode("@", $values);
    echo "write<br>";
    
    $query = "DELETE FROM `ridecal_default`";
    try {
        $pdo->query($query);
    } catch (PDOException $e) {
        echo "Erreur base de données...";
        die();
    }

    $query = "INSERT INTO ridecal_default (proposal) VALUES ";
    $query .= "('". $value ."');";
    try {
        echo "Query : ".$query;
        $pdo->query($query);
    } catch (Exception $e) {
        echo "Erreur base de données...";
        die();
    }
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
