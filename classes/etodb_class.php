<?
/**
 * Author:   Ernest Wojciuk
 * Web Site: www.imap.pl
 * Email:    ernest@moldo.pl
 * Comments: EMAIL TO DB
 */

class EMAIL_TO_DB extends commonClass{
	// for test
	public $prefix_table ;
	var $IMAP_host; #pop3 server
	var $IMAP_port; #pop3 server port
	var $IMAP_login;
	var $IMAP_pass;
	var $link;
	var $error = array();
	var $status;
	var $max_headers = 100;  #How much headers you want to retrive 'max' = all headers (
	var $filestore; 
	var $file_path = "outlook/Attachment/"; 
			#Where to write file attachments to /var/www/files/email/
			# win: c://wamp//www//emailtodb//files//
			#[full/path/to/attachment/store/(chmod777)]
	var $partsarray = array();
	var $msgid =1; 
	var $newid;
	var $logid;
	var $this_file_name = 'outlook_etodb_index_cron.php'; #If mode "html" 
	var $mode = 'cron'; #If script run from cron "mode" =  "cron" or ""; mode "html" is using if You run script from browser
	var $spam_folder = 1; #Folder where moving spam (ID from DB)
	var $file = array(); #File in multimart message
	function connect($host, $port, $login, $pass,$table_pre)
	{
		$this->prefix_table	=	$table_pre;
		$this->IMAP_host = $host;
		$this->IMAP_login = $login;
		$this->link = imap_open("{". $host . $port."}INBOX", $login, $pass);
		if($this->link) {
			$this->status = 'Connected';
		} else {
			$this->error[] = 'connect '.imap_last_error();
			$this->status = 'Not connected';
		}
		
		// echo "Test--> ".$this->status;
		// print_r($this->error); 
	}
 
	function connect1($host, $port, $login, $pass)
	{
		$this->IMAP_host = $host;
		$this->IMAP_login = $login;
		$this->link = imap_open("{". $host . $port."}INBOX", $login, $pass);
		$imap_obj = imap_check($this->link);
		if($this->link) {
			$this->status = 'Connected';
		} else {
			$this->error[] = imap_last_error();
			$this->status = 'Not connected';
		}
	}
 
 
	function set_path(){     
		#If You need set here more parameters
		# - recognise operating systems, or something      
		$path = $this->file_path;      
		return $path;
	}
 

	function set_filestore(){
		$dir = $this->dir_name();
		$path = $this->set_path();
		$this->filestore = $path.$dir;
	}	 
	/**
	* Get mailbox info
	*/
	function mailboxmsginfo(){   
		//$mailbox = imap_mailboxmsginfo($this->link); #It's wery slow
		$mailbox = imap_check($this->link);  
		if ($mailbox) {
			$mbox["Date"]    = $mailbox->Date;
			$mbox["Driver"]  = $mailbox->Driver;
			$mbox["Mailbox"] = $mailbox->Mailbox;
			$mbox["Messages"]= $this->num_message();
			$mbox["Recent"]  = $this->num_recent();
			$mbox["Unread"]  = $mailbox->Unread;
			$mbox["Deleted"] = $mailbox->Deleted;
			$mbox["Size"]    = $mailbox->Size;
		} else {
			$this->error[] = 'ner7'.imap_last_error();
		}    
		return $mbox;
	} 
	/**
	* Number of Total Emails
	*/
	function num_message(){
		return imap_num_msg($this->link);
	} 
	/**
	* Number of Recent Emails
	*/
	function num_recent(){
		return imap_num_recent($this->link);
	}  
	/**
	* Type and subtype message
	*/
	function msg_type_subtype($_type){
		
		if($_type > 0){
			switch($_type){
				case '0': $type = "text"; break;
				case '1': $type = "multipart"; break;
				case '2': $type = "message"; break;
				case '3': $type = "application"; break;
				case '4': $type = "audio"; break;
				case '5': $type = "image"; break;
				case '6': $type = "video"; break;
				case '7': $type = "other"; break;
			}
		}    
		return $type;
	}	
	/**
	* Flag message
	*/
	function email_flag()
	{
		switch ($char) {
			case 'S':
				if (strtolower($flag) == '\\seen') {
					$msg->is_seen = true;
				}
				break;
			case 'A':
				if (strtolower($flag) == '\\answered') {
					$msg->is_answered = true;
				}
				break;
			case 'D':
			   if (strtolower($flag) == '\\deleted') {
					$msg->is_deleted = true;
				}
				break;
			case 'F':
				if (strtolower($flag) == '\\flagged') {
					$msg->is_flagged = true;
				}
				break;
			case 'M':
				if (strtolower($flag) == '$mdnsent') {
					$msg->is_mdnsent = true;
				}
				break;
			default:
				break;
		}
	}
  
	/**
	* Parse e-mail structure
	*/
	function parsepart($p,$msgid,$i)
	{   
		$part=imap_fetchbody($this->link,$msgid,$i);
		#Multipart
		if ($p->type!=0){
			#if base64
			if ($p->encoding==3)$part=base64_decode($part);
			#if quoted printable
			if ($p->encoding==4)$part=quoted_printable_decode($part);
			#If binary or 8bit -we no need to decode     
			#body type (to do)
			switch($p->type) {
				case '5': # image
					$this->partsarray[$i][image] = array('filename'=>imag1,'string'=>$part, 'part_no'=>$i);
				break;
			}       
			#Get attachment
			$filename='';
			if (count($p->dparameters)>0){
				foreach ($p->dparameters as $dparam){
					if ((strtoupper($dparam->attribute)=='NAME') ||(strtoupper($dparam->attribute)=='FILENAME')) 
						$filename=$dparam->value;
				}
			}
			#If no filename
			if ($filename==''){
				if (count($p->parameters)>0){
					foreach ($p->parameters as $param){
						if ((strtoupper($param->attribute)=='NAME') ||(strtoupper($param->attribute)=='FILENAME')) 
							$filename=$param->value;
					}
				}
			}
			if ($filename!='' ){
				$this->partsarray[$i][attachment] = array('filename'=>$filename,'string'=>$part, 'encoding'=>$p->encoding, 'part_no'=>$i,'type'=>$p->type,'subtype'=>$p->subtype);      
			}
			#end if type!=0       
		}  
		#Text email
		else if($p->type==0){
			#decode text
			#if QUOTED-PRINTABLE
			if ($p->encoding==4) $part=quoted_printable_decode($part);
			#if base_64
			if ($p->encoding==3) $part=base64_decode($part);      
			#if plain text
			if (strtoupper($p->subtype)=='PLAIN')1;
			#if HTML
			else if (strtoupper($p->subtype)=='HTML')1;
				$this->partsarray[$i][text] = array('type'=>$p->subtype,'string'=>$part);
		}  
		#if subparts
		if (count($p->parts)>0){
			foreach ($p->parts as $pno=>$parr){
				$this->parsepart($parr,$this->msgid,($i.'.'.($pno+1)));           
			}
		}
		$this->error[] = 'ner6'.imap_last_error();
		return;
	}
  
	/**
	* All email headers
	*/
	function email_headers(){
		#$headers=imap_headers($this->link);
		if($this->max_headers == 'max'){
			$headers = imap_fetch_overview($this->link, "1:".$this->num_message(), 0);
		} 
		else {
			$headers = imap_fetch_overview($this->link, "1:$this->max_headers", 0);
		}
		if($this->max_headers == 'max') {
			$num_headers = count($headers);
		} 
		else {
			$count =  count($headers);
				if($this->max_headers >= $count){
					$num_headers = $count;
				} 
				else {
				  $num_headers = $this->max_headers;
				}
		}
		$this->error[] = 'ner5'.imap_last_error();
		$size=sizeof($headers);
		for($i=1; $i<=$size; $i++){
			$val=$headers[$i]; 
			$subject_s = (empty($val->subject)) ? '[No subject]' : $val->subject;
			$lp = $lp +1;
			imap_setflag_full($this->link,imap_uid($this->link,$i),'\\SEEN',SE_UID);
			$header=imap_headerinfo($this->link, $i, 80,80);
			if($val->seen == "0"  && $val->recent == "0") 
			{
				echo  '<b>'.$val->msgno . '-' . $subject_s . '-' . $val->from .'-'. $val->date."</b><br><hr>" ;
			}
			else 
			{
				echo  $val->msgno . '-' . $subject_s . '-' . $val->from .'-'. $val->date."<br><hr>" ;
			}
		}
	}

	/**
	* Get email
	*/
	function email_get(){
		$email = array();
		$this->set_filestore();    
		$header=imap_headerinfo($this->link, $this->msgid, 80,80);
		$from = $header->from;
		$udate= $header->udate;
		$to   = $header->to;
		$size = $header->Size;
    
		if ($header->Unseen == "U" || $header->Recent == "N") {
        	#Check is it multipart messsage
			$s = imap_fetchstructure($this->link,$this->msgid);
			if (count($s->parts)>0){
				foreach ($s->parts as $partno=>$partarr){
					#parse parts of email
					$this->parsepart($partarr,$this->msgid,$partno+1);
				}
			} else { #for not multipart messages
				#get body of message
				$text=imap_body($this->link,$this->msgid);
				#decode if quoted-printable
				if ($s->encoding==4) $text=quoted_printable_decode($text);
				if ($s->encoding==3) $text=base64_decode($text);
				if (strtoupper($s->subtype)=='PLAIN') $text=$text;
				if (strtoupper($s->subtype)=='HTML') $text=$text;
				$this->partsarray['not multipart'][text]=array('type'=>$s->subtype,'string'=>$text);
			}
			if(is_array($from)){
				foreach ($from as $id => $object) {
					$fromname = $object->personal;
					$fromaddress = $object->mailbox . "@" . $object->host;
				}
			}    
			if(is_array($to)){
				foreach ($from as $id => $object) {
					$toaddress = $object->mailbox . "@" . $object->host;
				}
			}
			$this->error[] = 'ner4'.imap_last_error();
			
			$email['CHARSET']    = $charset;
			$email['SUBJECT']    = $this->mimie_text_decode($header->Subject);
			$email['FROM_NAME']  = $this->mimie_text_decode($fromname);
			$email['FROM_EMAIL'] = $fromaddress;
			$email['TO_EMAIL']   = $toaddress;
			$newdate = explode('-',$header->date);
			// $email['DATE']       = date("Y-m-d H:i:s",strtotime($header->date));
			$email['DATE']       = date("Y-m-d H:i:s",strtotime($newdate[0]));
			$email['SIZE']       = $size;
			#SECTION - FLAGS
			$email['FLAG_RECENT']  = $header->Recent;
			$email['FLAG_UNSEEN']  = $header->Unseen;
			$email['FLAG_ANSWERED']= $header->Answered;
			$email['FLAG_DELETED'] = $header->Deleted;
			$email['FLAG_DRAFT']   = $header->Draft;
			$email['FLAG_FLAGGED'] = $header->Flagged;
		}
		return $email;
	}
  
	function mimie_text_decode($string){
		$string = htmlspecialchars(chop($string));
		$elements = imap_mime_header_decode($string);
		if(is_array($elements)){
			for ($i=0; $i<count($elements); $i++) {
				$charset = $elements[$i]->charset;
				$txt .= $elements[$i]->text;
			}
		} else {
			$txt = $string;
		}
		if($txt == ''){
			$txt = 'No_name';
		}
		if($charset == 'us-ascii'){
			//$txt = $this->charset_decode_us_ascii ($txt);
		}
		return $txt;
	}

	/**
	* Save messages on local disc
	*/ 
	/* 
	function save_files($filename, $part){
		$fp=fopen($this->filestore.$filename,"w+");
		fwrite($fp,$part);
		fclose($fp);
		chown($this->filestore.$filename, 'apache');
	}
	*/
   /**
	* Set flags
	*/ 
	function email_setflag(){
		imap_setflag_full($this->link, "2,5","\\Seen \\Flagged"); 
		$this->error[] = 'ner3'.imap_last_error();
	}
	/**
	* Mark a message for deletion 
	*/ 
	function email_delete(){
		imap_delete($this->link, $this->msgid); 
		$this->error[] = 'ner2 '.$this->msgid.'--'.imap_last_error();
	}
  
	/**
	* Delete marked messages 
	*/ 
	function email_expunge(){
		imap_expunge($this->link);
		$this->error[] = 'ner del '.imap_last_error();
	}  
	/**
	* Close IMAP connection
	*/ 
	function close(){
		imap_close($this->link);   
	}
 
	function listmailbox(){
		$list = imap_list($this->link, "{".$this->IMAP_host."}", "*");
		if (is_array($list)) {
			return $list;
		} else {
			$this->error =  "imap_list failed: " . imap_last_error() . "\n";
		}
		return array();
	}
  
	/*******************************************************************************
	 *                                 SPAM  DETECTION                               
	 ******************************************************************************/

	function spam_detect(){
		$email = array();
		$id = $this->newid; #ID email in DB
		$execute = $this->getQuery("SELECT ID, IDEmail, EmailFrom, EmailFromP, EmailTo, Subject, Message, Message_html FROM ".$this->prefix_table.EMAIL_OUTLOOKS." WHERE ID='".$id."'");
		$row = $this->getAssocArray($execute);
		$ID = $row['ID'];
		$email['Email']       = $row['EmailFrom'];
		$email['Subject']     = $row['Subject'];
		$email['Text']        = $row['Message'];
		$email['Text_HTML']   = $row['Message_html'];
		if($this->check_blacklist($email['Email'])){
			$this->update_folder($id, $this->spam_folder);  
		}
		if($this->check_words($email['Subject'])){
			$this->update_folder($id, $this->spam_folder);  
		}
		if($this->check_words($email['Text'])){
			$this->update_folder($id, $this->spam_folder);  
		}
		if($this->check_words($email['Text_HTML'])){
			$this->update_folder($id, $this->spam_folder);  
		}
		$this->error[] = 'ner1'.imap_last_error();
	}
  
  
	function check_blacklist($email){
		#spam - emails 
		$execute = $this->getQuery("SELECT Email FROM ".$this->prefix_table.EMAIL_LIST." WHERE Email='".$this->sanitizeData($email)."' AND Type='B'");
		$row = $this->getAssocArray($execute);
		$e_mail = $row['Email'];
		if($e_mail == $email){
			return 1;
		} 
		else {
			return 0;
		}
	}
  
	function check_words($string){
		#spam - words
		$string = strtolower($string);
		$execute = $this->getQuery("SELECT Word FROM ".$this->prefix_table.EMAIL_WORD." ");
		while($row = $this->getAssocArray($execute)){
			$word = strtolower($row['Word']);
			if (eregi($word, $string)) {
				return 1;
			}
		}
	}
	/*******************************************************************************
	 *                                 DB FUNCTIONS                                 
	 ******************************************************************************/
	/**
	* Add email to DB
	*/
	function db_add_message($email){
		
		$emal_subject ='';
		
		$SubArr = explode("Lead from",$email['SUBJECT'],2);
		//PR($SubArr);
		//$FwCharArr	= array(":", "FW", "Fw");
		$emal_subject = strip_tags(preg_replace('/\s+/',' ', $SubArr[0]));		
		$emal_subject = $this->sanitizeData($emal_subject);
		
		if(strstr(strtolower($emal_subject),strtolower("New submission on Contact Us New")))
		{
			$emal_subject= "New submission on Contact Us New";
		}
		elseif(strstr(strtolower($emal_subject),strtolower("New Request for Quote submission from")))
		{
			$emal_subject= "New Request for Quote submission from";
		}
		echo "<br><br>select Subject,byPassMDB from ".$this->prefix_table.EMAIL_SUBJECT." where Subject like '%$emal_subject%'";
		
		$selectSubject = $this->getQuery("select Subject,byPassMDB from ".$this->prefix_table.EMAIL_SUBJECT." where Subject like '%$emal_subject%'");
		if($this->getNumRows($selectSubject)>0)
		{
			while( $es_row = $this->getAssocArray($selectSubject) ){
				
				$byPassMDB = $es_row['byPassMDB']=="yes" ? $es_row['byPassMDB'] : "no";
				
				$addQuery = "INSERT INTO ".$this->prefix_table.EMAIL_OUTLOOKS." (IDEmail, EmailFrom, EmailFromP, EmailTo, DateE, DateDb, Subject, MsgSize,byPassMDB) VALUES ('".$message_id."','".$email['FROM_EMAIL']."','".$this->sanitizeData(strip_tags($email['FROM_NAME']))."','".$this->sanitizeData(strip_tags($email['TO_EMAIL']))."','".$email['DATE']."','".date("Y-m-d H:i:s")."',\"".$this->sanitizeData($email['SUBJECT'])."\",'".$email["SIZE"]."','{$byPassMDB}')";
				$execute = $this->getQuery($addQuery);   
				$execute = $this->getQuery("select LAST_INSERT_ID() as UID");
				$row = $this->getAssocArray($execute);
				$this->newid = $row["UID"];
				return 1;
		
			}
		}
	}
	/**
	* Add email to DB
	*/
	function db_update_message($msg, $type= 'PLAIN')
	{
		$msg = trim($msg);
		$plainText = preg_replace("/[\r\n]+/", "\n", $msg);
		$plainText = str_replace(":\n",": ",$plainText);
		if($type == 'PLAIN') 
			$execute = $this->getQuery("UPDATE ".$this->prefix_table.EMAIL_OUTLOOKS." SET Message='".$this->sanitizeData($plainText)."' WHERE ID= '".$this->newid."'");
		if($type == 'HTML')  
			$execute = $this->getQuery("UPDATE ".$this->prefix_table.EMAIL_OUTLOOKS." SET Message_html='".$this->sanitizeData($msg)."' WHERE ID= '".$this->newid."'");
	}
  
	/**
	* Insert progress log
	*/
	function add_db_log($email, $info)
	{
		$execute = $this->getQuery("INSERT INTO ".$this->prefix_table.EMAIL_LOG." (IDemail, Email, Info, FSize, Date_start, Status) VALUES('".$this->newid."','".$email['FROM_EMAIL']."','".$this->sanitizeData(strip_tags($info))."','".$email["SIZE"]."','".date("Y-m-d H:i:s")."','2')");
		$execute = $this->getQuery("select LAST_INSERT_ID() as UID");
		$row = $this->getAssocArray($execute);
		$this->logid = $row['UID'];	 
		return  $this->logid;
	}
  
	/**
	* Set folder
	*/
	function update_folder($id, $folder){
		$execute = $this->getQuery("UPDATE ".$this->prefix_table.EMAIL_OUTLOOKS." SET Type = '".$this->sanitizeData($folder)."' WHERE ID = '".$id."'");
	}
  
	/**
	* Update progress log
	*/
	function update_db_log($info, $id){
		$execute = $this->getQuery("UPDATE ".$this->prefix_table.EMAIL_LOG."  SET Status = '1', Info='".$this->sanitizeData(strip_tags($info))."', Date_finish = '".date("Y-m-d H:i:s")."' WHERE IDlog = '".$id."'");
	}
  
  
	/**
	* Read log from DB
	*/
	function db_read_log()
	{
		$email = array();
		$execute = $this->getQuery("SELECT IDlog, IDemail, Email, Info, FSize, Date_start, Date_finish, Status FROM ".$this->prefix_table.EMAIL_LOG." ORDER BY Date_finish DESC LIMIT 100");
		while($row = $this->getAssocArray($execute)){
			$ID = $row['IDlog'];
			$email[$ID]['IDemail']     = $row['IDemail'];
			$email[$ID]['Email']       = $row['Email'];
			$email[$ID]['Info']        = $row['Info'];
			$email[$ID]['Size']        = $row['FSize'];
			$email[$ID]['Date_start']  = $row['Date_start'];
			$email[$ID]['Date_finish'] = $row['Date_finish'];
		}
		return $email;
	}  
	/**
	* Read emails from DB
	*/
	function db_read_emails(){
		if (!isset($db)) $db = new DB_WL;
		$email = array();
		$execute = $this->getQuery("SELECT ID, IDEmail, EmailFrom, EmailFromP, EmailTo, DateE, DateDb, Subject, Message, Message_html, MsgSize FROM ".$this->prefix_table.EMAIL_OUTLOOKS." ORDER BY ID DESC LIMIT 25");
		while($row = $this->getAssocArray($execute)){
			$ID = $row['ID'];
			$email[$ID]['Email']     = $row['EmailFrom'];
			$email[$ID]['EmailName'] = $row['EmailFrom'];
			$email[$ID]['Subject']   = $row['Subject'];
			$email[$ID]['Date']      = $row['DateE'];
			$email[$ID]['Size']      = $row['MsgSize'];
		}
		return $email;
	}
  
	function dir_name() {
		$year  = date('Y');
		$month = date('m');
		$dir_n = $year . "_" . $month;
		//echo $this->set_path();
		if (is_dir($this->set_path() . $dir_n)) {
			return $dir_n . '/';
		} 
		else {
			mkdir($this->set_path() . $dir_n, 0777);
			return $dir_n . '/';
		}
	}
  
	function do_action(){ 
		$getMSG = 0;
		if($this->num_message() >= 1) {
			$getMSG = $this->num_message();
			$alMSG = 1;
			while($getMSG >= $alMSG){			
				if($this->msgid <= 0) {
					$this->msgid = 1;
				} else {
					
				}  
				#Get first message
				$email = $this->email_get();		   
				#Get store dir
				$dir = $this->dir_name();			
				#Insert message to db 
				$ismsgdb = $this->db_add_message($email);
				if($ismsgdb == 1)
				{
					$id_log = $this->add_db_log($email, 'Copy e-mail - start ');		
					foreach($this->partsarray as $part){
						if($part[text][type] == 'HTML'){
							#$message_HTML = $part[text][string];
							$this->db_update_message($part[text][string], $type= 'HTML');
						}elseif($part[text][type] == 'PLAIN'){
							$message_PLAIN = $part[text][string];
							$this->db_update_message($part[text][string], $type= 'PLAIN');
						}elseif($part[attachment]){
							#Save files(attachments) on local disc       
							// $message_ATTACH[] = $part[attachment];
							foreach(array($part[attachment]) as $attach){
								$attach[filename] = $this->mimie_text_decode($attach[filename]);
								$attach[filename] = preg_replace('/[^a-z0-9_\-\.]/i', '_', $attach[filename]);
								$this->add_db_log($email, 'Start coping file:"'.strip_tags($attach[filename]).'"');            
								//$this->save_files($this->newid.$attach[filename], $attach[string]);
								$filename =  $dir.$this->newid.$attach[filename];
								$this->update_db_log('<b>'.$filename.'</b>Finish coping: "'.strip_tags($attach[filename]).'"', $this->logid);
							}
						}
						elseif($part[image]){
							#Save files(attachments) on local disc 
							$message_IMAGE[] = $part[image];        
							foreach($message_IMAGE as $image){
								$image[filename] = $this->mimie_text_decode($image[filename]);
								$image[filename] = preg_replace('/[^a-z0-9_\-\.]/i', '_', $image[filename]);
								$this->add_db_log($email, 'Start coping file: "'.strip_tags($image[filename]).'"');
								// $this->save_files($this->newid.$image[filename], $image[string]);
								$filename =  $dir.$this->newid.$image[filename];
								$this->update_db_log('<b>'.$filename.'</b>Finish coping:"'.strip_tags($image[filename]).'"', $this->logid);
							}
						}			
					}
				}
				//$this->spam_detect();
				//$this->email_setflag(); 
				//$this->email_delete();  
				if($ismsgdb == 1)
				{
					$this->update_db_log('Finish coping', $id_log);
				}
				
				if($email <> ''){
					unset($this->partsarray);
					if($this->mode == 'html') {
						echo "<meta http-equiv=\"refresh\" content=\"2; url=".$this->this_file_name."?msgid=".$this->msgid."\">";
					}        
				}
				$alMSG=$alMSG+1;
				$this->msgid = $alMSG;//$this->msgid+1;
			} 
			//$this->email_expunge(); 
			// echo "-------- test "; print_r($this->error);
		} 
		else {
			if($this->mode == 'html') {
				echo "<meta http-equiv=\"refresh\" content=\"10; url=".$this->this_file_name."?msgid=".$this->msgid."\">";
			}
		}		
	}
  
	function plainMessage($email)
	{
		//$explodeArr = explode('you have received a new inquiry from your website',$email,2);
		//$newText = preg_split( '/(<table>|<TABLE>)/', $email);
		// $newText11 = preg_split( '/(<tr>|<TR>)/', $email);
		//$newText = preg_split( '/(<tbody>|<TBODY>)/', $newText);
		//$newText = preg_split( '(<br />)', $email);
		
		$newText1 = array();
		$newText11 = preg_split('/(<table.*?>)/', $email);
		for($k=0;$k<count($newText11);$k++)
		{
			$arrMerge =  preg_split('/(<strong.*?>)/', $newText11[$k]);
			$newText1 = array_merge($newText1,$arrMerge);
		}		
		// echo "<pre>";
		// print_r($newText11);echo "<br/>--";
		// echo "</pre>";
		// die;
		
		// $newText = explode('</tbody>',$newText[1]);
		//$newText11 = preg_split( '/(<tr>|<TR>)/', $newText[1]);
		// print_r($newText11[16]);echo "<br/>qqqq--";		
		$temp=0;
		// echo "<br/>hiii -- ".count($newText11)."<br/>";
		/*
		 PR($newText1);
		 die;
		if(count($newText11)<= 1)
		{
			$newText11 = preg_split( '/(<td>|<BR>)/', $email);
			$temp =1;
		}	
		*/	
		$new_array = array();
		$newLinet  = array();
		//echo $temp;
		$i=0;
		
		
		foreach($newText1 as  $test)
		{
			/* $newText1 = preg_split( '/(<td.*?>|<TD.*?>)/', $test);
			foreach($newText1 as  $test1)
			{ */

				//PR($test);	 		
				
				$test = preg_replace("/<p.*?>/",":",$test, 1); 
				//echo "12--- ".$test.'<br/>';
				//PR($test1);
				//PR("<hr/>");	 		
				$test= str_replace('?',':',$test);
				
				//echo "13--- ".$test.'<br/>';
				$test = preg_replace("/#/"," ",$test);
				$test = preg_replace("/::/"," : ",$test);
				// echo $i;
				//echo "12-- ".$test.'<br/>';
				if(strpos(strip_tags($test),':')!== false) 
				{
					$Content = preg_replace("/&[a-z0-9]+;/i"," ",$test);
					$new_array[] = trim(strip_tags($Content));
				}		
				$test="";
				$test1="";
				$i++;
			/* } */
		}
		
		foreach($new_array as $newdat)
		{
			list($pieceName,$GetVal)= preg_split("/[\:]+/", $newdat ,2);
			if($temp == 1)
			{
				$newLinet[] = trim(strip_tags(preg_replace('/\s+/','_', trim($pieceName)))).": ".trim(strip_tags(preg_replace('/\s+/',' ',$GetVal)));
			}
			else
			{
				$newLinet[] = trim(strip_tags(preg_replace('/\s+/','_', trim($pieceName)))).": ".trim(strip_tags(preg_replace('/\s+/',' ',$GetVal)));
			}
		} 	
		// echo "<br/>lps--- <pre>";
		// print_r($newLinet); 
		// exit; 
		 
		return $newLinet;
	}
	
	function plainOrderNotificationMessage($email)
	{	
		$newText = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)\s>/i",'<$1$2>', $email);			
		$newText11 = preg_split( '/(<tbody.*?>|<TBODY.*?>)/', $newText);
		
		$newText1 = preg_replace("/<b\s\/>/is", "<b>", $newText11[0]);
		$newText11 = preg_split('/(<b.*?>|<B.*?>)/', $newText1);

		$new_array=$newLinet=array();
		$i=0;
		foreach($newText11 as  $test1)
		{
			$test = str_replace("</span></strong>",":</span></strong>",$test1);
			$test = str_replace("::",":",$test);
			// echo $test."<br/>";
			//$test = preg_replace("/<span\s\/>/is", "<span>", $test1);
			$test = preg_replace("/<.*?>/","#",$test);
			$test = preg_replace("/#&nbsp;/i","#",$test);
			$test = preg_replace("/##/","",$test);
			// echo "<br/>-->1".$test;
			$test = preg_replace("/#/","",$test);
			// echo "<br/>-->2".$test;
			$test = preg_replace("/:#/"," : ",$test);
			// echo "<br/>-->3".$test;
			$test = preg_replace("/#/"," : ",$test);
			// echo "<br/>-->4".$test;			
			$test = preg_replace("/&nbsp;/i"," ",$test);
			$test = preg_replace("/&160;/i"," ",$test);
			// $Content = preg_replace("/&#[a-z0-9]+;/i"," ",$test);
			// $Content = preg_replace("/#/"," ",$test);
			// $new_array[]=trim(strip_tags($Content));
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
		
		if(strstr($email,"Billing Address"))
		{
			$explodeAdd = explode("Billing Address",$email);
			if(!empty($explodeAdd[1]))
			{
				$explodePhone = explode("Billing Phone Number",$explodeAdd[1]);
				$new_array[]= "Billing_Address:".$explodePhone[0];
			}
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
		return $newLinet;
	}
	
	function plainContactFormMessage($email)
	{	
		$newText = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)\s>/i",'<$1$2>', $email);			
		// $newText11 = preg_split( '/(bgcolor="#EAF2FA")/', $newText);
		$newText1 = preg_replace("/(<br.*?>|<BR.*?>)/", "<br>", $newText);
		$newText11 = preg_split('/(<br.*?>|<BR.*?>)/', $newText1);
		$new_array =array();
		$newLinet=array();
		// PR($newText11);
		$i=0;
		foreach($newText11 as $test1)
		{
			$test = preg_replace("/<.*?>/","#",$test1);
			$test = preg_replace("/#&nbsp;/i","#",$test);
			$test = preg_replace("/##/","",$test);
			$test = preg_replace("/#/","",$test);
			$test = preg_replace("/:#/"," : ",$test);
			$test = preg_replace("/#/"," : ",$test);
			$test = preg_replace("/&nbsp;/i"," ",$test);
			$test = preg_replace("/&160;/i"," ",$test);
			// $Content = preg_replace("/&#[a-z0-9]+;/i"," ",$test);
			// $Content = preg_replace("/#/"," ",$test);
			// $new_array[]=trim(strip_tags($Content));
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
				$GetVal = str_replace($arr,"",$GetVal);
				$GetVal = str_replace("\n","@BR@",$GetVal);
				if($temp == 1)
				{
					$newLinet[] = trim(strip_tags(preg_replace('/\s+/','_', trim($pieceName)))).": ".trim(strip_tags(preg_replace('/\s+/',' ',$GetVal),"<br>"));
				}
				else
				{
					$newLinet[] = trim(strip_tags(preg_replace('/\s+/','_', trim($pieceName)))).": ".trim(strip_tags(preg_replace('/\s+/',' ',$GetVal),"<br>"));
				}
			}
		}
		return $newLinet;
	}
	
	function plainEquipmentMessage($email)
	{	
/* 		$dom = new DOMDocument();
		$dom->loadHTML($email);
		$x = new DOMXpath($dom);
		foreach($x->query('//tr') as $td){
			echo $td->C14N()."<hr/>";
			//if just need the text use:
			//echo $td->textContent;
		}
		die;
 */		
		
		$exploadeText = explode( 'id="distributor"', $email);
		if(count($exploadeText)==1)
		{
			$exploadeText = explode( "id='distributor'", $email);
		}
		
		$exploadeTextN = explode( 'class="mainbody"', $exploadeText[1]);
		if(count($exploadeTextN)==1)
		{
			$exploadeTextN = explode( "class='mainbody'", $exploadeText[1]);
		}
		
		$newText = "<table id=\"distributor\" ".$exploadeTextN[0];
		$newText = preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)\s>/i",'<$1$2>', $newText);		
			
		$newText12 = preg_split( '/(<tbody.*?>|<TBODY.*?>)/', $newText); 
		
		/* $newText11 = preg_split( '/(<table.*?>|<TABLE.*?>)/', $newText); */
		// echo "<br/><br/>--->".count($newText11); A new lead has been entered via HubSpot
		$i=0;
		$new_array =array();
		$newLinet=array();
		$temp=0;
		for($k=0;$k<count($newText12);$k++)
		{
			$newText1 = preg_replace("/<tr\s\/>/is", "<tr>", $newText12[$k]);
			$newText11 = preg_split('/(<tr.*?>|<TR.*?>)/', $newText1);
			$temp =1;
			foreach($newText11 as  $test1)
			{
				$test = str_replace("</span></strong>",":</span></strong>",$test1);
				$test = str_replace("::",":",$test);

				$test = preg_replace("/<.*?>/","#",$test);
				$test = preg_replace("/#&nbsp;/i","#",$test);
				$test = preg_replace("/##/","",$test);
				$test = preg_replace("/#/","",$test);
				$test = preg_replace("/:#/"," : ",$test);
				$test = preg_replace("/#/"," : ",$test);
				$test = preg_replace("/&nbsp;/i"," ",$test);
				$test = preg_replace("/&160;/i"," ",$test);
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
		return $newLinet;
	}
	
	//function for check date are compertable to mysql or not 	
		
	function checkEmpty($val,$fill='')
	{
		if(!empty($val))
		{
			$out = $this->sanitizeData( $val ) ;
		}
		else
		{
			$out = $fill;
		}
		return $out;
	}

	
	function fetchRefinedLeads(){
		global $table_pre;	

		$outlookSql = $this->getQuery("select outlook_email,outlook_password,outlook_host,outlook_port from ".$this->prefix_table.EMAIL_ADDRESS." where outlook_email != '' and outlook_password != ''");
		while($res = $this->getNumArray($outlookSql)) 
		{
			$outlook_email    	= $res[0];
			$outlook_password 	= $res[1];
			$serverHost 		= $res[2];
			$serverPort 		= $res[3];
			$connection 		= $this->connect($serverHost,$serverPort,$outlook_email,$outlook_password,$table_pre);
			$this->do_action();
		}
	}
	
	function checkEmptyContent($val)
	{
		$arr = array("-","_"," ",",","(",")","!","@","#","$","%","^","&","*","+","=","`","[","]","'",";",",",".","/","{","}","|","\"",":","<",">","?","~","\\");
		$checkEmptyVal = str_replace($arr,"",$val);
		if(empty($checkEmptyVal))
		{
			return "";
		}
		return $val;
	}

	function refineValue($val)
	{
		$val =str_replace(","," " , trim($val));
		$val =str_replace("\n"," " , $val );
		$val =preg_replace("/\r/", "<br>" ,$val);
		$val =addslashes(html_entity_decode(preg_replace("/\n/", "<br>" ,$val)));
		return $val;
	}

	function getCountry($country)
	{
		list($matched,$mcountry) = $this->mapcountry($country);
		if($matched==1)
		{
			return $mcountry;
		}else{
			$res 	=	$this->getQuery("select country from ".$this->prefix_table.EMAIL_COUNTRY_CODE." where (country='{$country}' OR cod='{$country}') and country<>'' and country IS NOT NULL ");
			if($this->getNumRows($res)>0)
			{
				$row = $this->getAssocArray($res);
				return @trim($row['country']);
			}
			else{
				return @trim($country);
			}
		}
	}
	
	function getCountyCityStatebyZip($country,$zip)
	{
		if((strtoupper($country)=="USA" OR strtoupper($country)=="MEXICO" OR strtoupper($country)=="BRAZIL" OR strtoupper($country)=="INDIA") && !empty($zip))
		{
			$res 	=	$this->getQuery("select City,State,County from ".$this->prefix_table.LAT_LONG." where Country='".@trim($country)."' and ZipCode='".@trim($zip)."' ");
			if($this->getNumRows($res)>0)
			{
				$row = $this->getAssocArray($res);
				return array(1,@trim($row['City']),@trim($row['State']),@trim($row['County']));
			}
		}
		elseif((strtoupper($country)=="CANADA" OR strtoupper($country)=="COLOMBIA") && !empty($zip))
		{
			$res 	=	$this->getQuery("select City,StateFull as State,County from ".$this->prefix_table.LAT_LONG." where Country='".@trim($country)."' and ZipCode='".substr(@trim($zip),0,3)."' ");
			if($this->getNumRows($res)>0)
			{
				$row = $this->getAssocArray($res);
				return array(1,@trim($row['City']),@trim($row['State']),@trim($row['County']));
			}
		}
		return array(0,"","","");
	}
	
	function getActualState($country,$state)
	{
		$res 	=	$this->getQuery("select if(Country='USA',State,StateFull) as State from ".$this->prefix_table.LAT_LONG." where Country='{$country}' and (StateFull='".@trim($state)."' OR State='".@trim($state)."') ");
		if($this->getNumRows($res)>0)
		{
			$row = $this->getAssocArray($res);
			if(!empty($row['State']))
			{
				return array(1,@trim($row['State']));
			}
		}
		return array(0,$state);
	}
	
	function getCountyByCityAndState($country,$city,$state)
	{
		if(strtoupper($country)=="USA" && !empty($city) && !empty($state))
		{
			$res 	=	$this->getQuery("select County from ".$this->prefix_table.LAT_LONG." where Country='".@trim($country)."' and City='".@trim($city)."' and State='".@trim($state)."'  GROUP BY County ");
			if($this->getNumRows($res)==1)
			{
				$row = $this->getAssocArray($res);
				return array(1,@trim($row['County']));
			}
		}
		return array(0,"");
	}	
	
	function updateCriteriaStatus($ID,$status)
	{
		$updatesql1 = $this->getQuery("update ".$this->prefix_table.EMAIL_OUTLOOKS." set criteriastatus='".$status."' where ID='".$ID."'");
	}
	
	function getRefinedLeads($system)
	{
		$res 	=	$this->getQuery("select a.*,b.BrandName,a.Product as ProductName from ".$this->prefix_table.EMAIL_DOWNLOAD." as a LEFT JOIN ".$this->prefix_table.BRANDS." as b ON a.Brand=b.ID where a.IsActiveDB='1' and a.byPassMDB='yes' group by a.ID order by ID desc LIMIT 5");
		$resultjson['result'] = $resultjson['byPassMdb'] = array();
		$resultjson['downloadfixedval'] = "";
		$resultjson['byPassMdbDownloadfixedval'] = "";
		$resultjson['resultDownloadfixedval'] = "";
		if($this->getNumRows($res)>0)
		{
			while($row = $this->getAssocArray($res))
			{
				if($row['byPassMDB']=="yes")
				{
					$resultjson['byPassMdb'][]		=	$row;
					$resultjson['byPassMdbDownloadfixedval']	.=	$row['ID'].",";
				}
				else{
					$resultjson['result'][]			=	$row;
					$resultjson['resultDownloadfixedval']		.=	$row['ID'].",";
				}
				$resultjson['downloadfixedval'] 	.=	$row['ID'].",";
			}
			$resultjson['downloadfixedval']			=	substr($resultjson['downloadfixedval'],0,-1);
			$resultjson['byPassMdbDownloadfixedval']=	substr($resultjson['byPassMdbDownloadfixedval'],0,-1);
			$resultjson['resultDownloadfixedval']=	substr($resultjson['resultDownloadfixedval'],0,-1);
		}
		$resultjson['reviewQProID']		=	$system['reviewQueProjectID'];
		// $resultjson['currentID'] 		=	$currentID;
		$resultjson['user_id'] 			= 	'1';
		// $resultjson['projID'] 			= 	$project_db;
		$resultjson['Leadsystem'] 		= 	'3';
		$resultjson['downloaded'] 		= 	'1';
		$resultjson['Temp_date'] 		= 	date('Y-m-d');
		$resultjson['reviewQueProjectID'] 			= $system['reviewQueProjectID'];
		$resultjson['reviewQueProjectShortName'] 	= $system['reviewQueProjectShortName'];
		$resultjson['table_pre'] 		= $system['table_pre'];
		$resultjson['host'] 			= $system['host'];
		$resultjson['user'] 			= $system['user'];
		$resultjson['pass'] 			= $system['pass'];
		$resultjson['dbName'] 			= $system['dbName'];
		$resultjson['outlook_email_update'] 	= $system['outlook_email_update'];
		
		return $resultjson;
	}

	function updateRefinedLeads($ids)
	{
		if(!empty($ids))
		{
			$this->getQuery("update ".$this->prefix_table.EMAIL_DOWNLOAD." SET IsActive='0',IsActiveDB='0' WHERE ID in ({$ids})");
		}
	}

	function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}

	function mapcountry($country)
	{
		$res 	=	$this->getQuery("select ActualCountry from ".$this->prefix_table.EMAIL_COUNTRY_MAP." where (MatchedCountry='{$country}' OR ActualCountry='{$country}') and ActualCountry<>'' and ActualCountry IS NOT NULL ");
		if($this->getNumRows($res)>0)
		{
			$row = $this->getAssocArray($res);
			return array(1,@trim($row['ActualCountry']));
		}
		else{
			return array(0,@trim($country));
		}
	}

	
	function refineLeads()
	{
		$sqlMsg = $this->getQuery("select FormFieldName,DbFieldName,type from ".$this->prefix_table.EMAIL_CRITERIA." where DbFieldName is NOT NULL and DbFieldName<>'' and IsActive='1' order by DbFieldName");
		$FormFieldName = array();
		if($this->getNumRows($sqlMsg)>0){
			while($rowMsg=$this->getAssocArray($sqlMsg))
			{
				if(!empty($rowMsg['FormFieldName'])){
					$fieldName = trim(strtolower($rowMsg['FormFieldName']));
					$fieldName = trim(str_replace(':','',$fieldName));
					$FormFieldName[$fieldName] = $rowMsg;
				}
			}
		}
		
		$res = $this->getQuery("select ID,Message,Subject,DateE,DateDb,Message_html from ".$this->prefix_table.EMAIL_OUTLOOKS." where criteriastatus='0' ");
		$FwCharArr				= array("*",":", "FW", "Fw");
		$CharArr          		= array(":", "?", "(", ")","'",",");
		$CharArr1          		= array(",", " Â ", "Â");
		$CharArr2          		= array(" Â ","Â"); // city/state/country/address where all city state in one
	
		while($row = $this->getAssocArray($res))
		{
			$perticularCol1= $TableValue = $TableValue1 = $Message1 = array();
			$creat_feelds = $insert_fields = "";

			$Subject 	= @trim(str_replace($FwCharArr,'',$row['Subject']));
			
			$row['Message_html'] = str_replace("&#43;","+",$row['Message_html']);
			$row['Message'] 	= str_replace("&#43;","+",$row['Message']);
			$sub1 = strtoupper("New Request for Quote submission from");
			
			
			/* if(!empty($row['Message_html']) && strstr(strtoupper($Subject),$sub1))
			{
				$Message1 	= $this->plainOrderNotificationMessage($row['Message_html']);
			}
			elseif(!empty($row['Message_html']))
			{
				$Message1 	= $this->plainContactFormMessage($row['Message_html']);
			}
			elseif(!empty($row['Message']))
			{
				$Message3 	= str_replace('>>','',$row['Message']);
				$Message2 = $Message3.'<br/> Testing:';
				
			}
			else
			{ 
				$Message1 	= $this->plainMessage($row['Message_html']);
			} */
			if(!empty($row['Message_html']))
			{
				$Message1 	= $this->plainMessage($row['Message_html']);
			}
			else
			{
				$Message2 	= str_replace('>>','',$row['Message']);
			}
			$perticularCol = array();
			$DateE		= $row['DateE'];
			$DateDb		= $row['DateDb'];
			if(!empty($Message1))
			{
				$ll=0;
				foreach($Message1 as $Message)
				{
					$Message = str_replace('â€‰','',$Message);
					list($pieceName,$GetVal)= preg_split("/[\:]+/", $Message,2,PREG_SPLIT_NO_EMPTY);
					$pieceName        		= str_replace($CharArr, "", $pieceName);
					$pieceName = trim(str_replace('#','',$pieceName));
					
					if(array_key_exists(@strtolower($pieceName),$FormFieldName)){
						//***********if criteria match then update status************/
						$criteriastatus = $criteriastatusFlash = '2';
						$DateEnter = explode(' ',$DateE);
						$SubmitDate = $DateEnter[0];
						
						$findwholetext          = $Message;//$matches[0][0];
						$GetVal        			= str_replace($CharArr1, "", $GetVal);
						$fieldname[]     		= str_replace(" ","_",$pieceName);
						$columnval[]      		= @trim($GetVal);
						// $columnvalForCSV[]      = "'".@trim($GetVal)."'";
						
						$pieceNameS  = trim($pieceName);
						
						$sql_pereticularFornat =$this->getQuery("select a.ID,a.FieldName from ".$this->prefix_table.EMAIL_FORMFIELDS." as a left join ".$this->prefix_table.EMAIL_CRITERIA." as b on a.ID=b.DbFieldName where b.FormFieldName='".trim($pieceNameS)."' and b.IsActive='1'"); 
						$countrow = $this->getNumRows($sql_pereticularFornat);
						while($Res_pereticularFornat = $this->getNumArray($sql_pereticularFornat))
						{
							$perticularCol[]	= trim($Res_pereticularFornat['1']);
							$perticularCol1[]	= trim($GetVal);						
						}	
						$ll=1;
					} 
				}
			}
			else
			{
				foreach($FormFieldName as $fkey => $fval)
				{
					$ll=0;
					$type='';
					$patterntserch = $fkey;
					$type = $fval['type'];
					$newCheck = trim(str_replace(':', "", $patterntserch));
					if($type=='2')
					{
						$pattern = "'".$patterntserch." (.*?)[/\n]'sU";
					}
					else
					{
						$pattern = "'".$patterntserch."(.*?)[/\n]'s";
					}
					
					if(preg_match_all($pattern, $Message2, $matches))
					{
						//***********if criteria match then update status************/
						
						$criteriastatus = $criteriastatusFlash = '2';
						$DateEnter = explode(' ',$DateE);
						$SubmitDate = $DateEnter[0];
						// $this->updateCriteriaStatus($ID,'2');
						$findwholetext          = $matches[0][0];
						contanue:
						if($type=='2')
						{
							$CharArr1          		= array("\n");
							$findwholetextnew       = str_replace($CharArr1, " ", $findwholetext);
							// $Charnew          		= array("Type of Contact:","Think Green!");
							$Charnew          		= array("Best,","Reach me by:","Surname:","Best regards,","Producto:","Fabricante:","Modelo:","Calle:","Thanks");
							$findwholetext1        		= str_replace($Charnew,">>>>", $findwholetextnew);
							$testData = explode(">>>>", $findwholetext1);						
							$findwholetext2 = $testData[0];
							list($pieceName,$GetVal)= preg_split("/[\:]+/", $findwholetext2,2,PREG_SPLIT_NO_EMPTY);
						}
						else
						{					
							list($pieceName,$GetVal)= preg_split("/[\:]+/", $findwholetext ,2,PREG_SPLIT_NO_EMPTY);
							// $Charnew1 = array("COMPANY NAME:","POSITION:","ADDRESS1:","ADDRESS2:","ADDRESS3:","ADDRESS4:","TELEPHONE:","FAX:","E-MAIL:");
							// $GetValNew = str_replace($Charnew1,"###", $GetVal);
							// $testData = explode("###", $GetValNew);						
							// $GetVal = $testData[0];
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
						
						$sql_pereticularFornat =$this->getQuery("select a.ID,a.FieldName from ".$this->prefix_table.EMAIL_FORMFIELDS." as a left join ".$this->prefix_table.EMAIL_CRITERIA." as b on a.ID=b.DbFieldName where b.FormFieldName='".trim($pieceNameS)."' and b.IsActive='1' group by a.ID" );
						$countrow = $this->getNumRows($sql_pereticularFornat);	
						while($Res_pereticularFornat = $this->getNumArray($sql_pereticularFornat))
						{
							$perticularCol[] = trim($Res_pereticularFornat['1']);
							$perticularCol1[] = trim($GetVal);
						}
						
						if(!empty($usr_comment))
						{
							$findwholetext=$usr_comment;
							$patterntserch="Fluidname:";
							$usr_comment='';
							$type =1;
							goto contanue;
						}
					}
				}
				$Query =1;
			}
			
			$perticularCol_new      = array_unique($perticularCol);
			$insert_fields = $creat_feelds= $insert_vals="";
			if(!empty($perticularCol_new)){
				$Var= array_keys($perticularCol_new);
				foreach($Var as $val)
				{
					$TableValue[] = $perticularCol1[$val];
					$TableValue1[] = $perticularCol_new[$val];
				}
				$perticularCol_new = $TableValue1;
				
				// PR($row['Message_html']);
				/* PR($Message1);
				 PR($TableValue);
				 PR($perticularCol_new); 
				 PR("<hr/>"); 
				 die;
				*/
				$i=0;
				$in=0;
				$insert_fields= "";
				$creat_feelds= "";
				$insert_vals= "";
				setlocale(LC_CTYPE, 'cs_CZ');
				foreach($perticularCol_new as $tableFealds)
				{
					$validateRefined = "";
					if(!empty($TableValue[$in]))
					{
						$validateRefined = preg_replace('/[\x00-\x1F\x7F-\xA0\xAD]/u', '',$this->checkEmpty(trim($TableValue[$in])));
						if(empty($validateRefined))
						{
							// echo iconv('UTF-8', 'ASCII//TRANSLIT', "Žlutoucký kun\n");
							// $validateRefined = iconv('UTF-8', 'ASCII//TRANSLIT', $validateRefined);
							$validateRefined = utf8_encode($TableValue[$in]);
						}
						else{
							// $validateRefined = iconv('UTF-8', 'ASCII//TRANSLIT', $TableValue[$in]);
						}
					}
					
					$insert_fields.="`".str_replace(" ","_",trim($tableFealds))."`,";
					$creat_feelds.= "`".str_replace(" ","_",trim($tableFealds))."` text,"; 
					$insert_vals.="\"".$validateRefined."\","; 
					$in++;
				}
			}
			unset($perticularCol_new);
			unset($perticularCol);
			unset($perticularCol1);
			unset($TableValue1);
			unset($TableValue);
			
			if($kkt==1)
			{
				if($criteriastatusFlash<>'2')
				{ 
					$criteriastatus='1';
					$this->updateCriteriaStatus($ID,'1');
					$criteriastatusFlash='1'; 
				}	
			}
			$creatTable='';
			$insert_fields =substr($insert_fields,0,-1);
			$creatTable= "CREATE TABLE `".$this->prefix_table.EMAIL_FIXEDVAL."` (ID int(11) NOT NULL auto_increment,{$creat_feelds} PRIMARY KEY  (ID))";
			$insert_vals =substr($insert_vals,0,-1);
			if($criteriastatusFlash=='2')
			{
				/***********************Create CSV Heading and data with Subject - CSVWithSubject****************/
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
				
				$dropperticularsql = $this->getQuery("DROP TABLE IF EXISTS  ".$this->prefix_table.EMAIL_FIXEDVAL."");
				$rsperticular		=$this->getQuery($creatTable);
				
				$insertperticularSql = $this->getQuery("INSERT into ".$this->prefix_table.EMAIL_FIXEDVAL."($insert_fields) values ($insert_vals)");	
				$result2 = $this->getQuery("select * from ".$this->prefix_table.EMAIL_FIXEDVAL."");	
				$header_prti = '';
				$dataROWS = '';
				
				while($row2 = $this->getAssocArray($result2))
				{
					foreach($row2 as $key => $val)
					{
						if($key=="LeadComments"){ $val = str_replace("@BR@","<br/>",$val); }else{ $val = str_replace("@BR@","",$val); }
						$row2[$key] = $this->checkEmptyContent($val);
					}
					$lineData = '';
					$rowID = $ID;
					if(!empty($row2['First_Name']) && !empty($row2['Last_Name'])){
						$FirstName =$this->refineValue($row2['First_Name']);
						$LastName =$this->refineValue($row2['Last_Name']);
					}
					elseif(!empty($row2['FirstName']) && !empty($row2['LastName'])){
						$FirstName =$this->refineValue($row2['FirstName']);
						$LastName =$this->refineValue($row2['LastName']);
					}
					elseif(!empty($row2['First_Name']) && empty($row2['Last_Name'])){
						$exp = explode(' ',@trim($row2['First_Name']),2);
						$FirstName =$this->refineValue($exp[0]);
						$LastName =$this->refineValue($exp[1]);
					}
					elseif ( strpos($row2['ContactName']," ") > 0 )
					{
						$exp = explode(' ',@trim($row2['ContactName']),2);
						$FirstName =$this->refineValue($exp[0]);
						$LastName =$this->refineValue($exp[1]);
					} 
					else if ( strpos($row2['ContactName']," ") == 0 )
					{
						$FirstName =$this->refineValue($row2['ContactName']);
						$LastName =$this->refineValue($row2['LastName']);
					}					
					$Contact = "";
					if(!empty($FirstName))
					{
						$Contact = $FirstName." ".$LastName;
					}
					
					if(!empty($row2['Country'])){ $row2['Country'] = $this->getCountry($row2['Country']); }
					$Country =$this->refineValue($row2['Country']);
					
					$rmSpecialChar = array("-","(",")","_","."," ");
					if(strtoupper($Country)=="BRAZIL" && !empty($row2['ZipCode'])){
						$explodeZip = explode("-",$row2['ZipCode']);
						$row2['ZipCode'] = substr($explodeZip[0],0,5);
					}
					elseif(strtoupper($Country)=="COLOMBIA" && !empty($row2['ZipCode'])){
						$row2['ZipCode'] = str_replace($rmSpecialChar,"",$row2['ZipCode']);
						
						$colombZip = strlen(trim($row2['ZipCode']));
						if($colombZip<3)
						{
							if($colombZip=='2'){ $row2['ZipCode'] = '0'.$row2['ZipCode']; }
							elseif($colombZip=='1'){ $row2['ZipCode'] = '00'.$row2['ZipCode']; }
						}
					}
					
					if(!empty($Country) && (strtoupper($Country)=="USA" OR strtoupper($Country)=="BRAZIL") && !empty($row2['ZipCode']))
					{
						$usaZip = strlen(trim($row2['ZipCode']));
						if($usaZip<5)
						{
							if($usaZip=='4'){ $row2['ZipCode'] = '0'.$row2['ZipCode']; }
							elseif($usaZip=='3'){ $row2['ZipCode'] = '00'.$row2['ZipCode']; }
							elseif($usaZip=='2'){ $row2['ZipCode'] = '000'.$row2['ZipCode'];  }
							elseif($usaZip=='2'){ $row2['ZipCode'] = '000'.$row2['ZipCode']; }
							elseif($usaZip=='1'){ $row2['ZipCode'] = '0000'.$row2['ZipCode']; }
						}
					}
					
					$matchByZip = 0;
					$Latitude	=	"";
					$Longitude	=	"";
					if((strtoupper($Country)=="USA" OR strtoupper($Country)=="CANADA" OR strtoupper($Country)=="MEXICO" OR strtoupper($Country)=="BRAZIL" OR strtoupper($Country)=="INDIA" OR strtoupper($Country)=="COLOMBIA") && !empty($row2['ZipCode']))
					{
						list($matchByZip,$nCity,$nState,$nCounty,$nLatitude,$nLongitude) = $this->getCountyCityStatebyZip($row2['Country'],$row2['ZipCode']);
						
						if($matchByZip==0)
						{
							list($matchState,$nState) = $this->getActualState($row2['Country'],$row2['State']);
							if(!empty($nState))
							{
								$row2['State'] = $nState;
							} 
						}
						else{
							$row2['City']	=	$nCity;
							$row2['State']	=	$nState;
							$row2['County']	=	$nCounty;
							if(strtoupper($Country)=="USA" OR strtoupper($Country)=="CANADA")
							{
								$Latitude	=	$nLatitude;
								$Longitude	=	$nLongitude;
							}
						}
					}
					
					if(strtoupper($Country)=="USA" OR strtoupper($Country)=="CANADA")
					{
						$row2['FiveDigZip']  = substr($row2['ZipCode'],0,5);
						$row2['ThreeDigZip'] = substr($row2['ZipCode'],0,3);
						$row2['SixDigZip']   = @substr($row2['ZipCode'],0,6);
					}
					
					if($Latitude=='' && $Latitude=='')
					{
						$getLatLong	=	$this->getLatLong($row2);
						if(count($getLatLong)>0)
						{
							$Latitude		=	$getLatLong[0]['Latitude'];
							$Longitude		=	$getLatLong[0]['Longitude'];	
						}
					}	
					
					if(empty($row2['County']) && !empty($row2['City']) &&  !empty($row2['State']) && strtoupper($Country)=="USA")
					{
						list($matchByCityState,$nCounty) = $this->getCountyByCityAndState($Country,$row2['City'],$row2['State']);
						if($matchByCityState==1 && !empty($nCounty))
						{
							$row2['County'] = $nCounty;
						}
					}
					
					$Email 				= $this->refineValue($row2['Email']);
					$Company 			= $this->refineValue($row2['Company']);
					if(empty($Company)){ $Company='Not Provided'; }
					$PhoneSupplied  	= $this->refineValue($row2['PhoneSupplied']);
					$PhoneResearched 	= $this->refineValue($row2['PhoneResearched']);
					$Address 			= $this->refineValue($row2['Address']);
					$City 				= $this->refineValue($row2['City']);
					$State 				= $this->refineValue($row2['State']);
					$County 			= $this->refineValue($row2['County']);
					$ZipCode 			= $this->refineValue($row2['ZipCode']);
					$LeadComments 		= $this->refineValue($row2['LeadComments']);
					$Referral 	  		= $this->refineValue($row2['Distributor']);
					$BrandCat	  		= $Brand=$this->refineValue($row2['Brand']);
					$ContactTitle 		= $this->refineValue($row2['ContactTitle']);
					$Industry			= $this->refineValue($row2['Industry']);
					$Type				= $this->refineValue($row2['What_type_of_product_are_you_interested_in']);
					$Assembly			= $this->refineValue($row2['Will_you_require_installation']);
					$Application		= $this->refineValue($row2['Type_of_application_booth_is_used_for']);
					$HearAbout			= $this->refineValue($row2['How_did_you_hear_about_colmet']);
					$Buying_TimeFrame	= $this->refineValue($row2['What_is_your_buying_timeframe']);

					if(empty($PhoneSupplied))
					{
						$PhoneSupplied = $PhoneResearched;
						unset($PhoneResearched);
					}
					
					$LeadSource1=$this->refineValue($row2['LeadSource1']);
					
					$searchSubject 	= $this->sanitizeData(@trim(str_replace($FwCharArr,'',$row['Subject'])));
					if(strstr(strtoupper($Subject),$sub1))
					{
						$sql_leadSource = $this->getQuery("select LeadSource,LeadSource2,LeadComments,Brand,byPassMDB from ".$this->prefix_table.EMAIL_SUBJECT." where Subject like '".@trim($sub1)."%'");
					}
					else
					{
						
						$SubjectArr = explode("Lead from",$row['Subject'],2);
						$searchSubject = $SubjectArr[0];
						
						$sql_leadSource = $this->getQuery("select LeadSource,LeadSource2,LeadComments,Brand,byPassMDB from ".$this->prefix_table.EMAIL_SUBJECT." where Subject like '".@trim($searchSubject)."%'");

					}
					
					list($LeadSource,$LeadSource_2,$LeadComment,$LeadBrand,$byPassMDB) = $this->getNumArray($sql_leadSource);

					if(empty($LeadComments)){
						$LeadComments	=	$LeadComment; 	
					}
					
					if(empty($Brand))
					{
						$Brand = $LeadBrand;				
					}
					if(!empty($LeadSource))
					{
						$LeadSource1 = $LeadSource;				
					}
					if(!empty($LeadSource_2))
					{
						$LeadSource2 = $LeadSource_2;				
					}
					
					
					if($FirstName<>'' || $LastName<>'' || $Email<>'' || $Company<>'Not Provided' || $ZipCode<>'' || $Country<>'')
					{
						$todayDateTime	    =	date('Y-m-d H:i:s');
						$LeadDate		    =	$SubmitDate;
						$ReceivedDateTime   =	$DateE;
						$ReceivedDateTimedb =	$DateDb;
						$byPassMDB = $byPassMDB=="yes" ? $byPassMDB : "no";
						$csvfile = $row['ID']."_OutlookMails.csv";
						
						$PdffileName = "";
						if(strstr(strtoupper($Subject),$sub1))
						{
							$PdffileName = "yes";
						}
						
						$insertSql = $this->getQuery("INSERT into ".$this->prefix_table.EMAIL_DOWNLOAD."(Referral,Brand,ReceivedDateTime,ReceivedDateTimeDB,FirstName,LastName,ContactTitle,Email,Company,Address,City,State,ZipCode,County,Country,LeadSource1,LeadSource2,LeadSource3,LeadComments,PhoneSupplied,PhSuppliedExtension,CSRName,PDF,DUNS,PhoneResearched,WebAddress,SIC,NAICS,noOfEmployees,ParentName,LineOfBusiness,Market,Product,PQ,Industry,Type,Assembly,Application,HearAbout,Buying_TimeFrame,Latitude,Longitude,byPassMDB,filename,Subject,PdfLink,email_id) values('".$Referral."','".$Brand."','".$ReceivedDateTime."','".$ReceivedDateTimedb."','".$FirstName."','".$LastName."','".$ContactTitle."','".$Email."','".$Company."','".$Address."','".$City."','".$State."','".$ZipCode."','".$County."','".$Country."','".$LeadSource1."','".$LeadSource2."','".$LeadSource3."','".$LeadComments."','".$PhoneSupplied."','".$PhSuppliedExtension."','".$CSRName."','".$PDF."','".$DUNS."','".$PhoneResearched."','".$WebAddress."','".$SIC."','".$NAICS."','".$noOfEmployees."','".$ParentName."','".$LineOfBusiness."','".$Market."','".$Product."','".$PQ."','".$Industry."','".$Type."','".$Assembly."','".$Application."','".$HearAbout."','".$Buying_TimeFrame."','".$Latitude."','".$Longitude."','".$byPassMDB."','".$csvfile."','".$this->sanitizeData($row['Subject'])."','".$PdffileName."','".$row['ID']."')");
						
						$this->updateCriteriaStatus($row['ID'],'2');	
					}
					else
					{
						$this->updateCriteriaStatus($row['ID'],'1');	
					}
				}
			}
			else
			{
				$this->updateCriteriaStatus($row['ID'],'1');	
			}
			$insert_fields =$creat_feelds=$insert_vals='';
		}
	}
	
	
	function getFilterColoumn($isActive='')
	{
		if(!empty($isActive))
		{
			$isActive = " where isActive='{$isActive}' " ;
		}
		$sql = $this->getQuery("Select * FROM ".$this->prefix_table.FILTER_COLOUMN." {$isActive} order by Sequence ");
		$arr = array();
		if($this->getNumRows($sql)>0)
		{
			while($row = $this->getAssocArray($sql))
			{
				$col = strtolower($row['Coloumn']);
				$arr[$col] = $row;
			}
		}
		return $arr;
	}
	function insertIntoTempDB($autoRefinedLeads)
	{
		$getFilterColoumn	=	$this->getFilterColoumn('1');
		$resByePass = count($autoRefinedLeads);
		$insertArray = array();
		for($by=0;$by<$resByePass;$by++)
		{
			$nResbyPass = $autoRefinedLeads[$by];
			$insertLead = "";
			foreach($nResbyPass as $rKey => $rVal)
			{
				switch($rKey)
				{
					case "ID": case "filename": case "Subject": case "IsActive": case "IsActiveDB":
						continue;
					break;
					default:
						switch($rKey)
						{
							case "FirstName":
								$rKey="First_Name";
							break;
							case "LastName":
								$rKey="Last_Name";
							break;
							case "ServiceProvider":
								$rVal=!empty($rVal) && $rVal=="Y" ? "Y" : "N";
							break;
						}
						
						if(array_key_exists(@strtolower($rKey),$getFilterColoumn)){
							$rVal		=	str_replace("\"","'",$rVal);
							if(strtolower($rKey)=="brand")
							{
								$insertLead .= " `uploadedBrand`=\"".$rVal."\",";
							}
							$insertLead .= " `{$rKey}`=\"".$rVal."\",";
						}
				}
			}
			if(!empty($insertLead))
			{
				$insertLead = substr($insertLead,0,-1).",`uploadedBy`='MDB'";
				$this->getQuery("INSERT INTO ".$this->prefix_table.LEAD_TEMP_DB." SET ".$insertLead);
				$insertID  = $this->getLastInsertID();
				if($insertID>0)
				{
					$this->updateRefinedLeads($nResbyPass["ID"]);
				}
			}
		}
	}
	
	function getTempDBData()
	{
		$count = 0;
		$sql = $this->getQuery("Select count(LUID) as pendingcount from ".$this->prefix_table.LEAD_TEMP_DB." where InsertInTemp='0' ");
		if($this->getNumRows($sql)>0)
		{
			list($count) = $this->getNumArray($sql);
		}
		return $count;
	}
	
	function getNameFromNumber($num) {
		$numeric = $num % 26;
		$letter = chr(65 + $numeric);
		$num2 = intval($num / 26);
		if ($num2 > 0) {
			return $this->getNameFromNumber($num2 - 1) . $letter;
		} else {
			return $letter;
		}
	}

}#end class
?>