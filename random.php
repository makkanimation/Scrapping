<?php 
  function plainMessageCatalog($email)
	{
		$explodeEmail = explode("A user made a request from the",$email);
		if(count($explodeEmail)>1)
		{
			$email = $explodeEmail[1];
		}
		$explodeS = explode("Yours sincerely",$email);
		if(count($explodeS)>1)
		{
			$email = $explodeS[0];
		}
		
		$newText = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)\s>/i",'<$1$2>', $email);
		if(count($newText)<= 1)
		{
			$newText12 = explode( 'Form Details', $newText);
			// $newText122 = preg_replace('#(<[a-z ]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $newText12[0]);
			$newText122 = preg_replace('#<(.+?)style=(:?"|\')?[^"\']+(:?"|\')?(.*?)>#si', '<a\\1 \\2>', $newText12[0]);		
			$newText1 = preg_replace("/<p\s\/>/is", "<p>", $newText122);
			$newText11 = preg_split('/(<ab>|<AB>)/', $newText122);
			// echo count($newText11);
			if(count($newText11)<=1)
			{
				$newText11 = preg_split('/(<p>|<P>)/', $newText122);
			}
			$new_array = array();
			foreach($newText11 as  $test1)
			{
				// $test1 = utf8_decode($test1);
				/*$test = preg_replace("/<.*?>/","#",$test1,3);*/
				$test = preg_replace("/<.*?>/","#",$test1);
				$test = preg_replace("/##/","",$test);
				$test = preg_replace("/#/","",$test);
				$test = preg_replace("/:#/"," : ",$test);
				$test = preg_replace("/#/"," : ",$test);				
				$test = preg_replace("/'>/",">",$test);				
				if(strpos(strip_tags($test),':') !== false) 
				{
					$Content = preg_replace("/&#[a-z0-9]+;/i"," ",$test);
					$Content = preg_replace("/#/"," ",$test);
					$new_array[]=trim(strip_tags($Content));
				}		
				$test="";
				$test1="";
				$i++;
			}
		}
		else{		 
			foreach($newText11 as  $test)
			{
				if (strpos(strip_tags($test),':') !== false) 
				{
					$new_array[]=strip_tags($test);
				}
				$test="";
			}
		}
		$newLinet = array();
		foreach($new_array as $newdat)
		{
			list($pieceName,$GetVal)= preg_split("/[\:]+/", $newdat,2);
			if($this->_s_has_special_chars($GetVal))
			{
				$GetVal = $this->removeSpacialCharactersMann($GetVal); 
			}

			$GetVal = preg_replace("/&nbsp;/i"," ",$GetVal);
			$newLinet[] =trim(strip_tags(preg_replace('/\s+/','_', trim(strip_tags($pieceName))))).": ".trim(strip_tags(preg_replace('/\s+/',' ',strip_tags($GetVal))));
		}
		return $newLinet;
	}
      
      	function plainMessageMotion($email)
	{
		$newText = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)\s>/i",'<$1$2>', $email);
		$newText = preg_split( '/(<tbody>|<TBODY>)/', $newText);
		if(!empty($newText[1]))
		{
			$newText11 = preg_split( '/(<tr>|<TR>)/', $newText[1]);
		}
		else{
			$newText11 = preg_split( '/(<tr>|<TR>)/', $newText[0]);
		}
		$newText11 =  preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', $newText11[2]);
		$newText22 = preg_split( '/(<tr>|<TR>)/', $newText11);
		$new_array = array();
		foreach($newText22 as  $test1)
		{
			$newText33 = preg_split( '/(<\/td>|<\/TD>)/', $test1);
			$test_1 = preg_replace("/<.*?>/","#",$newText33[0]);
			$test_2 = preg_replace("/<.*?>/","#",$newText33[1]);
			$test = $test_1.":".$test_2;
			$test = preg_replace("/##/","",$test);
			$test = preg_replace("/#/","",$test);
			$test = preg_replace("/:#/"," : ",$test);
			$test = preg_replace("/#/"," : ",$test);
			if(strpos(strip_tags($test),':') !== false) 
			{
				$Content = preg_replace("/&#[a-z0-9]+;/i"," ",$test);
				$Content = preg_replace("/#/"," ",$test);
				$new_array[]=str_replace("&nbsp;","",trim(strip_tags($Content)));
			}		
			$test="";
			$test1="";
			$i++;
		}
		
		foreach($new_array as $newdat)
		{
			list($pieceName,$GetVal)= preg_split("/[\:]+/", $newdat,2);
			if($this->_s_has_special_chars($GetVal))
			{
				$GetVal = $this->removeSpacialCharactersMann($GetVal); 
			}
			$GetVal = preg_replace("/&nbsp;/i"," ",$GetVal);
			$pieceName = str_replace(": :",":",$pieceName);
			$newLinet[] =str_replace(": :",":",trim(strip_tags(preg_replace('/\s+/','_', trim(strip_tags($pieceName))))).": ".trim(strip_tags(preg_replace('/\s+/',' ',strip_tags($GetVal)))));
		}
		return $newLinet;
	} 
	
	function plainMessagetraceparts($email)
	{
		$expEmail = explode("The user above has selected the following product",$email);
		
		$newText = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)\s>/i",'<$1$2>', $expEmail[0]);
		$newText = preg_split( '/(<tbody>|<TBODY>)/', $newText);
		if(!empty($newText[1]))
		{
			$newText11 = preg_split( '/(<tr>|<TR>)/', $newText[1]);
		}
		else{
			$newText11 = preg_split( '/(<tr>|<TR>)/', $newText[0]);
		}
		// $newText11 =  preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', $newText11[2]);
		// $newText22 = preg_split( '/(<tr>|<TR>)/', $newText11);
		// PR($newText11); die;
		$new_array = array();
		foreach($newText11 as  $test1)
		{
			$newText33 = preg_split( '/(<\/td>|<\/TD>)/', $test1);
			$test_1 = preg_replace("/<.*?>/","#",$newText33[0]);
			$test_2 = preg_replace("/<.*?>/","#",$newText33[1]);
			$test = $test_1.":".$test_2;
			$test = preg_replace("/##/","",$test);
			$test = preg_replace("/#/","",$test);
			$test = preg_replace("/:#/"," : ",$test);
			$test = preg_replace("/#/"," : ",$test);
			if(strpos(strip_tags($test),':') !== false) 
			{
				$Content = preg_replace("/&#[a-z0-9]+;/i"," ",$test);
				$Content = preg_replace("/#/"," ",$test);
				$new_array[]=str_replace("&nbsp;","",trim(strip_tags($Content)));
			}		
			$test="";
			$test1="";
			$i++;
		}
		
		if(!empty($expEmail[1]))
		{
			$newText = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)\s>/i",'<$1$2>', $expEmail[1]);
			$newText = preg_split( '/(<tbody>|<TBODY>)/', $newText);
			if(!empty($newText[1]))
			{
				$newText11 = preg_split( '/(<tr>|<TR>)/', $newText[1]);
			}
			else{
				$newText11 = preg_split( '/(<tr>|<TR>)/', $newText[0]);
			}
			
			$newText155 = preg_split( '/(<\/table>|<\/TABLE>)/', $newText11[2]);
			$newText44 = preg_split( '/(<\/td>|<\/TD>)/', $newText11[1]);
			$newText55 = preg_split( '/(<\/td>|<\/TD>)/', $newText155[0]);
			for($k=0;$k<count($newText44);$k++)
			{
				$test_1 = preg_replace("/<.*?>/","#",$newText44[$k]);
				$test_2 = preg_replace("/<.*?>/","#",$newText55[$k]);
				$test = $test_1.":".$test_2;
				$test = preg_replace("/##/","",$test);
				$test = preg_replace("/#/","",$test);
				$test = preg_replace("/:#/"," : ",$test);
				$test = preg_replace("/#/"," : ",$test);
				if(strpos(strip_tags($test),':') !== false) 
				{
					$Content = preg_replace("/&#[a-z0-9]+;/i"," ",$test);
					$Content = preg_replace("/#/"," ",$test);
					$new_array[]=str_replace("&nbsp;","",trim(strip_tags($Content)));
				}		
				$test="";
				$test1="";
			}
			
			if(!empty($newText155[1]))
			{
				$newText33 = preg_split( '/(<\/p>|<\/P>)/', $newText155[1]);
				$test_1 = preg_replace("/<.*?>/","#",$newText33[0]);
				$test_2 = preg_replace("/<.*?>/","#",$newText33[1]);
				$test = $test_1.":".$test_2;
				$test = preg_replace("/##/","",$test);
				$test = preg_replace("/#/","",$test);
				$test = preg_replace("/:#/"," : ",$test);
				$test = preg_replace("/#/"," : ",$test);
				if(strpos(strip_tags($test),':') !== false) 
				{
					$Content = preg_replace("/&#[a-z0-9]+;/i"," ",$test);
					$Content = preg_replace("/#/"," ",$test);
					$new_array[]=str_replace("&nbsp;","",trim(strip_tags($Content)));
				}		
				$test="";
				$test1="";
			}
		}
		foreach($new_array as $newdat)
		{
			list($pieceName,$GetVal)= preg_split("/[\:]+/", $newdat,2);
			if($this->_s_has_special_chars($GetVal))
			{
				$GetVal = $this->removeSpacialCharactersMann($GetVal); 
			}
			$GetVal = preg_replace("/&nbsp;/i"," ",$GetVal);
			$pieceName = str_replace(": :",":",$pieceName);
			$newLinet[] =str_replace(": :",":",trim(strip_tags(preg_replace('/\s+/','_', trim(strip_tags($pieceName))))).": ".trim(strip_tags(preg_replace('/\s+/',' ',strip_tags($GetVal)))));
		}
		return $newLinet;
	} 
	
	function plainMessageMotionCADDownload($email)
	{
		$newText = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)\s>/i",'<$1$2>', $email);
		$newText = preg_split( '/(<tbody>|<TBODY>)/', $newText);
		// PR($newText);
		if(!empty($newText[2]))
		{
			$newText11 = preg_split( '/(<tr>|<TR>)/', $newText[2]);
		}
		elseif(!empty($newText[1]))
		{
			$newText11 = preg_split( '/(<tr>|<TR>)/', $newText[1]);
		}
		else{
			$newText11 = preg_split( '/(<tr>|<TR>)/', $newText[0]);
		}
		$newText11 =  preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', $newText11[1]);
		$newText22 = preg_split( '/(<tr>|<TR>)/', $newText11);
		$new_array = array();
		foreach($newText22 as  $test1)
		{
			$newText33 = preg_split( '/(<\/td>|<\/TD>)/',$test1);
			$test_1 = preg_replace("/<.*?>/","#",$newText33[0]);
			$test_2 = preg_replace("/<.*?>/","#",$newText33[1]);
			$test = $test_1.":".$test_2;
			$test = preg_replace("/##/","",$test);
			$test = preg_replace("/#/","",$test);
			$test = preg_replace("/:#/"," : ",$test);
			$test = preg_replace("/#/"," : ",$test);
			if(strpos(strip_tags($test),':') !== false) 
			{
				$Content = preg_replace("/&#[a-z0-9]+;/i"," ",$test);
				$Content = preg_replace("/#/"," ",$test);
				$new_array[]=str_replace("&nbsp;","",trim(strip_tags($Content)));
			}		
			$test="";
			$test1="";
			$i++;
		}
		foreach($new_array as $newdat)
		{
			list($pieceName,$GetVal)= preg_split("/[\:]+/", $newdat,2);
			if($this->_s_has_special_chars($GetVal))
			{
				$GetVal = $this->removeSpacialCharactersMann($GetVal); 
			}
			$GetVal = preg_replace("/&nbsp;/i"," ",$GetVal);
			$pieceName = str_replace(": :",":",$pieceName);
			$newLinet[] =str_replace(": :",":",trim(strip_tags(preg_replace('/\s+/','_', trim(strip_tags($pieceName))))).": ".trim(strip_tags(preg_replace('/\s+/',' ',strip_tags($GetVal)))));
		}
		return $newLinet;
	} 
	
