<?php

# E-mail Class
# Laatst bijgewerkt op: 
#
# Mogelijke acties:
# - 

class email {
	
	public $smtpPort = 		'25';
	public $smtpHost = 		'mail.calexis.nl';
	public $smtpUsername = 	'info@calexis.nl';
	public $smtpPassword = 	'schoenmode';
	public $fromName = 		'Calexis';
	public $fromEmail = 	'info@calexis.nl'; 
	public $langcode = 		'nl';
	public $SMTPAuth = 		true;
	public $isHTML = 		true;
	public $mergeHTML = 	false;
	
	function __construct(){
		//$this->smtpPort = 	$_SESSION['SiteSettings']['smtpport'];
		//$this->smtpHost = 	$_SESSION['SiteSettings']['smtphost'];
		//$this->smtpUsername = $_SESSION['SiteSettings']['smtpusername'];
		//$this->smtpPassword = $_SESSION['SiteSettings']['smtppassword'];
		//$this->fromName = 	$_SESSION['SiteSettings']['name'];
		//$this->fromEmail = 	$_SESSION['SiteSettings']['noreplyemail'];
		//$this->langcode = $_SESSION['SiteSettings']['langcode'];
	}
	
	function validate($email){
		
		$valid = $mail->ValidateAddress($email);
		if($valid === false) $errors[] = "Not a valid e-mail address";
		if(!(isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER']) && stristr($_SERVER['HTTP_REFERER'],$_SERVER['HTTP_HOST']))){
			$errors[] = "You must enable referrer logging to use the form";
		}
		if(!empty($errors)){
			return false;
		}else{
			return true;
		}
	}
	
	/* 
		MERGEWITHTEMPLATE - Het html bericht samenvoegen met de template.
	*/
	function mergeWithTemplate($message){
		ob_start();
		include("templates/frontend/". $_SESSION['SiteSettings']['frontendtemplate'] ."/mail.php");
		$html = ob_get_contents();
		ob_end_clean();
		$html = str_replace('{content}',$message,$html);
		return $html;
	}
	
	
	function send($name, $emailaddress, $subject, $message, $attachmentlocation="", $attachmentname="", $att2loc='', $att2name=''){
		try{
			$mail = new phpmailer(true);
			
			$mail->SetLanguage($this->langcode);
			
			$mail->IsSMTP();                           		// tell the class to use SMTP
			$mail->SMTPAuth   = $this->SMTPAuth;            // enable SMTP authentication
			$mail->Port       = $this->smtpPort;        // set the SMTP server port
			$mail->Host       = $this->smtpHost; 		// SMTP server
			$mail->Username   = $this->smtpUsername;    // SMTP server username
			$mail->Password   = $this->smtpPassword;    // SMTP server password
			
			if($this->mergeHTML == true){
				$message = $this->mergeWithTemplate($message);
			}
			
			$mail->From       = $this->fromEmail;
			$mail->FromName   = $this->fromName;
			$mail->AddAddress($emailaddress, $name);
			$mail->Subject = $subject;
			$mail->IsHTML(); // send as HTML
			$mail->AltBody = "Gebruik een email client die HTML ondersteunt om dit bericht te bekijken!";
			$mail->MsgHTML($message."<br/><br/>");
			
			
			if(!empty($attachmentlocation)){
				if(file_exists($attachmentlocation)){
					if(!empty($attachmentname)){
						$mail->AddAttachment($attachmentlocation, $attachmentname);	
					}
				}
			}
			
			$mail->Send();
			return true;
		}catch (phpmailerException $e){
			return $e->errorMessage();
		}
		
	}
	
	function sendform(){
		if(!empty($_POST)){
		    $post = $_POST;
			foreach($post as $key => $value){
				$post[$key] = htmlentities(strip_tags($value));
			}
			if(empty($post['name'])) $_SESSION['error']['name'] = 'Geen naam ingevuld.<br/>';
			if(empty($post['email'])) $_SESSION['error']['email'] = 'Geen email ingevuld.<br/>';
			if(!preg_match('/^[A-Za-z0-9\+._-]+@[A-Za-z0-9._-]+\.[A-Za-z]{2,6}$/', $post['email'])) $_SESSION['error']['email'] .= 'Geen geldige email.<br/>';
			if(empty($post['subject'])) $_SESSION['error']['subject'] = 'Geen onderwerp ingevuld.<br/>';
			if(empty($post['message'])) $_SESSION['error']['message'] = 'Geen bericht ingevuld.<br/>';
			if(!isset($_SESSION['error'])){
				$email = new email;
				$email->fromEmail = $post['email'];
				$email->fromName = $post['name'];
				$result = $email->send('Calexis', "info@calexis.nl", $post['subject'], $post['message']);
                
				if($result == true){
					$result = 'Location: /content/bedankt.html';
					$return = array ("ajax" => false, "result" => $result);
					return $return;
				}else{
					$result = 'Location: /content/sorry.html';
					$return = array ("ajax" => false, "result" => $result);
					return $return;
				}
			}else{
				//BACK TO FORM
				foreach($post as $key => $value){
					$_SESSION['post'][$key] = stripslashes($value);
				}
				$result = 'Location: /content/contact.html';
				return $return = array("ajax" => false, "result" => $result);
			}
		}else{
			$result = "Location: /index.php";
			return $return = array("ajax" => false, "result" => $result);	
		}
	}
	
	function applynewsletter($post, $get){
		//Check if the e-mail address already exists
		$result = $database->query("SELECT emailadres FROM nieuwsbriefabonnees WHERE emailadres='{0}'", $values=array($_POST['emailadres']));
		if(count($result) > 0){
			//It already exists
			//Return
		}elseif(validateEmail($_POST['emailadres'], true, true, '', '', true)){
			//Not valid
			//Return
		}else{
			//Add it to the database, create a token (timestamp) for verification
			$token = time();
			$database->query("INSERT INTO nieuwsbriefabonnees (emailadres, ipadres, token, geverifieerd) VALUES ('{0}', '{1}', '{2}', {3})",$values=array($_POST['emailadres'], $_SERVER['REMOTE_ADDR'], $token, 0), false);
			//Send them a verification e-mail
			$success = "Location: index.php?p=aangemeld";
			$failure = "Location: index.php?p=sorry";
			$to = $_POST['emailadres'];
			$from = 'no-reply@calexis.nl';
			$fromname = 'Calexis Schoenmode';
			$subject = 'Bevestig je e-mailadres';
			$body = 'Bedankt voor uw aanmelding op de Calexis nieuwsbrief. Om te bevestigen dat uw e-mailadres correct is ingevoerd dient u op de volgende link te klikken: <a href="http://www.calexis.nl/index.php?req=verify&token='.$token.'">http://www.calexis.nl/index.php?req=verify&token='.$token.'</a> Of kopier de link in uw browser.';
		}
	}
	
	function verifynewsletter($post, $get){
		//See if it exists
		$result = $database->query("SELECT geverifieerd FROM nieuwsbriefabonnees WHERE token='{0}'", $values=array($_GET['token']));
		if(count($result) > 0 && $result[0]['geverifieerd'] == 0){
			//Verify the e-mail address
			$database->query("UPDATE nieuwsbriefabonnees SET geverifieerd=1 WHERE token='{0}'", $values=array($_GET['token']), false);
			//Send the user to a success page
			header("Location: /content/geverifieerd.html");
			exit();
		}
	}
	
}

?>