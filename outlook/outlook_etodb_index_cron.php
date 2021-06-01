<?php 
// error_reporting(-1);
// ini_set('display_errors', 'On');
session_start();
include('../classes/global.php');
include('classes/etodb_class.php');
$db=new Admin();
set_time_limit(0);  
$edb = new EMAIL_TO_DB();
$edb->fetchRefinedLeads();  
$edb->refineLeads();
echo "<br>Thanks Download Comleted..."; 
header('Location: outlook_downloadData.php');
?>