<?php 
session_start();
include('../classes/global.php');  
if(!isset($_SESSION[$SESSION_PR.'UserIDAdmin']))
{
	header("location:index.php");
	exit();
}
$db=new Admin();
if(!empty($_REQUEST['id']))
{
	$db->getQuery("update ".$table_pre.EMAIL_OUTLOOKS." SET DateRead='".date("Y-m-d H:i:s")."',criteriastatus='3' where ID='".$_REQUEST['id']."' ");
	echo "success";
}
?>