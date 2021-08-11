<?php 

	function plainMessage_Eloqua_Interlynx($email)
	{	
		$newText = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $email);
		/* $newText_brak = explode("Data Provided in Form", $newText,2); */
		$newText_brak = explode("Data Provided in Form", $newText,2);
		// echo '<pre>';

		$newText_n = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', $newText_brak[1]);
		$newText = preg_split( '/(<tbody>|<TBODY>)/', $newText_n);	
		$testVal = preg_replace("/<div><span>/",": ",$newText[4]);
		$testVal = preg_replace("/<\/span><\/div>/","<br>",$testVal);
		
		$newText11 = preg_split( '/<br>/', $testVal); 
		// echo "<pre>";	
		// print_r($newText11);
		$temp=0;
 	
		
		$new_array ='';
		$newLinet='';
		// echo $newText11[1];
		// print_r($newText11);die; 
		
		$i=0;
		
		foreach($newText11 as  $test1)
		{
			// echo "<br/>".$test1;
			// $test1 = $newText11[$i];
			// echo "<br/>".$test1;			
			$test = preg_replace("/<.*?>/","#",$test1,3);
			// $test = preg_replace("/&#[a-z0-9]+;/i"," ",$test);
			$test = strip_tags(preg_replace("/\s+/",' ', trim($test)));
			// echo "<br/>".$test;
			$test = preg_replace("/# #/"," ",$test);
			// echo "<br/>".$test;
			$test = preg_replace("/#/"," ",$test);
			// $test = preg_replace("/: : /"," : ",$test);
			// echo "<br/>".$test;
			$test = preg_replace("/#/"," : ",$test);			
			if (strpos(strip_tags($test),':') !== false) 
			{
				$Content = preg_replace("/&#[a-z0-9]+;/i"," ",$test);
				$Content = preg_replace("/#/"," ",$test);
				$new_array[]=trim(strip_tags($Content));
			}		
			$test="";
			$test1="";
			// $i++;
		}
		// echo "<br><br/>----";	
		// print_r($new_array);
		
		foreach($new_array as $newdat)
		{
			$newdat = str_replace(": :",":",$newdat);
			list($pieceName,$GetVal)= preg_split("/[\:]+/", $newdat ,2);
			
			$Textnew12 = explode("?",$pieceName,2);
			$GetValnew = explode(">>>>",$GetVal,2);
			$pieceName = $Textnew12[0];
			$GetVal = $GetValnew[0];
			// echo "-->".$pieceName;
			$pieceName = str_replace("&nbsp;"," ",$pieceName);
			$GetVal = str_replace("&nbsp;"," ",$GetVal);
			
			if(!empty($GetVal) && $pieceName=="Series/Model")
			{
				$GetVal='Series/Model: '.$GetVal;
			}
			if(!empty($GetVal) && $pieceName=="Serial")
			{
				$GetVal='Serial: '.$GetVal;
			}
			
			if(!empty($GetVal) && $GetVal<>'' && !empty($pieceName) && $pieceName<>'')
			{
				$newLinet[] = trim(strip_tags(preg_replace("/\s+/",'_', trim($pieceName)))).": ".trim(strip_tags(preg_replace('/\s+/',' ',$GetVal)));
			}
		}
		/* echo "<br><br/>----";
		print_r($newLinet); 
		die; */ 
		return $newLinet;
	}

function plainMessage_tradeshow($email)
	{	
		$newText = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $email);
		$newText_brak = explode("Data Provided in Form", $newText,2);
		if(count($newText_brak)==1)
		{
			$newTextArr = preg_split( '/(<tbody>|<TBODY>)/', $newText_brak[0]);

			$newText = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', $newTextArr[1]);			
			$testVal = preg_replace("/<\/td><td *?>/",":",$newText);
			$testVal = preg_replace("/<pre>/","",$testVal);
			$testVal = preg_replace("/<\/pre>/","",$testVal);
			$testVal = preg_replace("/<tr><td>/","",$testVal);
			$testVal = preg_replace("/<\/td><\/tr>/","<br>",$testVal);
			$newText11 = preg_split( '/<br>/', $testVal);
			$temp=0;
			$new_array =array();
			$newLinet='';
			$i=0;
			foreach($newText11 as  $test1)
			{
				$test = preg_replace("/<.*?>/","#",$test1,3);
				$test = preg_replace("/##/","",$test);
				$test = preg_replace("/- /","",$test);
				$test = preg_replace("/#/","",$test);
				$test = preg_replace("/:#/"," : ",$test);
				$test = preg_replace("/#/"," : ",$test);			
				$test = preg_replace("/&nbsp;/i"," ",$test);			
				if (strpos(strip_tags($test),':') !== false) 
				{
					$Content = preg_replace("/&#[a-z0-9]+;/i"," ",$test);
					$Content = preg_replace("/#/"," ",$test);
					$new_array[]=trim(strip_tags($Content));
				}		
				$test="";
				$test1="";
				$i++;
			}

			foreach($new_array as $newdat)
			{
				list($pieceName,$GetVal)= preg_split("/[\:]+/", $newdat ,2);
				$pieceName = str_replace(":"," ",$pieceName);
				$pieceName = preg_replace('/[*:]/', '', $pieceName);			
				if(!empty($GetVal) && $GetVal<>'')
				{
					if($temp == 1)
					{
						$newLinet[] = trim(strip_tags(preg_replace('/\s+/','_', trim($pieceName)))).": ".trim(strip_tags(preg_replace('/\s+/',' ',$GetVal)));
					}
					else
					{
						$newLinet[] = trim(strip_tags(preg_replace('/\s+/','_', trim($pieceName)))).": ".trim(strip_tags(preg_replace('/\s+/',' ',$GetVal)));
					}
				}
			}
			/* echo "<br><br/>----"; */
			// echo '<pre>';
			// print_r($newLinet);
			// die;    
			return $newLinet;
		}
		else
		{
			$newText_brak[1] = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', $newText_brak[1]);	
			$newText11 = preg_split( '/(<table>|<TABLE>)/', $newText_brak[1]);
		}
		// echo "<pre>";	
		// print_r($newText11);
		$temp=0;

		if(count($newText11)>= 1)
		{
			$newText1 = preg_replace("/<tr\s\/>/is", "<tr>", $newText11[1]);
			$newText11 = preg_split('/(<tr>|<TR>)/', $newText1);
			$temp =1;
		} 	
		$new_array ='';
		$newLinet='';
		// echo $newText11[1];
		// print_r($newText11);
		
		$i=0;
		
		foreach($newText11 as  $test1)
		{
			// echo "<br/>".$test1;
			// $test1 = $newText11[$i];
			// echo "<br/>".$test1;			
			$test = preg_replace("/<.*?>/","#",$test1,3);
			// $test = preg_replace("/&#[a-z0-9]+;/i"," ",$test);
			$test = strip_tags(preg_replace("/\s+/",' ', trim($test)));
			// echo "<br/>".$test;
			$test = preg_replace("/# #/"," ",$test);
			// echo "<br/>".$test;
			$test = preg_replace("/#/"," ",$test);
			// $test = preg_replace("/: : /"," : ",$test);
			// echo "<br/>".$test;
			$test = preg_replace("/#/"," : ",$test);			
			if (strpos(strip_tags($test),':') !== false) 
			{
				$Content = preg_replace("/&#[a-z0-9]+;/i"," ",$test);
				$Content = preg_replace("/#/"," ",$test);
				$new_array[]=trim(strip_tags($Content));
			}		
			$test="";
			$test1="";
			// $i++;
		}
		// echo "<br><br/>----";	
		// print_r($new_array);
		
		foreach($new_array as $newdat)
		{
			$newdat = str_replace(": :",":",$newdat);
			list($pieceName,$GetVal)= preg_split("/[\:]+/", $newdat ,2);
			
			$Textnew12 = explode("?",$pieceName,2);
			$GetValnew = explode(">>>>",$GetVal,2);
			$pieceName = $Textnew12[0];
			$GetVal = $GetValnew[0];
			// echo "-->".$pieceName;
			$pieceName = str_replace("&nbsp;"," ",$pieceName);
			$GetVal = str_replace("&nbsp;"," ",$GetVal);
			
			if(!empty($GetVal) && $pieceName=="Series/Model")
			{
				$GetVal='Series/Model: '.$GetVal;
			}
			if(!empty($GetVal) && $pieceName=="Serial")
			{
				$GetVal='Serial: '.$GetVal;
			}
			
			if(!empty($GetVal) && $GetVal<>'' && !empty($pieceName) && $pieceName<>'')
			{
				$newLinet[] = trim(strip_tags(preg_replace("/\s+/",'_', trim($pieceName)))).": ".trim(strip_tags(preg_replace('/\s+/',' ',$GetVal)));
			}
		}
		// echo "<br><br/>----";
		// print_r($newLinet); 
		// die; 
		return $newLinet;
	}

	function plainMessage_lub($email)
	{	
		$newText = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', $email);
		$newText = preg_split( '/(<table>|<TABLE>)/', $newText);
		$newText11 = array();
		if(count($newText)>1){
			$newText11 = preg_split( '/(<\/table>)/', $newText[1]);
			$newText11 = preg_split( '/(<tr>|<TR>)/', $newText11[0]);
		}
		$new_array = array();
		for($i=0;$i<count($newText11);$i++){
			$testVal = preg_replace("/<\/td>/is"," : " ,$newText11[$i], 1 );
			$testVal = preg_replace('!\s+!', ' ', $testVal);
			$testVal = strip_tags($testVal);
			$new_array[] = $testVal;
		}
		
		$newLinet = array();
		foreach($new_array as $newdat)
		{
			list($pieceName,$GetVal)= preg_split("/[\:]+/", $newdat ,2);
			$pieceName = str_replace(":"," ",$pieceName);
			$pieceName = preg_replace('/[*:]/', '', $pieceName);			
			if(!empty($GetVal) && $GetVal<>'')
			{
				if($temp == 1)
				{
					$newLinet[] = trim(strip_tags(preg_replace('/\s+/','_', trim($pieceName)))).": ".trim(strip_tags(preg_replace('/\s+/',' ',$GetVal)));
				}
				else
				{
					$newLinet[] = trim(strip_tags(preg_replace('/\s+/','_', trim($pieceName)))).": ".trim(strip_tags(preg_replace('/\s+/',' ',$GetVal)));
				}
			}
		}		
		return $newLinet;
	}
