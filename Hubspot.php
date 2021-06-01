	<?php 
  function plainMessageHubspot($email)
	{	
		$email = preg_replace("/<img[^>]+\>/i", "", $email); 
		$email = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $email);
		$newText = preg_replace('/(<[^>]+) style=\'.*?\'/i', '$1', $email);
		$newText = preg_replace('/\s+/', ' ',$newText);
		$explo = explode('View in HubSpot',$newText);
		$newText11 = preg_split( '/(<table>|<TABLE>)/', $explo[0]);
		
		$temp=0;
		if(count($newText11)> 1)
		{
			$newText11 = preg_split('/(<p>|<P>)/', $newText11[0]);
			$temp =1;
		}
		if(count($newText11)<= 1)
		{
			$newText1 = preg_replace("/<p\s\/>/is", "<p>", $newText11[0]);
			$newText11 = preg_split('/(<p>|<P>)/', $newText1);
			$temp =1;
		} 	
		$new_array ='';
		$newLinet='';
		$i=0;
		foreach($newText11 as  $test1)
		{
			$test = str_replace("*","",$test1);
			$test = preg_replace("/<.*?>/","#",$test,3);
			$test = strip_tags(preg_replace("/\s+/",' ', trim($test)));
			$test = preg_replace("/# #/",">>>>",$test);
			$test = preg_replace("/#/"," ",$test);
			$test = preg_replace("/#/"," : ",$test);			
			if (strpos(strip_tags($test),':') !== false) 
			{
				$Content = preg_replace("/&#[a-z0-9]+;/i"," ",$test);
				$Content = preg_replace("/#/"," ",$test);
				$new_array[]=trim(strip_tags($Content));
			}		
			$test="";
			$test1="";
		}

		foreach($new_array as $newdat)
		{
			$newdat = str_replace(": :",":",$newdat);
			list($pieceName,$GetVal)= preg_split("/[\:]+/", $newdat ,2);
			
			$Textnew12 = explode("?",$pieceName,2);
			$GetValnew = explode(">>>>",$GetVal,2);
			$pieceName = $Textnew12[0];
			$GetVal = $GetValnew[0];
			$pieceName = str_replace("&nbsp;"," ",$pieceName);
			$GetVal = str_replace("&nbsp;"," ",$GetVal);
			
			if(!empty($GetVal) && $GetVal<>'' && !empty($pieceName) && $pieceName<>'')
			{
				if(strstr(strtolower($pieceName),"how did you hear about us")){
					$explodeContent = explode("View in HubSpot",$GetVal);
					
					if(!strstr(strtolower($pieceName),"These fields were submitted by the contact in a previous")){
						$newLinet[] = trim(strip_tags(preg_replace("/\s+/",'_', trim($pieceName)))).": ".trim(strip_tags(preg_replace('/\s+/',' ',$explodeContent[0])));
					}
					
					if(count($explodeContent)>1 && strstr($explodeContent[1],"Company size:")){
						$explodeCompSize = explode("Company size:",$explodeContent[1]);
						
						$explodeIndustry = explode("Industry:",$explodeCompSize[1]);
						$newLinet[] = "CompanySize:".trim(strip_tags(preg_replace('/\s+/',' ',$explodeIndustry[0])));
						$explodeLoca 	= explode("Location:",$explodeIndustry[1]);
						if(count($explodeLoca)>1 && strstr($explodeLoca[1],"Hub ID:")){
							$explodeLoca1 = explode("Hub ID:",$explodeLoca[1]);
							$explodeCityState = explode(",",$explodeLoca1[0]);
							$newLinet[] = "City: ".trim(strip_tags(preg_replace('/\s+/',' ',$explodeCityState[0])));
							$newLinet[] = "StateFetched: ".trim(strip_tags(preg_replace('/\s+/',' ',$explodeCityState[1])));
						}
					}
				}
				elseif(strstr(strtolower($pieceName),"these fields were submitted by the contact in a previous form submission."))
				{
					$explodeCompSize = explode("Company size:",$GetVal);
						
					$explodeIndustry = explode("Industry:",$explodeCompSize[1]);
					$newLinet[] = "CompanySize:".trim(strip_tags(preg_replace('/\s+/',' ',$explodeIndustry[0])));
					$explodeLoca 	= explode("Location:",$explodeIndustry[1]);
					// $newLinet[] = "Industry:".trim(strip_tags(preg_replace('/\s+/',' ',$explodeLoca[0])));
					if(count($explodeLoca)>1 && strstr($explodeLoca[1],"Hub ID:")){
						$explodeLoca1 = explode("Hub ID:",$explodeLoca[1]);
						$explodeCityState = explode(",",$explodeLoca1[0]);
						$newLinet[] = "City: ".trim(strip_tags(preg_replace('/\s+/',' ',$explodeCityState[0])));
						$newLinet[] = "StateFetched: ".trim(strip_tags(preg_replace('/\s+/',' ',$explodeCityState[1])));
					}
				}
				else{
					$newLinet[] = trim(strip_tags(preg_replace("/\s+/",'_', trim($pieceName)))).": ".trim(strip_tags(preg_replace('/\s+/',' ',$GetVal)));
				}
			}
		}
		return $newLinet;
	}
