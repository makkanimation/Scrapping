	function plainMessage($email)
	{
		$email = preg_replace("/<p\s\/>/is", "<p>", $email);
		$newText1 = preg_split( '/(<p>|<P>)/', $email);
		$newText11 = array();
		for($i=0;$i<count($newText1);$i++)
		{
			$splitStrong = array();
			if(strstr($newText1[$i],"<strong>"))
			{
				$splitStrong = preg_split( '/(<strong.*?>|<STRONG.*?>|<Strong.*?>)/',$newText1[$i]);
				for($k=0;$k<count($splitStrong);$k++)
				{
					if(!empty($splitStrong[$k]))
					{
						$spliComment = array();
						if(strstr($splitStrong[$k],"Comment:"))
						{
							$spliComment = preg_split( "/<\/p>/",$splitStrong[$k]);
							$newText11[] =  $spliComment[0];
						}
						else{
							$newText11[] =  $splitStrong[$k];
						}
						$spliComment = array();
					}
				}
			}
		}
		$temp =1;
		$new_array =array();
		$newLinet=array();
		$i=0;
		foreach($newText11 as  $test1)
		{
			$test = preg_replace("/<.*?>/","###",$test1,2);
			$test=preg_replace('/\s+/',' ', $test);
			$test = str_replace("###
                    ###","###",$test);
			
			$test = str_replace("### ###","###",$test);
			$test = preg_replace("/###/",":",$test);
			$test = preg_replace("/::/"," : ",$test);
			$test = preg_replace("/: :/"," : ",$test);

			if (strpos(strip_tags($test),':') !== false) 
			{
				$Content = preg_replace("/&[a-z0-9]+;/i"," ",$test);
				$new_array[]=trim(strip_tags($Content));
			}		
			$test="";
			$test1="";
			$i++;
		}
		$charars=array('(',')',',');
		// print_r($new_array); 
		foreach($new_array as $newdat)
		{
			list($pieceName,$GetVal)= preg_split("/[\:]+/", $newdat ,2);
			
			$pieceName = explode("(",$pieceName,2);
			$pieceName = trim($pieceName[0]);			
			$GetVal = ltrim($GetVal,":");
			$GetVal = rtrim($GetVal,":");
			
			if($temp == 1)
			{
				$newLinet[] = trim(strip_tags(preg_replace('/\s+/','_', $pieceName))).": ".trim(strip_tags(preg_replace('/\s+/',' ',$GetVal)));
			}
			else
			{
				$newLinet[] = trim(strip_tags(preg_replace('/\s+/','_', $pieceName))).": ".trim(strip_tags(preg_replace('/\s+/',' ',$GetVal)));
			}
		} 	
		return $newLinet;
	}
