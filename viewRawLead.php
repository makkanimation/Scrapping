<?
session_start();
include('../classes/global.php');
$ld=new Lead();
//get lead ID
$LUID=$_REQUEST['ID'];
$MailType=$_REQUEST['MailType'];  //for reminder
?>
<html>
<head>
<title><?=$BrdPageTitle;?></title>
<? include($root.'include/common.php'); ?>
</head>
<body>
<DIV id="TipLayer" style="visibility:hidden;position:absolute;z-index:1000;top:-100"></DIV>
<table width='100%' border='0' align='center'  cellpadding="0" cellspacing='1'>
<?
//---------Get Lead------>
$lead=$ld->getRawLead($LUID);
if(count($lead)>0)
{	
?>
	<tr>
    	<td valign="middle" height="50" class=""><b>Email From</b>: <?php echo $lead[0]['EmailFrom'];?></td>
    </tr>
	<tr>
    	<td valign="middle" height="50" class=""><b>Subject</b>: <?php echo $lead[0]['Subject'];?></td>
    </tr>
	<tr>
    	<td valign="middle" height="50" class=""><b>Recieved Date</b>: <?php echo $lead[0]['DateE'];?></td>
    </tr>
	<tr>
    	<td valign="middle" height="50" class=""><b>SendType</b>: <?php echo strtoupper($lead[0]['byPassMDB'])=="YES" ? "AutoStitched" : "Manual" ?></td>
    </tr>
	<tr>
		<td>
<?php 		
	if(!empty($lead[0]['Message_html']))
	{
		echo $lead[0]['Message_html'];
	}
	else{
		echo $lead[0]['Message'];
	}
?>    
    </td>
    </tr>
<?php 
}
?>
    <tr>
    	<td width="2%" align="center" valign="middle" height="50">
    	<a href="javascript:window.close();" class="hyperlink" >[Close Me]</a></td>
    </tr>
</table>
</body>
</html>