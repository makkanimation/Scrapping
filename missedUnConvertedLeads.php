<?
session_start();
include('../classes/global.php');  
if(!isset($_SESSION[$SESSION_PR.'UserIDAdmin']))
{
	header("location:{$wwwroot}/index.php");
	exit();
}  
$db=new Admin();
/*******************paing start***********/
$start=$_REQUEST['start'];
$eu = ($start - 0);
$limit = 100; // No of records to be shown per page.
$ths = $eu + $limit;
$back = $eu - $limit;
$next = $eu + $limit;
$currenTpagenum = $next/$limit;

$day_before = date( 'Y-m-d', strtotime(date('Y-m-d') . ' -1 day' ) );

// echo "select * from ".$table_pre.EMAIL_OUTLOOKS." where criteriastatus NOT IN ('2','3') and left(DateE,10)<='{$day_before}' order by ID desc"; 
$result_All = $db->getQuery("select * from ".$table_pre.EMAIL_OUTLOOKS." where ((criteriastatus NOT IN ('2','3') and left(DateE,10)<='{$day_before}') OR criteriastatus='1') order by ID desc");
$numRows=$db->getNumRows($result_All);
$cond=" limit $eu,$limit";
$managePage = "missedUnConvertedLeads.php";
$result = $db->getQuery("select * from ".$table_pre.EMAIL_OUTLOOKS." where ((criteriastatus NOT IN ('2','3') and left(DateE,10)<='{$day_before}') OR criteriastatus='1') order by ID desc $cond");

/*******************paing End***********/
// include('include/common.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title><? echo TITLE; ?></title>
		<? include($root."include/common.php"); ?>
	</head>
	<body>
		<table width="100%" align="center"  border="0" cellspacing="1" cellpadding="1" bgcolor="#000000" >
			<tr bgcolor="#FFFFFF">
				<td colspan="3" align="left" valign="top" ><? include($root."include/admin_top.php");?></td>
			</tr>
			<tr style="height:auto" bgcolor="#FFFFFF">
				<td width="231" height="480px;" valign="top" style="border-right:#000000 1px solid;" ><? include_once ($root."/include/admin_leftmenu.php");?></td>
				<td width="100%"  height="450px;" align="center"  valign="top">
					<table width="100%" cellspacing="1" bgcolor="#343434">
						<tr bgcolor="#FFFFFF">
							<td align="center" valign="middle"><br /><br />
								<form name="exportForm" method="post" action="#" >		
									<table width="100%" align="center"   border="0"  cellspacing="1" cellpadding="4" class="table" bgcolor="#000000">
										<? if($numRows>0)
										{ ?>
											<tr bgcolor="#FFFFFF" height="35">
												<td colspan="12" align="left" valign="bottom">
													<?
													if($numRows>$limit)
													{	?>					
														<table align = 'center' width='50%' border='0' valign='top'>
															<tr>
																<td align='left' width='30%'>
																	<?	
																	if($back >=0) {
																		print "<a href='$managePage?start=$back' class='hyperlink'><font face='Verdana' >PREV</font></a>";
																	} ?>
																</td>
																<td align=center width='30%'>
																	<?
																	$i=0;
																	$l=1;
																	for($i=0;$i < $numRows;$i=$i+$limit){
																		if($i <> $eu){
																			echo " <a href='$managePage?start=$i' class='hyperlink'><font face='Verdana' >$l</font></a> ";
																		}
																		else { 
																			echo "<font face='Verdana' size='3' color=red >$l</font>";
																		} /// Current page is not displayed as link and given font color red
																		$l=$l+1;
																	} ?>
																</td>
																<td align='right' width='30%'>
																	<?
																	if($ths < $numRows) {
																		print "<a href='$managePage?start=$ths' class='hyperlink'><font face='Verdana' >NEXT</font></a>";
																	} ?>
																</td>
															</tr>
														</table>
												<?	} ?>
												</td>	  
											</tr>
											<tr bgcolor="#B4ACBB" class="whitelabel">
												<td align="center">All<br />
													<input type="checkbox" name="chkall" id='chkall' value="1" class="chechbox" onClick="CheckAllBox('chkall');">
												</td>
												<td align="center">Subject</td>
												<td align="center">Email From</td>
												<td align="center">Received Date</td>
												<td align="center">SendType</td>
												<td align="center">View Lead</td>
												<td align="center">Read </td>
											</tr>
											<? 		 
											if($result<>'')
											{
												$p=0;
												while($fetchRow=$db->getAssocArray($result))
												{
													$ID=$fetchRow['ID'];				  
												?>
													<tr bgcolor="#FFFFFF" height="35" style="display:'block';" id="showhide<?=$ID;?>">
														<td align="left" >
															<input type="checkbox" name="excelchk[]" value="<?=$ID;?>" class="chechbox" id='chkall'>
														</td>
														<td align="left" ><a href="#" onclick="javascript:exportdata(<?=$ID;?>,'CSVWithSubject');">
															<?=$fetchRow['Subject'];?> </a>
														</td>
														<td align="left" ><?=$fetchRow['EmailFrom'];?></td>
														<td align="left"><?=$fetchRow['DateE'];?></td>
														<td align="center"><?php echo strtoupper($fetchRow['byPassMDB'])=="YES" ? "AutoStitched" : "Manual" ?></td>
														<td align="center"><a href="javascript:sendMailPopupTemp('<?=$fetchRow['ID'];?>');" class="hyperlink">view</a></td>
														<td align="center" id="read_<?=$fetchRow['ID'];?>"><button type="button" onClick="return readfunction('<?=$fetchRow['ID'];?>');" class="btn btn-primary">Unread</button></td>
													</tr>
													<?
													$p++;
												} ?>
												<tr bgcolor="#FFFFFF" height="35">
													<td colspan="12" align="left" valign="top"><?
														if($numRows>$limit)
														{ ?>						
															<table align = 'center' width='50%' border='0'>
																<tr>
																	<td align='left' width='30%'>
																		<? if($back >=0) {
																			print "<a href='$managePage?start=$back' class='hyperlink'><font face='Verdana' >PREV</font></a>";
																		} ?>
																	</td>
																	<td align=center width='30%'> <?
																		$i=0;
																		$l=1;
																		for($i=0;$i < $numRows;$i=$i+$limit){
																			if($i <> $eu){
																				echo " <a href='$managePage?start=$i' class='hyperlink'><font face='Verdana' >$l</font></a> ";
																			}
																			else { 
																				echo "<font face='Verdana' size='3' color=red >$l</font>";
																			} /// Current page is not displayed as link and given font color red
																			$l=$l+1;
																		} ?>
																	</td>
																	<td align='right' width='30%'> <?
																		if($ths < $numRows) {
																			print "<a href='$managePage?start=$ths' class='hyperlink'><font face='Verdana' >NEXT</font></a>";
																		} ?>
																	</td>
																</tr>
															</table>
													<?	}?>
													</td>
												</tr>
										<? 	}
										}
										else
										{ ?>
											<tr bgcolor="#FFFFFF">
												<td height="300" colspan="12" align="center" class="label">!! All Data Is De - Activated !!<br /><!--Please <a href="outlook_activemail_action.php" class="hyperlink">Clik Hare</a> To Active For All ExportDate--></td>
											</tr>
									<?  } ?>
									</table>
								</form>
							</td>
						</tr>
					</table>
				</td>
			</tr>
			<tr>
				<td colspan="3" align="center" ><? include($root."include/bottom.php") ?></td>
			</tr>
		</table>
	</body>
</html>
<script language="javascript"> 
function sendMailPopupTemp(ID)
{
	var width=550;
	var height=550;
	window.open("viewRawLead.php?ID="+ID, "a", "toolbars=0, menubar=0, noresize, scrollbars=yes,width=" + width + ", height=" + height)
}

function readfunction(id)
{
	if(confirm("Do you realy want to mark this lead as read?"))
	{
		$.ajax({
		  method: "POST",
		  url: "readRawLead.php",
		  data: { id: id }
		}).done(function( msg ) {
			if(msg=="success")
			{
				$("#read_"+id).html("Read");
			}
			else{
				alert( "There is some problem. Please try again.");
				return false;
			}
		});
	}
}
function exportdata(ID,folder)
{
	window.location="outlook_downloadDataToCSV.php?ID="+ID+"&folder="+folder;
}
function exportdataInPDF(ID,folder)
{
	window.location="outlook_downloadDataToPDF.php?ID="+ID+"&folder="+folder;
}
</script>
<script language="javascript" type="text/javascript">
function MM_findObj(n, d)
{ //v4.01
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) {
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);}
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n];
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document);
  if(!x && d.getElementById) x=d.getElementById(n); return x;
}

function CheckAllBox(chk)
{
	var chk_bx = MM_findObj(chk);
    if(chk_bx[0].checked == true)
	{
		for(var i=1;i < chk_bx.length;i++)
		{		
			chk_bx[i].checked = true;
		}	

	}
	else
	{
		for(var i=1;i < chk_bx.length;i++)
		{		
			chk_bx[i].checked = false;
		}
	}

} 
function collectselected_export()
{  
	
	var MID='';
	var flg=1;
	var totalRecord = 0;   

 	for(var i=0;i<document.exportForm.elements.length;i++)
	{  
		if(document.exportForm.elements[i].type=='checkbox' && document.exportForm.elements[i].name=='excelchk[]' && document.exportForm.elements[i].checked==true)
		{
		  MID+=document.exportForm.elements[i].value+",";
		  flg=0;
		  totalRecord++;
		}
	}

	if(flg==1)
	{
	   alert("Please select atleast one record to export.");
	   return false;
	}
	else
	{
	  MID=MID.substring(0,MID.length-1); 
	   var col_array=MID.split(",");
	   var part_num=0;
		while (part_num < col_array.length)
		 {
		  var ID = col_array[part_num];
		  var a=document.getElementById('showhide'+ID);
 	      a.style.display='none';
		  part_num+=1;
		  }
	  /******************/
	 

		xmlhttp=GetXmlHttpObject();
		if (xmlhttp==null)
		  {
		  alert ("Browser does not support HTTP Request");
		  return;
		  } 
	  document.exportForm.action="export_all.php?totalRecord="+totalRecord;
	  xmlhttp.onreadystatechange=stateChanged;
	  xmlhttp.open("GET",url,true);
	  xmlhttp.send(null);
	  //window.location.reload();
	  return true;
	   parent.opener.location.reload();
	}   
}
function stateChanged()
{
//if (xmlhttp.readyState==1){
//document.getElementById("showpostedlead").innerHTML = "loading";
//}
if (xmlhttp.readyState==4)
{
//currently we have no need of response text.

//document.getElementById("showpostedlead").innerHTML=xmlhttp.responseText;
}
}

//##############################################FOR DISABLE LEAD END HWERE ###################

function GetXmlHttpObject()
{
if (window.XMLHttpRequest)
  {
  // code for IE7+, Firefox, Chrome, Opera, Safari
  return new XMLHttpRequest();
  }
if (window.ActiveXObject)
  {
  // code for IE6, IE5
  return new ActiveXObject("Microsoft.XMLHTTP");
  }
return null;
}
</script>

