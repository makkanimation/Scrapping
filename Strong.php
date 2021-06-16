function plainMessageStrong($email)
	{
		/* echo "<pre><br/>--"; */
		$newText11 = preg_split( '/(<strong.*?>|<STRONG.*?>)/', $email);
		/* $newText = preg_split( '/(<tbody>|<TBODY>)/', $email); */
		/* $newText11 = preg_split( '/(<strong.*?>|<STRONG.*?>)/', $email); */
		/* // $newText11 = preg_split( '/(<tr>|<TR>)/', $email);
		$newText = preg_split( '/(<tbody>|<TBODY>)/', $email);
		// print_r($newText);
		// echo "<pre>";
		// $newText = explode('</tbody>',$newText[1]);
		// $newText11 = preg_split( '/(<tr>|<TR>)/', $newText);
		// print_r($newText11);	 */	
		/* print_r($newText);
		$newText11 = preg_split( '/(<strong>|<STRONG>)/', $newText); */
		/* print_r($newText11); */
		
		$temp=0;
		/* if(count($newText11)<= 1)
		{
			$newText11 = preg_split( '/(<br>|<BR>)/', $email);
			$temp =1;
		} */		
		$new_array ='';
		$newLinet='';
		
		$i=0;
		foreach($newText11 as  $test1)
		{
			$test = preg_replace("/<.*?>/","#",$test1,3);
			// echo "12--- ".$test.'<br/>';
			
			$test= str_replace('?',':',$test);
			
			// echo "13--- ".$test.'<br/>';
			$test = preg_replace("/##/",":",$test);
			$test = preg_replace("/#/"," ",$test);
			$test = preg_replace("/: :/"," : ",$test);
			// echo $i;
			// echo "14-- ".$test.'<br/>';
			if (strpos(strip_tags($test),':') !== false) 
			{
				$Content = preg_replace("/&[a-z0-9]+;/i"," ",$test);
				$Content = str_replace("<br/>","," , trim($Content));
				$Content = str_replace("<br />","," , trim($Content));
				$new_array[]=trim(strip_tags($Content));
			}		
			$test="";
			$test1="";
			$i++;
		}
		/* echo "<br/>lps--- <pre>";
		print_r($new_array); */
		foreach($new_array as $newdat)
		{
			list($pieceName,$GetVal)= preg_split("/[\:]+/", $newdat ,2);
			
			/* if($pieceName=="Brand")
			{
				$newGetVal1=explode("View in",$GetVal,2);			
				if(count($newGetVal1)==2)
				{
					$GetVal=$newGetVal1[0];
				}
			}
			
			$newGetVal=explode("Hub ID:",$GetVal,2);			
			if(count($newGetVal)==2)
			{
				$GetVal=$newGetVal[0];
			}
			$pieceName= trim(str_replace(':','',$pieceName)); */
			
			$GetVal =str_replace("<br>","," , trim($GetVal));
			$GetVal =trim($GetVal,":");
			if($temp == 1)
			{
				$newLinet[] = trim(strip_tags(preg_replace('/\s+/','_', trim($pieceName)))).": ".trim(strip_tags(preg_replace('/\s+/',' ',trim($GetVal))));
			}
			else
			{
				$newLinet[] = trim(strip_tags(preg_replace('/\s+/','_', trim($pieceName)))).": ".trim(strip_tags(preg_replace('/\s+/',' ',trim($GetVal))));
			}
		} 	
		/* echo "<br/>lps--- <pre>";
		print_r($newLinet); 
		exit; */   
		 
		return $newLinet;
	}
