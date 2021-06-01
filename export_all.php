<?php  
session_start();
include('../classes/global.php'); 

$csvName = "records_".date("Y-m-d H:i:s")."_.csv";
$tableName = EMAIL_DOWNLOAD;
include('classes/etodb_class.php');

$edb = new EMAIL_TO_DB();

require_once $root.'/classes/PHPExcel/Classes/PHPExcel.php';
$objPHPExcel = new PHPExcel();

$excelchk=$_REQUEST['excelchk'];
if(count($excelchk)>0)
{
	$getFilterColoumns = $edb->getFilterColoumn(1);
	$head="";
	$spCount = 1;
	$countSp = 0;

	foreach($getFilterColoumns as $key => $value)
	{
		$head.='"'.$value['csvColoumn'].'",';
		$char = $edb->getNameFromNumber($countSp);

		$objPHPExcel->setActiveSheetIndex(0)->setCellValue($char.$spCount,$value['csvColoumn']);
		$countSp++;
	}
	$header=substr($head,0,-1);
	$dataROWS = "";
	foreach($excelchk as $MID)
	{
		$spCount++;
		$res=$db->getQuery("select * from ".$table_pre.$tableName." where ID='".$MID."' and IsActive='1' LIMIT 1");	
		$rows = $db->getAssocArray($res);

		if(!empty($rows['FirstName']))
		{
			$rows['First_Name'] = $rows['FirstName'];
		}
		if(!empty($rows['LastName']))
		{
			$rows['Last_Name'] = $rows['LastName'];
		}

		$line = '';
		$countSp = 0;
		foreach($getFilterColoumns as $key => $value)
		{
			$line.='"'.$edb->refineValue($rows[$value['Coloumn']]).'",';
			$char = $edb->getNameFromNumber($countSp);
			$objPHPExcel->setActiveSheetIndex(0)->setCellValue($char.$spCount,$edb->refineValue($rows[$value['Coloumn']]));
			$countSp++;
		}
		$line=substr($line,0,-1);
		$dataROWS .= trim($line). "\n";
		/**********Deactive When export Data************/
		$UpdateRes=$db->getQuery("update ".$table_pre.$tableName." set IsActive='0' where ID='".$MID."'"); 
		/**********End Deactive When export Data************/
	}
}
if( trim($dataROWS) == "" )
{
  $dataROWS = "\n(0)Records Found!\n";    
  $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A2',"(0)Records Found!");
}

$objPHPExcel->getActiveSheet()->setTitle('Simple');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
$objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel5)
header('Content-type: text/csv');
header('Content-Disposition: attachment;filename="'.$csvName.'"');
header('Cache-Control: max-age=0');

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'CSV');

$objWriter->save('php://output');
exit;
/*
header("Content-type: application/msexcel");
header("Content-Disposition: attachment; filename={$csvName}");
header("Pragma: no-cache");
header("Expires: 0");
print "$header\n$dataROWS";*/
?>