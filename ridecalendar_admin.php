<link rel="stylesheet" href="ridecalendar/bootstrap-4.3.1-dist/css/bootstrap.min.css" />
<?php 

global $debug;
global $connection;
global $process_page;
global $pdo;

include "ridecalendar/ridecalendar_database.php";
$process_page = "ridecalendar/ridecalendar_admin_process.php";

//print_r($_POST);


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


function affiche_gestion()
{
   global $process_page;

   $defaults =& GetDefaultProposals();
   
   echo "Choix possibles:";
   echo '<form id="mainform" action="'.$process_page;
   echo '" method="post">';
   
   for ($i = 1; $i <= 5; $i++)
   {
      echo '<div class="form-group">';
      echo "$i";
      echo '<input name="val[]" type="text">';
      echo '</div>';
   }
   echo '<input align="middle" type="submit" name="defaultridechange" value="Modifier !">';
   echo '</form>';
};


?>