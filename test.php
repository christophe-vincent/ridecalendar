<?php
include 'ridecalendar_database.php';

function getDateW()
{
   return 6;
}

function GetNextSunday2($hour)
{
   $CurrentYear = date("Y");
   $CurrentMonth = date("m");
   $CurrentDay = date("d");
   
   $CurrentDay = 26;
   
   $stamp = mktime(0, 0, 0, $CurrentMonth, $CurrentDay, $CurrentYear);
   // get the date of the next sunday
   echo date('H')."<br>";
   
   if (!((getDateW() == 0) && (date('H') < $hour)))
   {
   echo ("OK   ");
      //$stamp += (7-date("w"))*86400;
      $stamp += (7- getDateW())*86400;
   }
   
   $CurrentYear = date("Y", $stamp);
   $CurrentMonth = 0 + date("m", $stamp);
   $CurrentDay = date("d", $stamp);
   
   return $CurrentYear."-".$CurrentMonth."-".$CurrentDay;
}

$nextsunday = '';
$nextsunday = GetNextSunday2(23);

echo $nextsunday;

?>