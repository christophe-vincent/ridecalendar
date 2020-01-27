<link rel="stylesheet" href="ridecalendar/bootstrap-4.3.1-dist/css/bootstrap.min.css" />
<?php
global $pdo;
include "ridecalendar/ridecalendar_database.php";



function affiche_tableau()
{
   $defPropositions =& GetDefaultProposals();
   
   echo '<form id="mainform" action="'.$_SERVER['PHP_SELF'];
   echo ' method="post">';
   echo '<table class="table table-bordered table-dark">';
   echo '<tr><th class="emptycell" width="40"> </th>';
   echo '<th  class="emptycell" width="3"> </th>';
   foreach ($defPropositions as $key => $value) {
      if ($value) {
         echo '<th>'.$value.'</th>';
      }
      # code...
   }
   
   echo "<th>Commentaire</th> ";
   echo "</tr>";


   echo '</table>';
   echo '</form>';
};

?>