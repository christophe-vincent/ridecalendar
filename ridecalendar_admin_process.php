<?php
global $pdo;
require "ridecalendar_database.php";


/********************************/ 
/*                              */ 
/*  Modif proposition default   */
/*                              */ 
/********************************/
if (isset($_POST['defaultridechange']))
{
    WriteDefaultProposals($_REQUEST['val']);

    $defPropos =& GetDefaultProposals();
    print_r("<br> propositions : ".$defPropos);
}


//header('Status: 301 Moved Permanently', false, 301);      
//header('Location: /?page_id=59');      
//exit();
?>