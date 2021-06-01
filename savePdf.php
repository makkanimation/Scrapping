<?php
$pdf -> ezSetMargins(50,70,50,50);
$all = $pdf->openObject();
$pdf->saveState();
$pdf->setStrokeColor(0,0,0,1);


$pdf->restoreState();
$pdf->closeObject();
// note that object can be told to appear on just odd or even pages by changing 'all' to 'odd'
// or 'even'.
$pdf->addObject($all,'all');

$pdf->ezSetDy(-100);

//$mainFont = './fonts/Helvetica.afm';
$mainFont = 'fonts/Times-Roman.afm';
$codeFont = 'fonts/Courier.afm';
// select a font
$pdf->selectFont($mainFont);
$pdf->ezText($Message,20,array('justification'=>'left'));
$pdf->ezSetDy(-100);
// modified to use the local file if it can

$pdf->openHere('Fit');

$PdffileName = $ID."_OutlookMails.pdf";
$Pdffile = "msg_pdf/".$PdffileName;
$pdfcode = $pdf->output();
$fp=fopen($Pdffile,'wb');
fwrite($fp,$pdfcode);
fclose($fp);


// insert into main pdfPage folder
/*$PdffileNamePage = $ID."_OutlookMails.pdf";
$PdffilePage = "../pdfPage/".$PdffileNamePage;
$pdfcode = $pdf->output();
$fpPage=fopen($PdffilePage,'wb');
fwrite($fpPage,$pdfcode);
fclose($fpPage);*/

?>

