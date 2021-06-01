<?php 
##########################################################################################
###File:auto_stitching.php (rewritten from original v1 supplied by Interlynx)                                                                      		   #
###   Version : 1.0                                                           			#
###   Author  : Manish Kumar <michael@interlynxsystems.com>                        		#
###   Created : 2018-07-31                                                      		#
##########################################################################################

session_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_STRICT);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
require_once('../classes/global.php');  
if(!empty($maintainaceMode) && $maintainaceMode['autostitch']=='Yes')
{
	$status = 400; // Bad Request
	
	$SubjectDateTime	=	date("Y_m_d_H_i_s");
	$subject_name ="Auto Stitch maintainance: ".TITLE;
	
	$Name	=	"Team ";
	$MAILBODYTYPE 	= $mailbody_css.$BrdName." : This system is on maintainance mode for Auto Stiching.";
	$BrdSendFrom	=	$BrdSendFrom;
	$BrdSendEmail	=	SENDEMAIL;
	
	$TO = 'michael@interlynxsystems.com';
	// $CC = "adam@interlynxsystems.com";  
	require($root."include/mailbody.php");  
	echo 	$MAILBODYTYPE; die;		
} 
require_once('classes/etodb_class.php'); 

set_time_limit(0); 
$starttime = microtime(true);
function convert($size,$starttime="")
{
	$unit=array('b','kb','mb','gb','tb','pb');
	$final = "";
	if(!empty($starttime))
	{
		$diff = microtime(true) - $starttime;
		// Break the difference into seconds and microseconds
		$sec = intval($diff);
		$micro = $diff - $sec;

		// Format the result as you want it
		// $final will contain something like "00:00:02.452"
		$final = strftime('%T', mktime(0, 0, $sec)) . str_replace('0.', '.', sprintf('%.3f', $micro));
	}
	
	return @round($size/pow(1024,($i=floor(log($size,1024)))),4).' '.$unit[$i]." :: Time :=>".$final;
}
// die;
// $rque			=	"https://reviewq.irleads.com/";
// $mdbRoot		=	"http://nsp.harwoodmidwest.com/";
$rque			=	"http://interlynx/ir/reviewque1/";
$mdbRoot		=	"http://sun730/mdb/";

$systemInfURL	=	$rque."getSystemID.php";
$searchMDBUrl	=	$mdbRoot."insertSystemLeadsWithAllLeads.php";
$updateReviewQueToMDBUrl	=	$mdbRoot."updateReviewQueType.php";
$TO = "michael@interlynxsystems.com"; 
$system				= array();
$system['table_pre']= $db->prefix_table;
$system['host'] 	= $db->host;
$system['user']		= $db->user;
$system['pass']		= str_replace("&","@@and@@",$db->pass);
$system['dbName'] 	= $db->dbName;
$system_Lead 		= json_encode($system);
$system1 = $system;
$system1['dbName'] 	= "IRleadsDB";
$system_Lead1 		= json_encode($system1);
/* ======================get system details starts ========================*/
$curlResult 		= $db->mCurl("POST",$systemInfURL,$system_Lead);
// PR($curlResult); die;
$error				=	0;
$Attach_Filename	=	array();
if(!empty($curlResult) && $curlResult['resp']==201)
{
	$newReviewQProID = $curlResult['result']->proId."_".$system['dbName'];
	$system['reviewQueProjectID'] 			=	$curlResult['result']->proId;
	$system['reviewQueProjectShortName'] 	=	$curlResult['result']->proShortName;
	$system['reviewQueProjectName'] 		=	$curlResult['result']->proShortName;
	$system['outlook_email_update'] 		=	$system['dbName'].".".$table_pre.EMAIL_DOWNLOAD;
	/* ============== Fetch & Insert Raw formated leads of Specified Subject Starts =====*/  
	$edb = new EMAIL_TO_DB();
	// $edb->fetchRefinedLeads(); // for importing and inserting in email table 
	$edb->refineLeads();   // Extract Refined Lead according to CSV 
	/* ============== Fetch & Insert Raw formated leads of Specified Subject Ends =====*/ 
	$resultjson = $edb->getRefinedLeads($system);
	// PR($resultjson); die;
	$SubjectDateTime	=	date("Y_m_d_H_i_s");
	if(count($resultjson['byPassMdb'])>0)
	{
		$byePassArray = $resultjson;
		$byePassArray['result'] = $resultjson['byPassMdb'];
		$data_json 	= 	json_encode($byePassArray);
		$allrefinedLeads = $resultjson['downloadfixedval'];
		$byPassMdbDownloadfixedval = $resultjson['byPassMdbDownloadfixedval'];
		unset($resultjson);
		
		$response= $db->mCurl("POST",$searchMDBUrl,$data_json,"see");
		PR($response);
		die;
		if(isset($_GET['demo']) && $_GET['demo']=="yes")
		{
			PR($response);
		}
		// PR($response); 
		// PR($byePassArray['result']); die;
		if($response['resp']<>201 && count($byePassArray['result'])>0)
		{
			$getFilterColoumns = $edb->getFilterColoumn(1);
			$response['result']->notMatchedInserted = array();
			for($u=0;$u<count($byePassArray['result']);$u++)
			{
				$insertItem = " INSERT INTO @@tableName@@ SET ";
				foreach($getFilterColoumns as $getFilterColoumn => $getFilterColoumnVal)
				{
					$colom = $getFilterColoumns[$getFilterColoumn]['Coloumn'];
					if(strtoupper($colom)==strtoupper("ServiceProvider")) // 
					{
						$insertItem .= "`".$getFilterColoumns[$getFilterColoumn]['Coloumn']."`='N',";
					}
					elseif(empty($byePassArray['result'][$u][$colom]) && strtoupper($colom)==strtoupper("DemoLead"))
					{
						$insertItem .= "`".$getFilterColoumns[$getFilterColoumn]['Coloumn']."`='No',";
						
					}
					else{
						$insertItem .= "`".$getFilterColoumns[$getFilterColoumn]['Coloumn']."`='".str_replace("'","&#39;",$byePassArray['result'][$u][$colom])."',";
					}
				}
				
				$insertItem = substr($insertItem,0,-1).",reviewQProID='".$newReviewQProID."'";
				$response['result']->notMatchedInserted[] = $insertItem;
				$insertItem = "";
			}
			
			if(count($response['result']->notMatchedInserted)>0)
			{
				$response['resp']=201;
				$response['result']->inserted = array();
			}
		}
		if(!empty($response) && $response['resp']==201){
			$response1 = array();
			if(count($response['result']->inserted)>0 OR count($response['result']->notMatchedInserted)>0)
			{
				$insertedCount = count($response['result']->inserted);
				if($insertedCount>0)
				{
					for($in=0;$in<$insertedCount;$in++){
						if(!empty($response['result']->inserted[$in])){
							$response['result']->inserted[$in] = str_replace(",ad=",",adField=",str_replace("SubmitterLast_Name","SubmitterLastName",str_replace("SubmitterFirst_Name","SubmitterFirstName",str_replace("@@tableName@@",$db->prefix_table.LEAD_TEMP_DB,$response['result']->inserted[$in]))));
							echo $response['result']->inserted[$in]." <br/><br/>";
							$db->getQuery($response['result']->inserted[$in]);	
						}	
					}
				}
				$notMatchedInsertedCount = count($response['result']->notMatchedInserted);
				if($notMatchedInsertedCount>0)
				{
					for($in=0;$in<$notMatchedInsertedCount;$in++){
						if(!empty($response['result']->notMatchedInserted[$in])){
							$response['result']->notMatchedInserted[$in] = str_replace(",ad=",",adField=",str_replace("SubmitterLast_Name","SubmitterLastName",str_replace("SubmitterFirst_Name","SubmitterFirstName",str_replace("@@tableName@@",$db->prefix_table.LEAD_TEMP_DB,$response['result']->notMatchedInserted[$in]))));
							echo $response['result']->notMatchedInserted[$in]." <br/><br/>";
							$db->getQuery($response['result']->notMatchedInserted[$in]);	
						}	
					}
				}
			}
			if(!empty($byPassMdbDownloadfixedval))
			{
				$edb->updateRefinedLeads($byPassMdbDownloadfixedval);		
				unset($byPassMdbDownloadfixedval);
			}
		}
	}
	$tempDBCount	= $edb->getTempDBData($newReviewQProID);
	if($tempDBCount>0)
	{
		$data_json 	= 	json_encode($curlResult['result']);
		$response1= $db->mCurl("POST",$rque."User/".$system['reviewQueProjectShortName']."/autostitch_uploadCSVactionNew.php",$data_json);
		// PR($response1);
		unset($data_json);
		echo "Auto Stitching Process Successfully done.<br/>";
			if(!empty($response1) && $response1['resp']==201){
				$directout = !empty($response1['result']->out) & count($response1['result']->out)>0 ? (INT)count($response1['result']->out) : 0;
				$stuck = !empty($response1['result']->stuck) & count($response1['result']->stuck)>0  ? (INT)count($response1['result']->stuck) : 0;
				$latlongIssue = !empty($response1['result']->latlongIssue) & count($response1['result']->latlongIssue)>0 ? (INT)count($response1['result']->latlongIssue) : 0;
				$dateIssue = !empty($response1['result']->dateIssue) & count($response1['result']->dateIssue)>0 ? (INT)count($response1['result']->dateIssue) : 0;
				$emptyBrand = !empty($response1['result']->emptyBrand) & count($response1['result']->emptyBrand)>0 ? (INT)count($response1['result']->emptyBrand) : 0;
				$duplicateEntry = !empty($response1['result']->duplicateEntry) & count($response1['result']->duplicateEntry)>0 ? (INT)count($response1['result']->duplicateEntry) : 0;
				
				if(!empty($response1['result']->records))
				{
					$mdb_json = json_encode($response1['result']->records);
					$updateResponse= $db->mCurl("POST",$updateReviewQueToMDBUrl,$mdb_json);
				}
			}else{
				$directout = 0;
				$stuck = 0;
				$latlongIssue = 0;
				$dateIssue = 0;
				$emptyBrand = 0;
				$duplicateEntry = 0;
			} 	
				
			// $potCount  = !empty($response['result']->potCount) ? $response['result']->potCount : 0;
			$potCount  = 0;
			// $notMatchCount  = !empty($response['result']->notMatchCount) ? $response['result']->notMatchCount : 0;
			$notMatchCount  = 0;
			
			$stuckCount = $stuck+$latlongIssue+$dateIssue+$emptyBrand+$duplicateEntry;
			if(empty($stuckCount)){ $stuckCount = 0;}
			$exactCount = $directout+$stuckCount;
			$allTotal = $exactCount+$potCount+$notMatchCount;
			$subject_name ="Auto Stitch Success: Total Downloaded Status of ".$system['reviewQueProjectName']." -".$SubjectDateTime;
			$Name	=	"Team ";
			$attc = 0;
			if(!empty($response1['result']->exactRecords))
			{
				$csvfile 			= 	"ReviewQue_lead_".$SubjectDateTime.".csv";
				$filename 			= 	$root_dir."admin/csvfile/masterDB_CSV/exact/".$csvfile;
				$file = @fopen($filename,"w");
				@fwrite($file,$response1['result']->exactRecords); 
				@fclose($file);	
				$Attach_Filename[$attc]['path']	=	$filename;
				$Attach_Filename[$attc]['name']	=	$csvfile;
				$attc++;
			} 
				
			$fullurl=$mdbRoot;
			$MAILBODYTYPE 	= $mailbody_css." Please find the attachment of Total Downloaded Lead from outlook.<br/><br/>";
			$MAILBODYTYPE 	.="<style>	.bgtext {
					font-family: Tahoma, Verdana, Arial;
					font-size: 12px;
					color: #000000;
					text-decoration: none;
					margin: 1px;
					padding: 1px 15px;
				}

			.bgtext2 {
					font-family: Tahoma, Verdana, Arial;
					font-size: 12px;
					color: #000000;
					text-decoration: none;
					margin: 1px;

			}
			.bgtext22{
				font-family: Tahoma, Verdana, Arial;
				font-size: 12px;
				color: #000000;
				text-decoration: none;
				margin: 1px;
			}

			.button {
			font-family: Tahoma, Verdana, Arial;
			font-size: 12px;
			color: #000000;
			background-color:#CCCCCC;
			background-repeat: repeat-x;
			border: 1px solid #716F64;
			margin: 1px;
			padding: 1px 15px;
			height:22px;
			text-decoration:none;
			font-weight:bold;
			}			
			.link{ 
				font-family: Tahoma, Verdana, Arial;
				color:#000099;
				font-size: 13px;
				text-decoration:underline;
			}
			.redboldtext
			{
				font-family:Tahoma, Verdana, Arial;
				font-size:14px;
				color:#FF0000;
				font-weight: bold;
			}
					
			.buttonpurple {
				font-family: Tahoma, Verdana, Arial;
				font-size: 12px;
				color: #000000;
				background-color:#FF00FF;
				background-repeat: repeat-x;
				border: 1px solid #716F64;
				margin: 1px;
				padding: 1px 15px;
				height:22px;
				text-decoration:none;
				font-weight:bold;
			}
					
			</style><table style='width:500px' lign='center' border='0'  cellspacing='1' cellpadding='1' class='bgtext2' bgcolor='#000000' >"; 
			$MAILBODYTYPE 	.="<tr style='font-weight:800' bgcolor='#00AEEF' ><td>SNo.</td><td>Subject</td><td>Total</td></tr>";
			$MAILBODYTYPE 	.="<tr bgcolor='#CCCCCC' ><td>1.</td><td>Interlynx Total Uploaded leads in ReviewQue</td><td>".$exactCount."</td></tr>";
			$MAILBODYTYPE 	.="<tr bgcolor='#FFFFFF'><td>1a.</td><td>Stuck in ReviewQue</td><td>".$stuckCount."</td></tr>";
			$MAILBODYTYPE 	.="<tr bgcolor='#CCCCCC'><td>1b.</td><td>Out from ReviewQue</td><td>".$directout."</td></tr>";
			$MAILBODYTYPE 	.="<tr bgcolor='#FFFFFF'><td>2.</td><td>Interlynx Potential Leads</td><td>".$potCount."</td></tr>";
			$MAILBODYTYPE 	.="<tr bgcolor='#CCCCCC'><td>3.</td><td>Interlynx Not Matched Leads</td><td>".$notMatchCount."</td></tr>";
			$MAILBODYTYPE 	.="<tr bgcolor='#FFFFFF'><td></td><td>Totals</td><td>".$allTotal."</td></tr>";
			// $MAILBODYTYPE 	.="</table><br/><br/><strong>Note:</strong> <a target='_blank' href='$fullurl' class='button'>&nbsp;Login Here&nbsp;</a> to see Potential and Not Matched lead(s) in Master Database."; 
			$BrdSendFrom	=	SENDFROM;
			$BrdSendEmail	=	SENDEMAIL;

			$TO = 'adam@interlynxsystems.com,michael@interlynxsystems.com';
			$CC = "researchmanager@interlynxsystems.com,raul.nayyar@interlynxsystems.com";   
			require($root."include/mailbody.php"); 
			echo 	$MAILBODYTYPE; die;		
	}
	else
	{
		$subject_name ="Auto Stitch: No Lead to fetch in ".$system['reviewQueProjectName']." -".$SubjectDateTime;
		$Name	=	"Team ";
		
		$fullurl=$mdbRoot;
		$MAILBODYTYPE 	= $mailbody_css." There is no lead in outlook to fetch.";

		$BrdSendFrom	=	SENDFROM;
		$BrdSendEmail	=	SENDEMAIL;

		$TO = 'researchmanager@interlynxsystems.com,adam@interlynxsystems.com';
		$CC = "";   
		require($root."include/mailbody.php");
		echo "After All Completes..".convert(memory_get_usage(),$starttime)."<br/>";

		echo 	$MAILBODYTYPE; die;		
	}
}else{
	$error++;
	$response = $curlResult;
}
if($error>0){
	$SubjectDateTime	=	date("Y_m_d_H_i_s");
	$subject_name ="Auto Stitch Error: ".TITLE." -".$response['result']->exception;
	
	$Name	=	"Team ";
	$MAILBODYTYPE 	= $mailbody_css." Please find the error below.<br/><br/>";
	foreach($response['result'] as $key => $val){
		$MAILBODYTYPE 	.= "<strong>".$key."</strong>: ".$val."<br/>";
	}
	$BrdSendFrom	=	SENDFROM;
	$BrdSendEmail	=	SENDEMAIL;
	
	$TO = 'michael@interlynxsystems.com';
	// $CC = "adam@interlynxsystems.com";  
	require($root."include/mailbody.php"); 
	echo 	$MAILBODYTYPE; die;		
} 
/* ======================get system details ends ========================*/
?> 