<?
session_start();
include('../classes/global.php'); 
$db=new Admin();
$csvfilepath = "header/csvheader.csv";
$readheader = fopen($csvfilepath,"r");
$heading = @fgetcsv($readheader, 50000, ",");

$header = '';
foreach( $heading as $h )
{
  $header .= $h. ",";
}

$excelchk=$_REQUEST['excelchk'];
if(count($excelchk)>0)
{
	foreach($excelchk as $MID)
	{
		$res=$db->getQuery("select * from ".$table_pre.LEAD_TEMP_DB." where LUID='".$MID."' and matchType='not-matched' and InsertInTemp='0' LIMIT 1");	

		while($rows = $db->getAssocArray($res))
		{
			$line = '';
				
			$FirstName =str_replace(","," " , trim($rows['FirstName']));
			if(empty($FirstName))
			{
				$FirstName =str_replace(","," " , trim($rows['First_Name']));
			}
			$FirstName =str_replace("\n"," " , $FirstName );
			$FirstName =preg_replace("/\r/", "<br>" ,$FirstName);
			$FirstName =preg_replace("/\n/", "<br>" ,$FirstName);
			
			$LastName =str_replace(","," " , trim($rows['LastName']));
			if(empty($LastName))
			{
				$LastName =str_replace(","," " , trim($rows['Last_Name']));
			}
			$LastName =str_replace("\n"," " , $LastName );
			$LastName =preg_replace("/\r/", "<br>" ,$LastName);
			$LastName =preg_replace("/\n/", "<br>" ,$LastName);
							
			$Email =str_replace(","," " , trim($rows['Email']));
			$Email =str_replace("\n"," " , $Email );
			$Email =preg_replace("/\r/", "<br>" ,$Email);
			$Email =preg_replace("/\n/", "<br>" ,$Email);
			
			$Address =str_replace(","," " , trim($rows['Address']));
			$Address =str_replace("\n"," " , $Address );
			$Address =preg_replace("/\r/", "<br>" ,$Address);
			$Address =preg_replace("/\n/", "<br>" ,$Address);	
			
			$City =str_replace(","," " , trim($rows['City']));
			$City =str_replace("\n"," " , $City );
			$City =preg_replace("/\r/", "<br>" ,$City);
			$City =preg_replace("/\n/", "<br>" ,$City);
			
			$State =str_replace(","," " , trim($rows['State']));
			$State =str_replace("\n"," " , $State );
			$State =preg_replace("/\r/", "<br>" ,$State);
			$State =preg_replace("/\n/", "<br>" ,$State);
			
			$Country =str_replace(","," " , trim($rows['Country']));
			$Country =str_replace("\n"," " , $Country );
			$Country =preg_replace("/\r/", "<br>" ,$Country);
			$Country =preg_replace("/\n/", "<br>" ,$Country);
			
			$ZipCode =str_replace(","," " , trim($rows['ZipCode']));
			$ZipCode =str_replace("\n"," " , $ZipCode );
			$ZipCode =preg_replace("/\r/", "<br>" ,$ZipCode);
			$ZipCode =preg_replace("/\n/", "<br>" ,$ZipCode);
						
			$Company =str_replace(","," " , trim($rows['Company']));
			$Company =str_replace("\n"," " , $Company );
			$Company =preg_replace("/\r/", "<br>" ,$Company);
			$Company =preg_replace("/\n/", "<br>" ,$Company);				
			
			$PhoneSupplied =str_replace(","," " , trim($rows['PhoneSupplied']));
			$PhoneSupplied =str_replace("\n"," " , $PhoneSupplied );
			$PhoneSupplied =preg_replace("/\r/", "<br>" ,$PhoneSupplied);
			$PhoneSupplied =preg_replace("/\n/", "<br>" ,$PhoneSupplied);
			
			$ReceivedDateTime =str_replace(","," " , trim($rows['ReceivedDateTime']));
			$ReceivedDateTime =str_replace("\n"," " , $ReceivedDateTime );
			$ReceivedDateTime =preg_replace("/\r/", "<br>" ,$ReceivedDateTime);
			$ReceivedDateTime =preg_replace("/\n/", "<br>" ,$ReceivedDateTime);
			
			$LeadSource1 =str_replace(","," " , trim($rows['LeadSource1']));
			$LeadSource1 =str_replace("\n"," " , $LeadSource1 );
			$LeadSource1 =preg_replace("/\r/", "<br>" ,$LeadSource1);
			$LeadSource1 =preg_replace("/\n/", "<br>" ,$LeadSource1);
			
			$LeadSource2 =str_replace(","," " , trim($rows['LeadSource2']));
			$LeadSource2 =str_replace("\n"," " , $LeadSource2 );
			$LeadSource2 =preg_replace("/\r/", "<br>" ,$LeadSource2);
			$LeadSource2 =preg_replace("/\n/", "<br>" ,$LeadSource2);
			
			$LeadSource3 =str_replace(","," " , trim($rows['LeadSource3']));
			$LeadSource3 =str_replace("\n"," " , $LeadSource3 );
			$LeadSource3 =preg_replace("/\r/", "<br>" ,$LeadSource3);
			$LeadSource3 =preg_replace("/\n/", "<br>" ,$LeadSource3);
			
			$ContactTitle =str_replace(","," " , trim($rows['ContactTitle']));
			$ContactTitle =str_replace("\n"," " , $ContactTitle );
			$ContactTitle =preg_replace("/\r/", "<br>" ,$ContactTitle);
			$ContactTitle =preg_replace("/\n/", "<br>" ,$ContactTitle);
			
			$Product =str_replace(","," " , trim($rows['Product']));
			$Product =str_replace("\n"," " , $Product );
			$Product =preg_replace("/\r/", "<br>" ,$Product);
			$Product =preg_replace("/\n/", "<br>" ,$Product);
			
			$LeadComments =str_replace(","," " , trim($rows['LeadComments']));
			$LeadComments =str_replace("\n"," " , $LeadComments );
			$LeadComments =preg_replace("/\r/", "<br>" ,$LeadComments);
			$LeadComments =preg_replace("/\n/", "<br>" ,$LeadComments);
			
			$Market =str_replace(","," " , trim($rows['Market']));
			$Market =str_replace("\n"," " , $Market );
			$Market =preg_replace("/\r/", "<br>" ,$Market);
			$Market =preg_replace("/\n/", "<br>" ,$Market);
			
			$Brand =str_replace(","," " , trim($rows['Brand']));
			$Brand =str_replace("\n"," " , $Brand );
			$Brand =preg_replace("/\r/", "<br>" ,$Brand);
			$Brand =preg_replace("/\n/", "<br>" ,$Brand);

			// $Contact =html_entity_decode($Contact);
			$FirstName =html_entity_decode($FirstName);
			$LastName =html_entity_decode($LastName);
			$Email =html_entity_decode($Email);
			$Company = html_entity_decode($Company);
			$Address =html_entity_decode($Address);			
			$City =html_entity_decode($City);
			$State =html_entity_decode($State);
			$ZipCode =html_entity_decode($ZipCode);
			$Country =html_entity_decode($Country);
			$LeadComments = html_entity_decode($LeadComments);			
			$PhoneSupplied =html_entity_decode($PhoneSupplied);	
			// $LeadDate =html_entity_decode($LeadDate);			
			$LeadSource1 =html_entity_decode($LeadSource1);	
			$ReceivedDateTime =html_entity_decode($ReceivedDateTime);
			$LeadSource2 =html_entity_decode($LeadSource2);
			$Market =html_entity_decode($Market);
			// $Representative =html_entity_decode($Representative);			
			$ContactTitle =html_entity_decode($ContactTitle);			
			$Brand =html_entity_decode($Brand);
			$Product =html_entity_decode($Product);
			
			// Referral	ReferralEmail	Brand	ReceivedDateTime	FirstName	LastName	ContactTitle	Email	Company	Address	County	City	State	ZipCode	Country	LeadSource1	LeadSource2	LeadSource3	LeadSource4	LeadComments	PhoneSupplied	PhSuppliedExtension	PhoneResearched	CSRName	PDF	DUNS	WebAddress	SIC	NAICS	noOfEmployees	ParentName	LineOfBusiness	Product	Market	PQ	interestedIn	DemoLead	YourTolomaticDistributor

			
			$line = '"'.$Referral.'","'.$ReferralEmail.'","'.$Brand.'","'.$ReceivedDateTime.'","'.$FirstName.'","'.$LastName.'","'.$ContactTitle.'","'.$Email.'","'.$Company.'","'.$Address.'","'.$County.'""'.$City.'","'.$State.'","'.$ZipCode.'",,"'.$Country.'","'.$LeadSource1.'","'.$LeadSource2.'","'.$LeadSource3.'","'.$LeadSource4.'","'.$LeadComments.'","'.$PhoneSupplied.'","'.$PhSuppliedExtension.'","'.$PhoneResearched.'","'.$CSRName.'","'.$PDF.'","'.$DUNS.'","'.$WebAddress.'","'.$SIC.'","'.$NAICS.'","'.$noOfEmployees.'","'.$ParentName.'","'.$LineOfBusiness.'","'.$Product.'","'.$Market.'","'.$PQ.'","","'.$DemoLead.'","'.$YourTolomaticDistributor.'"';
			
			$dataROWS .= trim($line). "\n";
		}
		
		/**********Deactive When export Data************/
		$UpdateRes=$db->getQuery("update ".$table_pre.LEAD_TEMP_DB." set InsertInTemp='10' where LUID='".$MID."'") or die("Err In Update Status :".mysql_error());
		/**********End Deactive When export Data************/
		
		$files = 'export/CSV/'.$MID.'_OutlookMails.csv'; // get all file names
			@unlink($files); // delete file
		
		$files1 = 'export/CSVWithSubject/'.$MID.'_OutlookMails.csv'; // get all file names
			@unlink($files1); // delete file
			
		$files2 = 'msg_pdf/'.$MID.'_OutlookMails.pdf'; // get all file names
			@unlink($files2); // delete file		
	}
}

if( trim($dataROWS) == "" )
{
  $dataROWS = "\n(0)Records Found!\n";                        
}


header("Content-type: application/msexcel");
header("Content-Disposition: attachment; filename=All_Mails.csv");
header("Pragma: no-cache");
header("Expires: 0");
print "$header\n$dataROWS";
?>
