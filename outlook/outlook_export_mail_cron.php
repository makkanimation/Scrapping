<?php
// include('../classes/global.php');
include 'classes/class.ezpdf.php';

// include('classes/etodb_class.php');
// $db=new Admin();
// set_time_limit(0);  
// global $table_pre;

$edb = new EMAIL_TO_DB();

// define a clas extension to allow the use of a callback to get the table of contents, and to put the dots in the toc
class Creport extends Cezpdf
{
	var $reportContents = array();
	function Creport($p,$o,$t,$op)
	{
		$this->Cezpdf($p,$o,$t,$op);
	}
	function rf($info)
	{
		// this callback records all of the table of contents entries, it also places a destination marker there
		// so that it can be linked too
		$tmp = $info['p'];
		$lvl = $tmp[0];
		$lbl = rawurldecode(substr($tmp,1));
		$num=$this->ezWhatPageNumber($this->ezGetCurrentPageNumber());
		$this->reportContents[] = array($lbl,$num,$lvl );
		$this->addDestination('toc'.(count($this->reportContents)-1),'FitH',$info['y']+$info['height']);
	}
}
$pdf = new Creport('a4','portrait','color',array(0.8,0.8,0.8));
/*****************End of ClassPDF********************************/

$sql1 = $db->getQuery("select ID from ".$table_pre.EMAIL_OUTLOOKS." where criteriastatus='0'");
while($fetchID = $db->getNumArray($sql1))
{
	$ID			= $fetchID[0];
	// echo "<br/><br/>-- ".$ID;
	$execute 	= $db->getQuery("SELECT Message,Subject,DateE,Message_html FROM ".$table_pre.EMAIL_OUTLOOKS." WHERE ID='".$ID."'");
	$row     	= $db->getAssocArray($execute);
	if(!empty($row['Message']))
	{
		$Message3 	= str_replace('>>','',$row['Message']);
		$Message2 = $Message3.'<br/> Testing:';
	}
	else
	{ 
		$Message1 	= $edb->plainMessage($row['Message_html']);
	}
	$FwCharArr	= array(":", "FW", "Fw");
	$Subject 	= trim(str_replace($FwCharArr,'',$row['Subject']));
	$Subject 	= trim($Subject);
	
	$DateE = $row['DateE'];
	$insert_fields="";$insert_vals="";$creat_feelds="";
	
	$selQry = $db->getQuery("select FormFieldName,DbFieldName,type from ".$table_pre.EMAIL_CRITERIA." where DbFieldName is NOT NULL and DbFieldName<>'' and IsActive='1' order by DbFieldName");
	$pieceName 	= '';
	$numrows 	= $db->getNumRows($selQry );
	
	if(!empty($Message1))
	{
		while($resMsg = $db->getNumArray($selQry))
		{
			$ll=0;
			$patterntserch = $resMsg[0];
			$pattern = "'".$patterntserch." (.*?)[/\n]'s";
			
			foreach($Message1 as $Message)
			{ 
				list($pieceName,$GetVal)= preg_split("/[\:]+/", $Message,2,PREG_SPLIT_NO_EMPTY);
				$CharArr          		= array(":", "?", "(", ")","'",",");
				$pieceName        		= str_replace($CharArr, "", $pieceName);
				
				if (trim(strip_tags($pieceName))==trim(strip_tags($patterntserch))) 
				{ 
					//***********if criteria match then update status************/
					$criteriastatus = $criteriastatusFlash = '2';
					$DateEnter = explode(' ',$DateE);
					$SubmitDate = $DateEnter[0];
					
					$updatesql1 = $db->getQuery("update ".$table_pre.EMAIL_OUTLOOKS." set criteriastatus='2' where ID='".$ID."'");
					
					$findwholetext          = $Message;//$matches[0][0];
					
					list($pieceName,$GetVal)= preg_split("/[\:]+/", $findwholetext,2,PREG_SPLIT_NO_EMPTY);
					$CharArr          		= array(":", "?", "(", ")","'",",");
					$CharArr1          		= array(",");
					$pieceName        		= str_replace($CharArr, "", $pieceName);
					$GetVal        			= str_replace($CharArr1, "", $GetVal);
								
					$fieldname[]     		= str_replace(" ","_",$pieceName);
					$columnval[]      		= trim($GetVal);
					$columnvalForCSV[]      = "'".trim($GetVal)."'";
					
					$pieceNameS  = trim($patterntserch);
					// echo "<br/><br/>select a.ID,a.FieldName from ".$table_pre.EMAIL_FORMFIELDS." as a left join ".$table_pre.EMAIL_CRITERIA." as b on a.ID=b.DbFieldName where b.FormFieldName='$pieceNameS' and b.IsActive='1'";
					$sql_pereticularFornat =$db->getQuery("select a.ID,a.FieldName from ".$table_pre.EMAIL_FORMFIELDS." as a left join ".$table_pre.EMAIL_CRITERIA." as b on a.ID=b.DbFieldName where b.FormFieldName='$pieceNameS' and b.IsActive='1'"); 
					$countrow = $db->getNumRows($sql_pereticularFornat);					
					while($Res_pereticularFornat = $db->getNumArray($sql_pereticularFornat))
					{
						$perticularCol[] = trim($Res_pereticularFornat['1']);
						$perticularCol1[] = trim($GetVal);						
					}	
					$ll=1;
				}
			} 
		}		
	}
	else 
	{
		while($resMsg = $db->getNumArray($selQry))
		{
			$ll=0;
			$type='';
			$patterntserch = $resMsg[0];
			$type = $resMsg[2];
			
			if($type=='2' || $type=='3')
			{
				$pattern = "'".$patterntserch." (.*?)[/\n]'sU";
			}
			else
			{
				$pattern = "'".$patterntserch."(.*?)[/\n]'s";
			}
			
			if(preg_match_all($pattern, $Message2, $matches))
			{
				$criteriastatus = $criteriastatusFlash = '2';
				$DateEnter = explode(' ',$DateE);
				$SubmitDate = $DateEnter[0];
				$updatesql1 = $db->getQuery("update ".$table_pre.EMAIL_OUTLOOKS." set criteriastatus='2' where ID='".$ID."'");
				$findwholetext          = $matches[0][0];
				if($type=='2' || $type=='3')
				{
					$CharArr1          		= array("\n");
					$findwholetextnew        	= str_replace($CharArr1, " ", $findwholetext);
					$Charnew          		= array("Type of Contact:","Think Green!");
					$findwholetext1        		= str_replace($Charnew,">>>>", $findwholetextnew);
					$testData = explode(">>>>", $findwholetext1);						
					$findwholetext2 = $testData[0];
					if($type=='3')
					{
						$findwholetext2 = str_replace('Message:'," ", $findwholetext2);
						$findwholetext2 = $patterntserch.' '.$findwholetext2;
					}
					list($pieceName,$GetVal)= preg_split("/[\:]+/", $findwholetext2,2,PREG_SPLIT_NO_EMPTY);
				}
				else
				{					
					list($pieceName,$GetVal)= preg_split("/[\:]+/", $findwholetext ,2,PREG_SPLIT_NO_EMPTY);
				}				
					
				$GetVal = strip_tags(preg_replace('/\s+/',' ', $GetVal));
				$pieceName = strip_tags(preg_replace('/\s+/',' ', $pieceName));
				$CharArr          		= array(":", "?", "(", ")","'",",");
				$pieceName        		= str_replace($CharArr, "", $pieceName);
				$GetVal        			= str_replace(',', "", $GetVal);
				$fieldname[]     		= str_replace(" ","_",$pieceName);
				$columnval[]      		= trim($GetVal);
				$columnvalForCSV[]      = "'".trim($GetVal)."'";
				
				$pieceNameS  = trim($patterntserch);
				
				$sql_pereticularFornat =$db->getQuery("select a.ID,a.FieldName from ".$table_pre.EMAIL_FORMFIELDS." as a left join ".$table_pre.EMAIL_CRITERIA." as b on a.ID=b.DbFieldName where b.FormFieldName like '$pieceNameS%' and b.IsActive='1' group by a.ID" );
				$countrow = $db->getNumRows($sql_pereticularFornat);	
				while($Res_pereticularFornat = $db->getNumArray($sql_pereticularFornat))
				{
					$perticularCol[] = trim($Res_pereticularFornat['1']);
					$perticularCol1[] = trim($GetVal);
				}
				$ll=1;
				
			}
		}
	}
	// die;
	$perticularCol_new      = array_unique($perticularCol);
	$Var= array_keys($perticularCol_new);
	
	foreach($Var as $val)
	{
		$TableValue[] = $perticularCol1[$val];
	}
	 
	$i=0;
	foreach($perticularCol_new as $tableFealds)
	{
		$insert_fields.="`".str_replace(" ","_",trim($tableFealds))."`,";
		$creat_feelds.= "`".str_replace(" ","_",trim($tableFealds))."` text,"; 
		$insert_vals.="\"".$edb->checkEmpty(trim($TableValue[$i]))."\",";
		$i++;
	}	
	unset($perticularCol);
	unset($perticularCol1);
	unset($TableValue);
	
	
	if($kkt==1)
	{
		if($criteriastatusFlash<>'2')
		{ 
			$criteriastatus='1';
			$updatesql1 = $db->getQuery("update ".$table_pre.EMAIL_OUTLOOKS." set criteriastatus='1' where ID='".$ID."'");
			$criteriastatusFlash='1'; 
		}	
	}
	
	$creatTable='';
	$insert_fields =substr($insert_fields,0,-1);
	$creatTable= "CREATE TABLE `".$table_pre.EMAIL_FIXEDVAL."` (ID int(11) NOT NULL auto_increment,{$creat_feelds} PRIMARY KEY  (ID))";
	// echo "<br/><br/>-- ".$creatTable;
	// echo "<br/><br/>-- ".$insert_vals."<br/><br/>"; 
	$insert_vals =substr($insert_vals,0,-1);
	// echo "<br/><br/>-- ".$insert_vals."<br/><br/>"; 
	// exit;
	if($criteriastatusFlash=='2')
	{
		/****************Create CSV Heading and data with Subject - CSVWithSubject****************/
		$heading = $fieldname; 
		array_push($heading,"subject");
		$header = '';
		foreach($heading as $h )
		{
			$header .= $h. ",";
		}
		$h++;
		
		$dataColumnval = $columnval; 
		array_push($dataColumnval,$Subject);
		$data = '';
		foreach($dataColumnval as $d )
		{
			$data .= $d. ",";
		}
		$d++;	
		$data = str_replace("\r" , "" , $data);	
		if( trim($data) == "" )
		{
			$data = "\n(0)Records Found!\n";                        
		}
		
		/* $file = "export/CSVWithSubject/".$ID."_OutlookMails.csv";
		$handle = fopen($file,'w');
		fwrite($handle, "$header\n$data");
		fclose($handle); */
		/************************Pdf creation page***********/
		// include("savePdf.php");
		/************************Pdf creation page***********/
				
		/***********************Create CSV with Particular fields****************/
		// $perticularCol      = @implode(",",array_unique($perticularCol));
		// $pimeryfield = "ID int(11) NOT NULL auto_increment,";
		// $perticularColStucture = @implode(",",array_unique($perticularColStucture));
		// $indxingfield = "PRIMARY KEY  (ID)";
		// $tblStructure = $pimeryfield.$perticularColStucture.",".$indxingfield;
		
		$dropperticularsql = $db->getQuery("DROP TABLE IF EXISTS  ".$table_pre.EMAIL_FIXEDVAL."");
		// $createsqlperticular="CREATE TABLE ".$table_pre.EMAIL_FIXEDVAL." (".$tblStructure.") ";
		
		$rsperticular=$db->getQuery($creatTable);
		$columnvalForCSV      = @implode(",",$columnvalForCSV);
		
		$insertperticularSql = $db->getQuery("INSERT into ".$table_pre.EMAIL_FIXEDVAL."($insert_fields) values ($insert_vals)");	
		$result2 = $db->getQuery("select * from ".$table_pre.EMAIL_FIXEDVAL."");	
		
		$heading_prti= array('Referral,Brand,ReceivedDateTime,FirstName,LastName,ContactTitle,Email,Company,Address,County,City,State,ZipCode,Country,LeadSource1,LeadSource2,LeadSource3,LeadComments,PhoneSupplied,PhSuppliedExtension,PhoneResearched,CSRName,PDF,DUNS,WebAddress,SIC,NAICS,noOfEmployees,ParentName,LineOfBusiness,Product,Market,PQ,ServiceProvider,RequestType,LeadMarking,DemoLead,HubSpot_Score,Contact_Belongs_to_List,Product_Category');
		$header_prti = '';
		$dataROWS = '';
		
		foreach( $heading_prti as $h_prti )
		{
			$header_prti .= $h_prti. ",";
		}	
		while($row2 = $db->getAssocArray($result2))
		{	
			$lineData = '';
			$rowID = $ID;
			// $Brand ='';

			/*$newName = strtolower(preg_replace("/[^A-Za-z0-9]/","",$row2['First_Name']));
			if(!empty($newName))
			{*/			
				$Contact =str_replace(","," " , trim($row2['First_Name']));
				$Contact =str_replace("\n"," " , $Contact );
				$Contact =preg_replace("/\r/", "<br>" ,$Contact);
				$Contact =preg_replace("/\n/", "<br>" ,$Contact);
				
				$ContactNew = explode(' ', $Contact,2);
				
				$First_Name = $ContactNew[0];
				$Last_Name = $ContactNew[1];
					
				$Email =str_replace(","," " , trim($row2['Email']));
				$Email =str_replace("\n"," " , $Email );
				$Email =preg_replace("/\r/", "<br>" ,$Email);
				$Email =preg_replace("/\n/", "<br>" ,$Email);
				
				$ContactTitle =str_replace(","," " , trim($row2['ContactTitle']));
				$ContactTitle =str_replace("\n"," " , $ContactTitle );
				$ContactTitle =preg_replace("/\r/", "<br>" ,$ContactTitle);
				$ContactTitle =preg_replace("/\n/", "<br>" ,$ContactTitle);
				
				$Company =str_replace(","," " , trim($row2['Company']));
				$Company =str_replace("\n"," " , $Company );
				$Company =preg_replace("/\r/", "<br>" ,$Company);
				$Company =preg_replace("/\n/", "<br>" ,$Company);	
				
				$PhoneSupplied =str_replace(","," " , trim($row2['PhoneSupplied']));
				$PhoneSupplied =str_replace("\n"," " , $PhoneSupplied );
				$PhoneSupplied =preg_replace("/\r/", "<br>" ,$PhoneSupplied);
				$PhoneSupplied =preg_replace("/\n/", "<br>" ,$PhoneSupplied);
				
				$PhoneResearched =str_replace(","," " , trim($row2['PhoneResearched']));
				$PhoneResearched =str_replace("\n"," " , $PhoneResearched );
				$PhoneResearched =preg_replace("/\r/", "<br>" ,$PhoneResearched);
				$PhoneResearched =preg_replace("/\n/", "<br>" ,$PhoneResearched);
			
				$Address =str_replace(","," " , trim($row2['Address']));
				$Address =str_replace("\n"," " , $Address );
				$Address =preg_replace("/\r/", "<br>" ,$Address);
				$Address =preg_replace("/\n/", "<br>" ,$Address);			
				
				$comment1 =str_replace(","," " , trim($row2['comment1']));
				$comment1 =str_replace("\n"," " , $comment1 );
				$comment1 =preg_replace("/\r/", "<br>" ,$comment1);
				$comment1 =preg_replace("/\n/", "<br>" ,$comment1);
				
				if(!empty($comment1))
				{
					$Address=$Address.' '.$comment1;
				}
				
				$City =str_replace(","," " , trim($row2['City']));
				$City =str_replace("\n"," " , $City );
				$City =preg_replace("/\r/", "<br>" ,$City);
				$City =preg_replace("/\n/", "<br>" ,$City);
				
				$State =str_replace(","," " , trim($row2['State']));
				$State =str_replace("\n"," " , $State );
				$State =preg_replace("/\r/", "<br>" ,$State);
				$State =preg_replace("/\n/", "<br>" ,$State);							
				
				$Country =str_replace(","," " , trim($row2['Country']));
				$Country =str_replace("\n"," " , $Country );
				$Country =preg_replace("/\r/", "<br>" ,$Country);
				$Country =preg_replace("/\n/", "<br>" ,$Country);
				
				$ZipCode =str_replace(","," " , trim($row2['ZipCode']));
				$ZipCode =str_replace("\n"," " , $ZipCode );
				$ZipCode =preg_replace("/\r/", "<br>" ,$ZipCode);
				$ZipCode =preg_replace("/\n/", "<br>" ,$ZipCode);				
				
				$LeadComments =str_replace(","," " , trim($row2['LeadComments']));
				$LeadComments =str_replace("\n"," " , $LeadComments );
				$LeadComments =preg_replace("/\r/", "<br>" ,$LeadComments);
				$LeadComments =preg_replace("/\n/", "<br>" ,$LeadComments);
					
				$HubSpot_Score =str_replace(","," " , trim($row2['HubSpot_Score']));
				$HubSpot_Score =str_replace("\n"," " , $HubSpot_Score );
				$HubSpot_Score =preg_replace("/\r/", "<br>" ,$HubSpot_Score);
				$HubSpot_Score =preg_replace("/\n/", "<br>" ,$HubSpot_Score); 
					
				$Contact_Belongs_to_List =str_replace(","," " , trim($row2['Contact_Belongs_to_List']));
				$Contact_Belongs_to_List =str_replace("\n"," " , $Contact_Belongs_to_List );
				$Contact_Belongs_to_List =preg_replace("/\r/", "<br>" ,$Contact_Belongs_to_List);
				$Contact_Belongs_to_List =preg_replace("/\n/", "<br>" ,$Contact_Belongs_to_List); 
					
				$Product_Category =str_replace(","," " , trim($row2['Product_Category']));
				$Product_Category =str_replace("\n"," " , $Product_Category );
				$Product_Category =preg_replace("/\r/", "<br>" ,$Product_Category);
				$Product_Category =preg_replace("/\n/", "<br>" ,$Product_Category); 
				
				$IP_Address =str_replace(","," " , trim($row2['IP_Address']));
				$IP_Address =str_replace("\n"," " , $IP_Address );
				$IP_Address =preg_replace("/\r/", "<br>" ,$IP_Address);
				$IP_Address =preg_replace("/\n/", "<br>" ,$IP_Address); 
				
				
				// $Subjectnew = implode(" ", array_slice(str_word_count($Subject ,2 ), 0, 3));
				$Subject = $db->sanitizeData($Subject);
				$sql_leadSource = $db->getQuery("select LeadSource,LeadComments,LeadSource2,Brand from ".$table_pre.EMAIL_SUBJECT." where Subject like '%$Subject%'");
				list($LeadSource1,$LeadComment,$LeadSource2,$Brand) = $db->getNumArray($sql_leadSource);
				
				/* if(strpos($Subject,'Rigging Catalog Ordering') == false)
				{	
					if(empty($LeadSource2))
					{
						$LeadSource1 = $Request;
					}
				} */
				// $LeadComments=$LeadComment;
				
				
				// Add slace and proper formated doucment 
				$FirstName =addslashes(html_entity_decode($First_Name));
				$LastName =addslashes(html_entity_decode($Last_Name));
				// $Contact =addslashes(html_entity_decode($Contact));
				$Email =addslashes(html_entity_decode($Email));
				$Company = addslashes(html_entity_decode($Company));
				$Address =addslashes(html_entity_decode($Address));			
				$City =addslashes(html_entity_decode($City));
				$State =addslashes(html_entity_decode($State));
				$ZipCode =addslashes(html_entity_decode($ZipCode));
				$Country =addslashes(html_entity_decode($Country));
				$LeadComments = addslashes(html_entity_decode($LeadComments));				
				$PhoneSupplied =addslashes(html_entity_decode($PhoneSupplied));
				$PhoneResearched =addslashes(html_entity_decode($PhoneResearched));
				
				// $HubSpot_Score =addslashes(html_entity_decode($HubSpot_Score));
				// $Contact_Belongs_to_List =addslashes(html_entity_decode($Contact_Belongs_to_List));
				// $Product_Category =addslashes(html_entity_decode($Product_Category));
				$IP_Address =addslashes(html_entity_decode($IP_Address));
				
				
				$ContactTitle =addslashes(html_entity_decode($ContactTitle));
				// $todayDateTime= date('Y-m-d H:i:s');
				
				$LeadDate = $SubmitDate;
				$ReceivedDateTime = $DateE;//$todayDateTime;
								
				$lineData = $Referral.','.$Brand.','.$ReceivedDateTime.','.$FirstName.','.$LastName.','.$ContactTitle.','.$Email.','.$Company.','.$Address.','.$County.','.$City.','.$State.','.$ZipCode.','.$Country.','.$LeadSource1.','.$LeadSource2.','.$LeadSource3.','.$LeadComments.','.$PhoneSupplied.','.$PhSuppliedExtension.','.$PhoneResearched.','.$CSRName.','.$PDF.','.$DUNS.','.$WebAddress.','.$SIC.','.$NAICS.','.$noOfEmployees.','.$ParentName.','.$LineOfBusiness.','.$Product.','.$Market.','.$PQ.','.$ServiceProvider.','.$RequestType.','.$LeadMarking.','.$DemoLead.','.$HubSpot_Score.','.$Contact_Belongs_to_List.','.$Product_Category;		
				
				// $dataROWS .= trim($lineData). "\n";
				$csvfile = $ID."_OutlookMails.csv"; 	
				$insertSql = @$db->getQuery("INSERT into ".$table_pre.EMAIL_DOWNLOAD."(Referral,ReferralEmail,Brand,ReceivedDateTime,FirstName,LastName,ContactTitle,Email,Company,Address,County,City,State,ZipCode,Country,LeadSource1,LeadSource2,LeadSource3,LeadComments,PhoneSupplied,PhSuppliedExtension,PhoneResearched,CSRName,PDF,DUNS,WebAddress,SIC,NAICS,noOfEmployees,ParentName,LineOfBusiness,Product,Market,PQ,ServiceProvider,RequestType,DemoLead,ipAddress,filename,Subject,PdfLink) values('".$Referral."','','".$Brand."','".$ReceivedDateTime."','".$FirstName."','".$LastName."','".$ContactTitle."','".$Email."','".$Company."','".$Address."','".$County."','".$City."','".$State."','".$ZipCode."','".$Country."','".$LeadSource1."','".$LeadSource2."','".$LeadSource3."','".$LeadComments."','".$PhoneSupplied."','".$PhSuppliedExtension."','".$PhoneResearched."','".$CSRName."','".$PDF."','".$DUNS."','".$WebAddress."','".$SIC."','".$NAICS."','".$noOfEmployees."','".$ParentName."','".$LineOfBusiness."','".$Product."','".$Market."','".$PQ."','".$ServiceProvider."','".$RequestType."','".$DemoLead."','".$IP_Address."','".$csvfile."','".$Subject."','".$PdffileName."')");
			//}
		}
		
		if( trim($dataROWS) == "" )
		{
			$dataROWS = "\n(0)Records Found!\n";                        
		}
		
		// $file = "export/CSV/".$csvfile;
		// $heading_prti = fopen($file,'w');
		// fwrite($heading_prti, "$header_prti\n$dataROWS");
		// fclose($heading_prti);
	}
	
	unset($fieldname);
	unset($columnval);
	unset($columnvalForCSV);
	unset($perticularCol);
	unset($perticularColStucture);
	unset($reportContents);
	unset($Message1);
	unset($Message2);
	unset($insert_fields);
	unset($creat_feelds);
	unset($insert_vals);
				
	unset($Contact);
	unset($Email);
	unset($Company);
	unset($Address);	
	unset($City);
	unset($State);
	unset($ZipCode);
	unset($Country);
	unset($LeadComments);
	unset($PhoneSupplied);
	unset($PhoneResearched);
	unset($InquiryType);
	unset($IndustryType);
	unset($ContactTitle);
	unset($Brand);
	unset($Product);
	unset($LeadDate);
	unset($ReceivedDateTime);
	
	unset($result2);
// exit;	
}
 echo "<br>Thanks Download Comleted..."; 
 header('Location: outlook_downloadData.php');

// exit;
?>